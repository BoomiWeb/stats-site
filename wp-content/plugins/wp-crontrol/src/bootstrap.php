<?php
/**
 * Functions related to bootstrapping WP Crontrol.
 */

namespace Crontrol;

use Crontrol\Event\Table;
use stdClass;
use WP_Error;
use Exception;

use function Crontrol\Event\check_integrity;

const TRANSIENT = 'crontrol-message-%d';
const PAUSED_OPTION = 'wp_crontrol_paused';

/**
 * Hook onto all of the actions and filters needed by the plugin.
 *
 * @return void
 */
function init_hooks() {
	$plugin_file = plugin_basename( PLUGIN_FILE );

	add_action( 'init',                               __NAMESPACE__ . '\action_init' );
	add_action( 'init',                               __NAMESPACE__ . '\action_handle_posts' );
	add_action( 'admin_menu',                         __NAMESPACE__ . '\action_admin_menu' );
	add_filter( "plugin_action_links_{$plugin_file}", __NAMESPACE__ . '\plugin_action_links' );
	add_filter( "network_admin_plugin_action_links_{$plugin_file}", __NAMESPACE__ . '\network_plugin_action_links' );
	add_filter( 'removable_query_args',               __NAMESPACE__ . '\filter_removable_query_args' );
	add_filter( 'pre_unschedule_event',               __NAMESPACE__ . '\maybe_clear_doing_cron' );
	add_filter( 'plugin_row_meta',                    __NAMESPACE__ . '\filter_plugin_row_meta', 10, 2 );

	add_action( 'load-tools_page_wp-crontrol', __NAMESPACE__ . '\setup_manage_page' );

	add_filter( 'cron_schedules',        __NAMESPACE__ . '\filter_cron_schedules' );
	add_action( 'crontrol_cron_job',     __NAMESPACE__ . '\action_php_cron_event' );
	add_action( 'crontrol_url_cron_job', __NAMESPACE__ . '\action_url_cron_event' );
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
	add_action( 'crontrol/tab-header',   __NAMESPACE__ . '\show_cron_status', 20 );
	add_action( 'activated_plugin',      __NAMESPACE__ . '\flush_status_cache', 10, 0 );
	add_action( 'deactivated_plugin',    __NAMESPACE__ . '\flush_status_cache', 10, 0 );
	add_action( 'switch_theme',          __NAMESPACE__ . '\flush_status_cache', 10, 0 );
}

/**
 * Sets an error message to show to the current user after a redirect.
 *
 * @param string $message The error message text.
 * @return bool Whether the message was saved.
 */
function set_message( $message ) {
	$key = sprintf(
		TRANSIENT,
		get_current_user_id()
	);
	return set_transient( $key, $message, 60 );
}

/**
 * Gets the error message to show to the current user after a redirect.
 *
 * @return string The error message text.
 */
function get_message() {
	$key = sprintf(
		TRANSIENT,
		get_current_user_id()
	);
	return get_transient( $key );
}

/**
 * Filters the array of row meta for each plugin in the Plugins list table.
 *
 * @param array<int,string> $plugin_meta An array of the plugin row's meta data.
 * @param string            $plugin_file Path to the plugin file relative to the plugins directory.
 * @return array<int,string> An array of the plugin row's meta data.
 */
function filter_plugin_row_meta( array $plugin_meta, $plugin_file ) {
	if ( 'wp-crontrol/wp-crontrol.php' !== $plugin_file ) {
		return $plugin_meta;
	}

	$plugin_meta[] = sprintf(
		'<a href="%1$s"><span class="dashicons dashicons-star-filled" aria-hidden="true" style="font-size:14px;line-height:1.3"></span>%2$s</a>',
		'https://github.com/sponsors/johnbillion',
		esc_html_x( 'Sponsor', 'verb', 'wp-crontrol' )
	);

	return $plugin_meta;
}

/**
 * Run using the 'init' action.
 *
 * @return void
 */
function action_init() {
	load_plugin_textdomain( 'wp-crontrol', false, dirname( plugin_basename( PLUGIN_FILE ) ) . '/languages' );

	/** @var array<array-key, true>|false $paused */
	$paused = get_option( PAUSED_OPTION );

	if ( ! is_array( $paused ) ) {
		$paused = array();
		update_option( PAUSED_OPTION, $paused, true );
	}

	foreach ( $paused as $hook => $value ) {
		if ( ! is_string( $hook ) ) {
			continue;
		}

		add_action( $hook, __NAMESPACE__ . '\\pauser', -99999, 0 );
	}
}

/**
 * @return void
 */
function pauser() {
	remove_all_actions( current_filter() );
}

/**
 * Handles any POSTs and GETs made by the plugin. Run using the 'init' action.
 *
 * @return void
 */
function action_handle_posts() {
	$request = new Request();

	if ( isset( $_POST['crontrol_action'] ) && ( 'new_cron' === $_POST['crontrol_action'] ) ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to add new cron events.', 'wp-crontrol' ), 401 );
		}
		check_admin_referer( 'crontrol-new-cron' );

		$cr = $request->init( wp_unslash( $_POST ) );

		if ( 'crontrol_cron_job' === $cr->hookname ) {
			wp_die( esc_html__( 'You are not allowed to add new PHP cron events.', 'wp-crontrol' ), 401 );
		}
		$args = json_decode( $cr->args, true );

		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = array();
		}

		$next_run_local = ( 'custom' === $cr->next_run_date_local ) ? $cr->next_run_date_local_custom_date . ' ' . $cr->next_run_date_local_custom_time : $cr->next_run_date_local;

		add_filter( 'schedule_event', function ( $event ) {
			if ( ! $event ) {
				return $event;
			}

			/**
			 * Fires after a new cron event is added.
			 *
			 * @param stdClass $event {
			 *     An object containing the event's data.
			 *
			 *     @type string       $hook      Action hook to execute when the event is run.
			 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
			 *     @type string|false $schedule  How often the event should subsequently recur.
			 *     @type mixed[]      $args      Array containing each separate argument to pass to the hook's callback function.
			 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
			 * }
			 */
			do_action( 'crontrol/added_new_event', $event );

			return $event;
		}, 99 );

		$added = Event\add( $next_run_local, $cr->schedule, $cr->hookname, $args );

		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_message' => '4',
			'crontrol_name'    => rawurlencode( $cr->hookname ),
		);

		if ( is_wp_error( $added ) ) {
			set_message( $added->get_error_message() );
			$redirect['crontrol_message'] = 'error';
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;

	} elseif ( isset( $_POST['crontrol_action'] ) && ( 'new_url_cron' === $_POST['crontrol_action'] ) ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to add new cron events.', 'wp-crontrol' ), 401 );
		}
		check_admin_referer( 'crontrol-new-cron' );

		$cr = $request->init( wp_unslash( $_POST ) );

		$next_run_local = ( 'custom' === $cr->next_run_date_local ) ? $cr->next_run_date_local_custom_date . ' ' . $cr->next_run_date_local_custom_time : $cr->next_run_date_local;
		$args = array(
			array(
				'url' => $cr->url,
				'method' => $cr->method,
				'name' => $cr->eventname,
				'hash' => wp_hash( $cr->url ),
			),
		);

		add_filter( 'schedule_event', function ( $event ) {
			if ( ! $event ) {
				return $event;
			}

			/**
			 * Fires after a new URL cron event is added.
			 *
			 * @param stdClass $event {
			 *     An object containing the event's data.
			 *
			 *     @type string       $hook      Action hook to execute when the event is run.
			 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
			 *     @type string|false $schedule  How often the event should subsequently recur.
			 *     @type mixed[]      $args      Array containing each separate argument to pass to the hook's callback function.
			 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
			 * }
			 */
			do_action( 'crontrol/added_new_url_event', $event );

			return $event;
		}, 99 );

		$added = Event\add( $next_run_local, $cr->schedule, 'crontrol_url_cron_job', $args );

		$hookname = ( ! empty( $cr->eventname ) ) ? $cr->eventname : __( 'URL Cron', 'wp-crontrol' );
		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_message' => '13',
			'crontrol_name'    => rawurlencode( $hookname ),
		);

		if ( is_wp_error( $added ) ) {
			set_message( $added->get_error_message() );
			$redirect['crontrol_message'] = 'error';
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;

	} elseif ( isset( $_POST['crontrol_action'] ) && ( 'new_php_cron' === $_POST['crontrol_action'] ) ) {
		if ( ! current_user_can( 'edit_files' ) ) {
			wp_die( esc_html__( 'You are not allowed to add new PHP cron events.', 'wp-crontrol' ), 401 );
		}
		check_admin_referer( 'crontrol-new-cron' );

		$cr = $request->init( wp_unslash( $_POST ) );

		$next_run_local = ( 'custom' === $cr->next_run_date_local ) ? $cr->next_run_date_local_custom_date . ' ' . $cr->next_run_date_local_custom_time : $cr->next_run_date_local;

		$args = array(
			array(
				'code' => $cr->hookcode,
				'name' => $cr->eventname,
				'hash' => wp_hash( $cr->hookcode ),
			),
		);

		add_filter( 'schedule_event', function ( $event ) {
			if ( ! $event ) {
				return $event;
			}

			/**
			 * Fires after a new PHP cron event is added.
			 *
			 * @param stdClass $event {
			 *     An object containing the event's data.
			 *
			 *     @type string       $hook      Action hook to execute when the event is run.
			 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
			 *     @type string|false $schedule  How often the event should subsequently recur.
			 *     @type mixed[]      $args      Array containing each separate argument to pass to the hook's callback function.
			 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
			 * }
			 */
			do_action( 'crontrol/added_new_php_event', $event );

			return $event;
		}, 99 );

		$added = Event\add( $next_run_local, $cr->schedule, 'crontrol_cron_job', $args );

		$hookname = ( ! empty( $cr->eventname ) ) ? $cr->eventname : __( 'PHP Cron', 'wp-crontrol' );
		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_message' => '14',
			'crontrol_name'    => rawurlencode( $hookname ),
		);

		if ( is_wp_error( $added ) ) {
			set_message( $added->get_error_message() );
			$redirect['crontrol_message'] = 'error';
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;

	} elseif ( isset( $_POST['crontrol_action'] ) && ( 'edit_cron' === $_POST['crontrol_action'] ) ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to edit cron events.', 'wp-crontrol' ), 401 );
		}

		$cr = $request->init( wp_unslash( $_POST ) );

		check_admin_referer( "crontrol-edit-cron_{$cr->original_hookname}_{$cr->original_sig}_{$cr->original_next_run_utc}" );

		if ( 'crontrol_cron_job' === $cr->hookname && ! current_user_can( 'edit_files' ) ) {
			wp_die( esc_html__( 'You are not allowed to edit PHP cron events.', 'wp-crontrol' ), 401 );
		}

		$args = json_decode( $cr->args, true );

		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = array();
		}

		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_message' => '4',
			'crontrol_name'    => rawurlencode( $cr->hookname ),
		);

		$original = Event\get_single( $cr->original_hookname, $cr->original_sig, $cr->original_next_run_utc );

		if ( is_wp_error( $original ) ) {
			set_message( $original->get_error_message() );
			$redirect['crontrol_message'] = 'error';
			wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
			exit;
		}

		$deleted = Event\delete( $cr->original_hookname, $cr->original_sig, $cr->original_next_run_utc );

		if ( is_wp_error( $deleted ) ) {
			set_message( $deleted->get_error_message() );
			$redirect['crontrol_message'] = 'error';
			wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
			exit;
		}

		$next_run_local = $cr->next_run_date_local_custom_date . ' ' . $cr->next_run_date_local_custom_time;

		/**
		 * Modifies an event before it is scheduled.
		 *
		 * @param stdClass|false $event An object containing the new event's data, or boolean false.
		 */
		add_filter( 'schedule_event', function ( $event ) use ( $original ) {
			if ( ! $event ) {
				return $event;
			}

			/**
			 * Fires after a cron event is edited.
			 *
			 * @param stdClass $event {
			 *     An object containing the new event's data.
			 *
			 *     @type string       $hook      Action hook to execute when the event is run.
			 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
			 *     @type string|false $schedule  How often the event should subsequently recur.
			 *     @type mixed[]      $args      Array containing each separate argument to pass to the hook's callback function.
			 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
			 * }
			 * @param stdClass $original {
			 *     An object containing the original event's data.
			 *
			 *     @type string       $hook      Action hook to execute when the event is run.
			 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
			 *     @type string|false $schedule  How often the event should subsequently recur.
			 *     @type mixed[]      $args      Array containing each separate argument to pass to the hook's callback function.
			 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
			 * }
			 */
			do_action( 'crontrol/edited_event', $event, $original );

			return $event;
		}, 99 );

		$added = Event\add( $next_run_local, $cr->schedule, $cr->hookname, $args );

		if ( is_wp_error( $added ) ) {
			set_message( $added->get_error_message() );
			$redirect['crontrol_message'] = 'error';
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;

	} elseif ( isset( $_POST['crontrol_action'] ) && ( 'edit_url_cron' === $_POST['crontrol_action'] ) ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to edit cron events.', 'wp-crontrol' ), 401 );
		}

		$cr = $request->init( wp_unslash( $_POST ) );

		check_admin_referer( "crontrol-edit-cron_{$cr->original_hookname}_{$cr->original_sig}_{$cr->original_next_run_utc}" );

		$args = array(
			array(
				'url' => $cr->url,
				'method' => $cr->method,
				'name' => $cr->eventname,
				'hash' => wp_hash( $cr->url ),
			),
		);
		$hookname = ( ! empty( $cr->eventname ) ) ? $cr->eventname : __( 'URL Cron', 'wp-crontrol' );
		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_message' => '13',
			'crontrol_name'    => rawurlencode( $hookname ),
		);

		$original = Event\get_single( $cr->original_hookname, $cr->original_sig, $cr->original_next_run_utc );

		if ( is_wp_error( $original ) ) {
			set_message( $original->get_error_message() );
			$redirect['crontrol_message'] = 'error';
			wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
			exit;
		}

		$deleted = Event\delete( $cr->original_hookname, $cr->original_sig, $cr->original_next_run_utc );

		if ( is_wp_error( $deleted ) ) {
			set_message( $deleted->get_error_message() );
			$redirect['crontrol_message'] = 'error';
			wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
			exit;
		}

		$next_run_local = ( 'custom' === $cr->next_run_date_local ) ? $cr->next_run_date_local_custom_date . ' ' . $cr->next_run_date_local_custom_time : $cr->next_run_date_local;

		/**
		 * Modifies an event before it is scheduled.
		 *
		 * @param stdClass|false $event An object containing the new event's data, or boolean false.
		 */
		add_filter( 'schedule_event', function ( $event ) use ( $original ) {
			if ( ! $event ) {
				return $event;
			}

			/**
			 * Fires after a URL cron event is edited.
			 *
			 * @param stdClass $event {
			 *     An object containing the new event's data.
			 *
			 *     @type string       $hook      Action hook to execute when the event is run.
			 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
			 *     @type string|false $schedule  How often the event should subsequently recur.
			 *     @type mixed[]      $args      Array containing each separate argument to pass to the hook's callback function.
			 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
			 * }
			 * @param stdClass $original {
			 *     An object containing the original event's data.
			 *
			 *     @type string       $hook      Action hook to execute when the event is run.
			 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
			 *     @type string|false $schedule  How often the event should subsequently recur.
			 *     @type mixed[]      $args      Array containing each separate argument to pass to the hook's callback function.
			 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
			 * }
			 */
			do_action( 'crontrol/edited_url_event', $event, $original );

			return $event;
		}, 99 );

		$added = Event\add( $next_run_local, $cr->schedule, 'crontrol_url_cron_job', $args );

		if ( is_wp_error( $added ) ) {
			set_message( $added->get_error_message() );
			$redirect['crontrol_message'] = 'error';
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;

	} elseif ( isset( $_POST['crontrol_action'] ) && ( 'edit_php_cron' === $_POST['crontrol_action'] ) ) {
		if ( ! current_user_can( 'edit_files' ) ) {
			wp_die( esc_html__( 'You are not allowed to edit PHP cron events.', 'wp-crontrol' ), 401 );
		}

		$cr = $request->init( wp_unslash( $_POST ) );

		check_admin_referer( "crontrol-edit-cron_{$cr->original_hookname}_{$cr->original_sig}_{$cr->original_next_run_utc}" );

		$args = array(
			array(
				'code' => $cr->hookcode,
				'name' => $cr->eventname,
				'hash' => wp_hash( $cr->hookcode ),
			),
		);
		$hookname = ( ! empty( $cr->eventname ) ) ? $cr->eventname : __( 'PHP Cron', 'wp-crontrol' );
		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_message' => '14',
			'crontrol_name'    => rawurlencode( $hookname ),
		);

		$original = Event\get_single( $cr->original_hookname, $cr->original_sig, $cr->original_next_run_utc );

		if ( is_wp_error( $original ) ) {
			set_message( $original->get_error_message() );
			$redirect['crontrol_message'] = 'error';
			wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
			exit;
		}

		$deleted = Event\delete( $cr->original_hookname, $cr->original_sig, $cr->original_next_run_utc );

		if ( is_wp_error( $deleted ) ) {
			set_message( $deleted->get_error_message() );
			$redirect['crontrol_message'] = 'error';
			wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
			exit;
		}

		$next_run_local = $cr->next_run_date_local_custom_date . ' ' . $cr->next_run_date_local_custom_time;

		/**
		 * Modifies an event before it is scheduled.
		 *
		 * @param stdClass|false $event An object containing the new event's data, or boolean false.
		 */
		add_filter( 'schedule_event', function ( $event ) use ( $original ) {
			if ( ! $event ) {
				return $event;
			}

			/**
			 * Fires after a PHP cron event is edited.
			 *
			 * @param stdClass $event {
			 *     An object containing the new event's data.
			 *
			 *     @type string       $hook      Action hook to execute when the event is run.
			 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
			 *     @type string|false $schedule  How often the event should subsequently recur.
			 *     @type mixed[]      $args      Array containing each separate argument to pass to the hook's callback function.
			 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
			 * }
			 * @param stdClass $original {
			 *     An object containing the original event's data.
			 *
			 *     @type string       $hook      Action hook to execute when the event is run.
			 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
			 *     @type string|false $schedule  How often the event should subsequently recur.
			 *     @type mixed[]      $args      Array containing each separate argument to pass to the hook's callback function.
			 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
			 * }
			 */
			do_action( 'crontrol/edited_php_event', $event, $original );

			return $event;
		}, 99 );

		$added = Event\add( $next_run_local, $cr->schedule, 'crontrol_cron_job', $args );

		if ( is_wp_error( $added ) ) {
			set_message( $added->get_error_message() );
			$redirect['crontrol_message'] = 'error';
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;

	} elseif ( isset( $_POST['crontrol_new_schedule'] ) ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to add new cron schedules.', 'wp-crontrol' ), 401 );
		}
		check_admin_referer( 'crontrol-new-schedule' );
		$name     = sanitize_text_field( wp_unslash( $_POST['crontrol_schedule_internal_name'] ) );
		$interval = absint( $_POST['crontrol_schedule_interval'] );
		$display  = sanitize_text_field( wp_unslash( $_POST['crontrol_schedule_display_name'] ) );

		Schedule\add( $name, $interval, $display );
		$redirect = array(
			'page'             => 'wp-crontrol-schedules',
			'crontrol_message' => '3',
			'crontrol_name'    => rawurlencode( $name ),
		);
		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'options-general.php' ) ) );
		exit;

	} elseif ( isset( $_GET['crontrol_action'] ) && 'delete-schedule' === $_GET['crontrol_action'] ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to delete cron schedules.', 'wp-crontrol' ), 401 );
		}
		$schedule = wp_unslash( $_GET['crontrol_id'] );
		check_admin_referer( "crontrol-delete-schedule_{$schedule}" );
		Schedule\delete( $schedule );
		$redirect = array(
			'page'             => 'wp-crontrol-schedules',
			'crontrol_message' => '2',
			'crontrol_name'    => rawurlencode( $schedule ),
		);
		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'options-general.php' ) ) );
		exit;

	} elseif ( ( isset( $_POST['action'] ) && 'crontrol_delete_crons' === $_POST['action'] ) || ( isset( $_POST['action2'] ) && 'crontrol_delete_crons' === $_POST['action2'] ) ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to delete cron events.', 'wp-crontrol' ), 401 );
		}
		check_admin_referer( 'bulk-crontrol-events' );

		if ( empty( $_POST['crontrol_delete'] ) ) {
			return;
		}

		/**
		 * @var array<string,array<string,string>>
		 */
		$delete  = (array) wp_unslash( $_POST['crontrol_delete'] );
		$deleted = 0;

		foreach ( $delete as $next_run_utc => $events ) {
			foreach ( (array) $events as $hook => $sig ) {
				if ( 'crontrol_cron_job' === $hook && ! current_user_can( 'edit_files' ) ) {
					continue;
				}

				$event = Event\get_single( urldecode( $hook ), $sig, $next_run_utc );
				$result = Event\delete( urldecode( $hook ), $sig, $next_run_utc );

				if ( ! is_wp_error( $result ) ) {
					++$deleted;

					/** This action is documented in wp-crontrol.php */
					do_action( 'crontrol/deleted_event', $event );
				}
			}
		}

		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_name'    => $deleted,
			'crontrol_message' => '9',
		);
		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;

	} elseif ( isset( $_GET['crontrol_action'] ) && 'delete-cron' === $_GET['crontrol_action'] ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to delete cron events.', 'wp-crontrol' ), 401 );
		}
		$hook         = wp_unslash( $_GET['crontrol_id'] );
		$sig          = wp_unslash( $_GET['crontrol_sig'] );
		$next_run_utc = wp_unslash( $_GET['crontrol_next_run_utc'] );
		check_admin_referer( "crontrol-delete-cron_{$hook}_{$sig}_{$next_run_utc}" );

		if ( 'crontrol_cron_job' === $hook && ! current_user_can( 'edit_files' ) ) {
			wp_die( esc_html__( 'You are not allowed to delete PHP cron events.', 'wp-crontrol' ), 401 );
		}

		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_message' => '6',
			'crontrol_name'    => rawurlencode( $hook ),
		);

		$event = Event\get_single( $hook, $sig, $next_run_utc );

		if ( is_wp_error( $event ) ) {
			set_message( $event->get_error_message() );
			$redirect['crontrol_message'] = 'error';
			wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
			exit;
		}

		$deleted = Event\delete( $hook, $sig, $next_run_utc );

		if ( is_wp_error( $deleted ) ) {
			set_message( $deleted->get_error_message() );
			$redirect['crontrol_message'] = 'error';
		} else {
			/**
			 * Fires after a cron event is deleted.
			 *
			 * @param stdClass $event {
			 *     An object containing the event's data.
			 *
			 *     @type string       $hook      Action hook to execute when the event is run.
			 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
			 *     @type string|false $schedule  How often the event should subsequently recur.
			 *     @type mixed[]      $args      Array containing each separate argument to pass to the hook's callback function.
			 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
			 * }
			 */
			do_action( 'crontrol/deleted_event', $event );
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;

	} elseif ( isset( $_GET['crontrol_action'] ) && 'delete-hook' === $_GET['crontrol_action'] ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to delete cron events.', 'wp-crontrol' ), 401 );
		}
		$hook    = wp_unslash( $_GET['crontrol_id'] );
		$deleted = false;
		check_admin_referer( "crontrol-delete-hook_{$hook}" );

		if ( 'crontrol_cron_job' === $hook ) {
			wp_die( esc_html__( 'You are not allowed to delete PHP cron events.', 'wp-crontrol' ), 401 );
		}

		$deleted = wp_unschedule_hook( $hook );

		if ( 0 === $deleted ) {
			$redirect = array(
				'page'             => 'wp-crontrol',
				'crontrol_message' => '3',
				'crontrol_name'    => rawurlencode( $hook ),
			);
			wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
			exit;
		} elseif ( $deleted ) {
			/**
			 * Fires after all cron events with the given hook are deleted.
			 *
			 * @param string $hook    The hook name.
			 * @param int    $deleted The number of events that were deleted.
			 */
			do_action( 'crontrol/deleted_all_with_hook', $hook, $deleted );

			$redirect = array(
				'page'             => 'wp-crontrol',
				'crontrol_message' => '2',
				'crontrol_name'    => rawurlencode( $hook ),
			);
			wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
			exit;
		} else {
			$redirect = array(
				'page'             => 'wp-crontrol',
				'crontrol_message' => '7',
				'crontrol_name'    => rawurlencode( $hook ),
			);
			wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
			exit;
		}
	} elseif ( isset( $_GET['crontrol_action'] ) && 'run-cron' === $_GET['crontrol_action'] ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to run cron events.', 'wp-crontrol' ), 401 );
		}
		$hook = wp_unslash( $_GET['crontrol_id'] );
		$sig = wp_unslash( $_GET['crontrol_sig'] );
		check_admin_referer( "crontrol-run-cron_{$hook}_{$sig}" );

		$ran = Event\run( $hook, $sig );

		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_message' => '1',
			'crontrol_name'    => rawurlencode( $hook ),
		);

		if ( is_wp_error( $ran ) ) {
			$set = set_message( $ran->get_error_message() );

			// If we can't store the error message in a transient, just display it.
			if ( ! $set ) {
				wp_die(
					esc_html( $ran->get_error_message() ),
					'',
					array(
						'response'  => 500,
						'back_link' => true,
					)
				);
			}
			$redirect['crontrol_message'] = 'error';
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;
	} elseif ( isset( $_GET['crontrol_action'] ) && 'pause-hook' === $_GET['crontrol_action'] ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to pause or resume cron events.', 'wp-crontrol' ), 401 );
		}

		$hook = wp_unslash( $_GET['crontrol_id'] );

		if ( 'crontrol_cron_job' === $hook ) {
			wp_die( esc_html__( 'You are not allowed to pause or resume cron events.', 'wp-crontrol' ), 401 );
		}

		check_admin_referer( "crontrol-pause-hook_{$hook}" );

		$paused = Event\pause( $hook );

		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_message' => '11',
			'crontrol_name'    => rawurlencode( $hook ),
		);

		if ( is_wp_error( $paused ) ) {
			$set = set_message( $paused->get_error_message() );

			// If we can't store the error message in a transient, just display it.
			if ( ! $set ) {
				wp_die(
					esc_html( $paused->get_error_message() ),
					'',
					array(
						'response'  => 500,
						'back_link' => true,
					)
				);
			}
			$redirect['crontrol_message'] = 'error';
		} else {
			/**
			 * Fires after a cron event hook is paused.
			 *
			 * @param string $hook The event hook name.
			 */
			do_action( 'crontrol/paused_hook', $hook );
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;
	} elseif ( isset( $_GET['crontrol_action'] ) && 'resume-hook' === $_GET['crontrol_action'] ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to pause or resume cron events.', 'wp-crontrol' ), 401 );
		}

		$hook = wp_unslash( $_GET['crontrol_id'] );

		if ( 'crontrol_cron_job' === $hook ) {
			wp_die( esc_html__( 'You are not allowed to pause or resume cron events.', 'wp-crontrol' ), 401 );
		}

		check_admin_referer( "crontrol-resume-hook_{$hook}" );

		$resumed = Event\resume( $hook );

		$redirect = array(
			'page'             => 'wp-crontrol',
			'crontrol_message' => '12',
			'crontrol_name'    => rawurlencode( $hook ),
		);

		if ( is_wp_error( $resumed ) ) {
			$set = set_message( $resumed->get_error_message() );

			// If we can't store the error message in a transient, just display it.
			if ( ! $set ) {
				wp_die(
					esc_html( $resumed->get_error_message() ),
					'',
					array(
						'response'  => 500,
						'back_link' => true,
					)
				);
			}
			$redirect['crontrol_message'] = 'error';
		} else {
			/**
			 * Fires after a paused cron event hook is resumed.
			 *
			 * @param string $hook The event hook name.
			 */
			do_action( 'crontrol/resumed_hook', $hook );
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'tools.php' ) ) );
		exit;
	} elseif ( isset( $_POST['crontrol_action'] ) && 'export-event-csv' === $_POST['crontrol_action'] ) {
		check_admin_referer( 'crontrol-export-event-csv', 'crontrol_nonce' );

		$type = isset( $_POST['crontrol_hooks_type'] ) ? wp_unslash( $_POST['crontrol_hooks_type'] ) : 'all';
		$headers = array(
			'hook',
			'arguments',
			'next_run',
			'next_run_gmt',
			'action',
			'schedule',
			'interval',
		);
		$filename = sanitize_file_name( sprintf(
			'cron-events-%s-%s.csv',
			$type,
			gmdate( 'Y-m-d-H.i.s' )
		) );
		$csv = fopen( 'php://output', 'w' );

		if ( false === $csv ) {
			wp_die( esc_html__( 'Could not save CSV file.', 'wp-crontrol' ) );
		}

		$events = Table::get_filtered_events( Event\get() );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header(
			sprintf(
				'Content-Disposition: attachment; filename="%s"',
				esc_attr( $filename )
			)
		);

		fputcsv( $csv, $headers );

		if ( isset( $events[ $type ] ) ) {
			foreach ( $events[ $type ] as $event ) {
				$next_run_local = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $event->timestamp ), 'c' );
				$next_run_utc = gmdate( 'c', $event->timestamp );
				$hook_callbacks = \Crontrol\get_hook_callbacks( $event->hook );

				if ( 'crontrol_cron_job' === $event->hook ) {
					$args = __( 'PHP Code', 'wp-crontrol' );
				} elseif ( empty( $event->args ) ) {
					$args = '';
				} else {
					$args = \Crontrol\json_output( $event->args, false );
				}

				if ( 'crontrol_cron_job' === $event->hook ) {
					$action = 'WP Crontrol';
				} else {
					$callbacks = array();

					foreach ( $hook_callbacks as $callback ) {
						$callbacks[] = $callback['callback']['name'];
					}

					$action = implode( ',', $callbacks );
				}

				if ( $event->schedule ) {
					$schedule_name = Event\get_schedule_name( $event );
					if ( is_wp_error( $schedule_name ) ) {
						$schedule_name = $schedule_name->get_error_message();
					}
				} else {
					$schedule_name = __( 'Non-repeating', 'wp-crontrol' );
				}

				$row = array(
					$event->hook,
					$args,
					$next_run_local,
					$next_run_utc,
					$action,
					$schedule_name,
					(int) $event->interval,
				);
				fputcsv( $csv, $row );
			}
		}

		fclose( $csv );

		exit;
	}
}

/**
 * Adds options & management pages to the admin menu.
 *
 * Run using the 'admin_menu' action.
 *
 * @return void
 */
function action_admin_menu() {
	$schedules = add_options_page(
		esc_html__( 'Cron Schedules', 'wp-crontrol' ),
		esc_html__( 'Cron Schedules', 'wp-crontrol' ),
		'manage_options',
		'wp-crontrol-schedules',
		__NAMESPACE__ . '\admin_options_page'
	);
	$events = add_management_page(
		esc_html__( 'Cron Events', 'wp-crontrol' ),
		esc_html__( 'Cron Events', 'wp-crontrol' ),
		'manage_options',
		'wp-crontrol',
		__NAMESPACE__ . '\admin_manage_page'
	);

	add_action( "load-{$schedules}", __NAMESPACE__ . '\admin_help_tab' );
	add_action( "load-{$events}", __NAMESPACE__ . '\admin_help_tab' );
}

/**
 * Adds a Help tab with links to help resources.
 *
 * @return void
 */
function admin_help_tab() {
	$screen = get_current_screen();

	if ( ! $screen ) {
		return;
	}

	$content = '<p>' . esc_html__( 'There are several places to get help with issues relating to WP-Cron:', 'wp-crontrol' ) . '</p>';
	$content .= '<ul>';
	$content .= '<li>';
	$content .= wp_kses(
		sprintf(
			/* translators: 1: URL to the documentation, 2: WP Crontrol */
			__( '<a href="%1$s">Read the %2$s website</a> which contains information about events that have missed their schedule, problems with spawning a call to the WP-Cron system, and much more.', 'wp-crontrol' ),
			'https://wp-crontrol.com/docs/how-to-use/',
			'WP Crontrol'
		),
		array(
			'a' => array(
				'href' => array(),
			),
		)
	);
	$content .= '</li>';
	$content .= '<li>';
	$content .= wp_kses(
		sprintf(
			/* translators: %s: URL to the documentation */
			__( '<a href="%s">Read the Frequently Asked Questions (FAQ)</a> which cover many common questions and answers.', 'wp-crontrol' ),
			'https://wordpress.org/plugins/wp-crontrol/faq/'
		),
		array(
			'a' => array(
				'href' => array(),
			),
		)
	);
	$content .= '</li>';
	$content .= '<li>';
	$content .= wp_kses(
		sprintf(
			/* translators: %s: URL to the documentation */
			__( '<a href="%s">Read the WordPress.org documentation on WP-Cron</a> for more technical details about the WP-Cron system for developers.', 'wp-crontrol' ),
			'https://developer.wordpress.org/plugins/cron/'
		),
		array(
			'a' => array(
				'href' => array(),
			),
		)
	);
	$content .= '</ul>';

	$screen->add_help_tab(
		array(
			'id'      => 'crontrol-help',
			'title'   => __( 'Help', 'wp-crontrol' ),
			'content' => $content,
		)
	);
}

/**
 * Adds items to the plugin's action links on the Plugins listing screen.
 *
 * @param array<string,string> $actions Array of action links.
 * @return array<string,string> Array of action links.
 */
function plugin_action_links( $actions ) {
	$new = array(
		'crontrol-events'    => sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'tools.php?page=wp-crontrol' ) ),
			esc_html__( 'Events', 'wp-crontrol' )
		),
		'crontrol-schedules' => sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=wp-crontrol-schedules' ) ),
			esc_html__( 'Schedules', 'wp-crontrol' )
		),
		'crontrol-help' => sprintf(
			'<a href="%s">%s</a>',
			'https://wp-crontrol.com/docs/how-to-use/',
			esc_html__( 'Help', 'wp-crontrol' )
		),
	);

	return array_merge( $new, $actions );
}

/**
 * Adds items to the plugin's action links on the Network Admin -> Plugins listing screen.
 *
 * @param array<string,string> $actions     Array of action links.
 * @return array<string,string> Array of action links.
 */
function network_plugin_action_links( $actions ) {
	$new = array(
		'crontrol-help' => sprintf(
			'<a href="%s">%s</a>',
			'https://wp-crontrol.com/docs/how-to-use/',
			esc_html__( 'Help', 'wp-crontrol' )
		),
	);

	return array_merge( $new, $actions );
}

/**
 * Gives WordPress the plugin's set of cron schedules.
 *
 * Called by the `cron_schedules` filter.
 *
 * @param array<string,array<string,(int|string)>> $scheds Array of cron schedule arrays. Usually empty.
 * @return array<string,array<string,(int|string)>> Array of modified cron schedule arrays.
 */
function filter_cron_schedules( array $scheds ) {
	$new_scheds = get_option( 'crontrol_schedules', array() );

	if ( ! is_array( $new_scheds ) ) {
		return $scheds;
	}

	return array_merge( $new_scheds, $scheds );
}

/**
 * Displays the Cron Schedules page for the plugin.
 *
 * @return void
 */
function admin_options_page() {
	$messages = array(
		'2' => array(
			/* translators: %s: The name of the cron schedule. */
			__( 'Deleted the cron schedule %s.', 'wp-crontrol' ),
			'success',
		),
		'3' => array(
			/* translators: %s: The name of the cron schedule. */
			__( 'Added the cron schedule %s.', 'wp-crontrol' ),
			'success',
		),
	);
	if ( isset( $_GET['crontrol_message'] ) && isset( $_GET['crontrol_name'] ) && isset( $messages[ $_GET['crontrol_message'] ] ) ) {
		$hook    = wp_unslash( $_GET['crontrol_name'] );
		$message = wp_unslash( $_GET['crontrol_message'] );

		printf(
			'<div id="crontrol-message" class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $messages[ $message ][1] ),
			sprintf(
				esc_html( $messages[ $message ][0] ),
				'<strong>' . esc_html( $hook ) . '</strong>'
			)
		);
	}

	$table = new Schedule_List_Table();

	$table->prepare_items();

	?>
	<div class="wrap">

	<?php do_tabs(); ?>

	<h1><?php esc_html_e( 'Cron Schedules', 'wp-crontrol' ); ?></h1>

	<?php $table->views(); ?>

	<div id="col-container" class="wp-clearfix">
		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h2><?php esc_html_e( 'Add Cron Schedule', 'wp-crontrol' ); ?></h2>
					<p><?php esc_html_e( 'Adding a new schedule allows you to schedule recurring events at the given interval.', 'wp-crontrol' ); ?></p>
					<form method="post" action="options-general.php?page=wp-crontrol-schedules">
						<div class="form-field form-required">
							<label for="crontrol_schedule_internal_name">
								<?php esc_html_e( 'Internal Name', 'wp-crontrol' ); ?>
							</label>
							<input type="text" value="" id="crontrol_schedule_internal_name" name="crontrol_schedule_internal_name" required/>
						</div>
						<div class="form-field form-required">
							<label for="crontrol_schedule_interval">
								<?php esc_html_e( 'Interval (seconds)', 'wp-crontrol' ); ?>
							</label>
							<input type="number" value="" id="crontrol_schedule_interval" name="crontrol_schedule_interval" min="1" step="1" required/>
						</div>
						<div class="form-field form-required">
							<label for="crontrol_schedule_display_name">
								<?php esc_html_e( 'Display Name', 'wp-crontrol' ); ?>
							</label>
							<input type="text" value="" id="crontrol_schedule_display_name" name="crontrol_schedule_display_name" required/>
						</div>
						<p class="submit">
							<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add Cron Schedule', 'wp-crontrol' ); ?>" name="crontrol_new_schedule"/>
						</p>
						<?php wp_nonce_field( 'crontrol-new-schedule' ); ?>
					</form>
				</div>
			</div>
		</div>
		<div id="col-right">
			<div class="col-wrap">
				<?php $table->display(); ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Clears the doing cron status when an event is unscheduled.
 *
 * What on earth does this function do, and why?
 *
 * Good question. The purpose of this function is to prevent other overdue cron events from firing when an event is run
 * manually with the "Run now" action. WP Crontrol works very hard to ensure that when cron event runs manually that it
 * runs in the exact same way it would run as part of its schedule - via a properly spawned cron with a queued event in
 * place. It does this by queueing an event at time `1` (1 second into 1st January 1970) and then immediately spawning
 * cron (see the `Event\run()` function).
 *
 * The problem this causes is if other events are due then they will all run too, and this isn't desirable because if a
 * site has a large number of stuck events due to a problem with the cron runner then it's not desirable for all those
 * events to run when another is manually run. This happens because WordPress core will attempt to run all due events
 * whenever cron is spawned.
 *
 * The code in this function prevents multiple events from running by changing the value of the `doing_cron` transient
 * when an event gets unscheduled during a manual run, which prevents wp-cron.php from iterating more than one event.
 *
 * The `pre_unschedule_event` filter is used for this because it's just about the only hook available within this loop.
 *
 * Refs:
 * - https://core.trac.wordpress.org/browser/trunk/src/wp-cron.php?rev=47198&marks=127,141#L122
 *
 * @param mixed $pre The pre-flight value of the event unschedule short-circuit. Not used.
 * @return mixed The unaltered pre-flight value.
 */
function maybe_clear_doing_cron( $pre ) {
	if ( defined( 'DOING_CRON' ) && DOING_CRON && isset( $_GET['crontrol-single-event'] ) ) {
		delete_transient( 'doing_cron' );
	}

	return $pre;
}

/**
 * Gets the status of WP-Cron functionality on the site by performing a test spawn if necessary. Cached for one hour when all is well.
 *
 * @param bool $cache Whether to use the cached result from previous calls.
 * @return true|WP_Error Boolean true if the cron spawner is working as expected, or a `WP_Error` object if not.
 */
function test_cron_spawn( $cache = true ) {
	global $wp_version;

	$cron_runner_plugins = array(
		'\HM\Cavalcade\Plugin\Job'         => 'Cavalcade',
		'\Automattic\WP\Cron_Control\Main' => 'Cron Control',
	);

	foreach ( $cron_runner_plugins as $class => $plugin ) {
		if ( class_exists( $class ) ) {
			return new WP_Error( 'crontrol_info', sprintf(
				/* translators: %s: The name of the plugin that controls the running of cron events. */
				__( 'WP-Cron spawning is being managed by the %s plugin.', 'wp-crontrol' ),
				$plugin
			) );
		}
	}

	if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
		return new WP_Error( 'crontrol_info', sprintf(
			/* translators: %s: The name of the PHP constant that is set. */
			__( 'The %s constant is set to true. WP-Cron spawning is disabled.', 'wp-crontrol' ),
			'DISABLE_WP_CRON'
		) );
	}

	if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
		return new WP_Error( 'crontrol_info', sprintf(
			/* translators: %s: The name of the PHP constant that is set. */
			__( 'The %s constant is set to true.', 'wp-crontrol' ),
			'ALTERNATE_WP_CRON'
		) );
	}

	$cached_status = get_transient( 'crontrol-cron-test-ok' );

	if ( $cache && $cached_status ) {
		return true;
	}

	$sslverify     = version_compare( $wp_version, '4.0', '<' );
	$doing_wp_cron = sprintf( '%.22F', microtime( true ) );

	$cron_request = apply_filters( 'cron_request', array(
		'url'  => add_query_arg( 'doing_wp_cron', $doing_wp_cron, site_url( 'wp-cron.php' ) ),
		'key'  => $doing_wp_cron,
		'args' => array(
			'timeout'   => 3,
			'blocking'  => true,
			'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
		),
	), $doing_wp_cron );

	$cron_request['args']['blocking'] = true;

	$result = wp_remote_post( $cron_request['url'], $cron_request['args'] );

	if ( is_wp_error( $result ) ) {
		return $result;
	} elseif ( wp_remote_retrieve_response_code( $result ) >= 300 ) {
		return new WP_Error( 'unexpected_http_response_code', sprintf(
			/* translators: %s: The HTTP response code. */
			__( 'Unexpected HTTP response code: %s', 'wp-crontrol' ),
			intval( wp_remote_retrieve_response_code( $result ) )
		) );
	} else {
		set_transient( 'crontrol-cron-test-ok', 1, 3600 );
		return true;
	}
}

/**
 * Deletes the cached value of the cron status check.
 *
 * @return void
 */
function flush_status_cache() {
	delete_transient( 'crontrol-cron-test-ok' );
}

/**
 * Shows the status of WP-Cron functionality on the site. Only displays a message when there's a problem.
 *
 * @return void
 */
function show_cron_status() {
	if ( ! empty( $_GET['crontrol_action'] ) ) {
		return;
	}

	if ( 'UTC' !== date_default_timezone_get() ) {
		?>
		<div id="crontrol-timezone-warning" class="notice notice-warning">
			<?php
				printf(
					'<p>%1$s</p><p><a href="%2$s">%3$s</a></p>',
					esc_html__( 'PHP default timezone is not set to UTC. This may cause issues with cron event timings.', 'wp-crontrol' ),
					'https://wp-crontrol.com/help/php-default-timezone/',
					esc_html__( 'More information', 'wp-crontrol' )
				);
			?>
		</div>
		<?php
	}

	$status = test_cron_spawn();

	if ( is_wp_error( $status ) ) {
		if ( 'crontrol_info' === $status->get_error_code() ) {
			?>
			<div id="crontrol-status-notice" class="notice notice-info">
				<p><?php echo esc_html( $status->get_error_message() ); ?></p>
			</div>
			<?php
		} else {
			?>
			<div id="crontrol-status-error" class="notice notice-error">
				<?php
				printf(
					'<p>%1$s</p><p><a href="%2$s">%3$s</a></p>',
					sprintf(
						/* translators: %s: Error message text. */
						esc_html__( 'There was a problem spawning a call to the WP-Cron system on your site. This means WP-Cron events on your site may not work. The problem was: %s', 'wp-crontrol' ),
						'</p><p><strong>' . esc_html( $status->get_error_message() ) . '</strong>'
					),
					'https://wp-crontrol.com/help/problems-spawning-wp-cron/',
					esc_html__( 'More information', 'wp-crontrol' )
				);
				?>
			</div>
			<?php
		}
	}
}

/**
 * Get the display name for the site's timezone.
 *
 * @return string The name and UTC offset for the site's timezone.
 */
function get_timezone_name() {
	/** @var string */
	$timezone_string = get_option( 'timezone_string', '' );
	$gmt_offset      = get_option( 'gmt_offset', 0 );

	if ( 'UTC' === $timezone_string || ( empty( $gmt_offset ) && empty( $timezone_string ) ) ) {
		return 'UTC';
	}

	if ( '' === $timezone_string ) {
		return get_utc_offset();
	}

	$parts = explode( '/', $timezone_string );

	return sprintf(
		'%s (%s)',
		str_replace( '_', ' ', end( $parts ) ),
		get_utc_offset()
	);
}

/**
 * Returns a display value for a UTC offset.
 *
 * Examples:
 *   - UTC
 *   - UTC+4
 *   - UTC-6
 *
 * @return string The UTC offset display value.
 */
function get_utc_offset() {
	$offset = get_option( 'gmt_offset', 0 );

	if ( empty( $offset ) ) {
		return 'UTC';
	}

	if ( 0 <= $offset ) {
		$formatted_offset = '+' . (string) $offset;
	} else {
		$formatted_offset = (string) $offset;
	}
	$formatted_offset = str_replace(
		array( '.25', '.5', '.75' ),
		array( ':15', ':30', ':45' ),
		$formatted_offset
	);
	return 'UTC' . $formatted_offset;
}

/**
 * Shows the form used to add/edit cron events.
 *
 * @param bool $editing Whether the form is for the event editor.
 * @return void
 */
function show_cron_form( $editing ) {
	$display_args = '';
	$edit_id      = null;
	$existing     = false;

	if ( $editing && ! empty( $_GET['crontrol_id'] ) ) {
		$edit_id = wp_unslash( $_GET['crontrol_id'] );

		foreach ( Event\get() as $event ) {
			if ( $edit_id === $event->hook && intval( $_GET['crontrol_next_run_utc'] ) === $event->timestamp && $event->sig === $_GET['crontrol_sig'] ) {
				$existing = array(
					'hookname' => $event->hook,
					'next_run' => $event->timestamp, // UTC
					'schedule' => ( $event->schedule ? $event->schedule : '_oneoff' ),
					'sig'      => $event->sig,
					'args'     => $event->args,
				);
				break;
			}
		}

		if ( empty( $existing ) ) {
			?>
			<div id="crontrol-event-not-found" class="notice notice-error">
				<?php
				printf(
					'<p>%1$s</p>',
					esc_html__( 'The event you are trying to edit does not exist.', 'wp-crontrol' )
				);
				?>
			</div>
			<?php
			return;
		}
	}

	$is_editing_php = ( $existing && 'crontrol_cron_job' === $existing['hookname'] );
	$is_editing_url = ( $existing && 'crontrol_url_cron_job' === $existing['hookname'] );

	if ( is_array( $existing ) ) {
		$other_fields  = wp_nonce_field( "crontrol-edit-cron_{$existing['hookname']}_{$existing['sig']}_{$existing['next_run']}", '_wpnonce', true, false );
		$other_fields .= sprintf( '<input name="crontrol_original_hookname" type="hidden" value="%s" />',
			esc_attr( $existing['hookname'] )
		);
		$other_fields .= sprintf( '<input name="crontrol_original_sig" type="hidden" value="%s" />',
			esc_attr( $existing['sig'] )
		);
		$other_fields .= sprintf( '<input name="crontrol_original_next_run_utc" type="hidden" value="%s" />',
			esc_attr( (string) $existing['next_run'] )
		);
		if ( ! empty( $existing['args'] ) ) {
			$display_args = wp_json_encode( $existing['args'] );

			if ( false === $display_args ) {
				$display_args = '';
			}
		}
		$button        = __( 'Update Event', 'wp-crontrol' );
		$next_run_gmt  = gmdate( 'Y-m-d H:i:s', $existing['next_run'] );
		$next_run_date_local = get_date_from_gmt( $next_run_gmt, 'Y-m-d' );
		$next_run_time_local = get_date_from_gmt( $next_run_gmt, 'H:i:s' );
	} else {
		$other_fields = wp_nonce_field( 'crontrol-new-cron', '_wpnonce', true, false );
		$existing     = array(
			'hookname' => '',
			'args'     => array(),
			'next_run' => 'now', // UTC
			'schedule' => false,
		);

		$button        = __( 'Add Event', 'wp-crontrol' );
		$next_run_date_local = '';
		$next_run_time_local = '';
	}

	if ( $is_editing_php && isset( $existing['args']['code'] ) ) {
		// Support the args array format used prior to WP Crontrol 1.16.2
		$existing['args'] = array(
			array(
				'code' => $existing['args']['code'],
				'name' => $existing['args']['name'] ?? '',
				'hash' => null,
			),
		);
	}

	$can_add_php = current_user_can( 'edit_files' ) && ! $editing;
	$allowed = ( ! $is_editing_php || current_user_can( 'edit_files' ) );
	?>
	<div id="crontrol_form" class="wrap narrow">
		<?php
		if ( $allowed ) {
			if ( $editing ) {
				$heading = __( 'Edit Cron Event', 'wp-crontrol' );
			} else {
				$heading = __( 'Add Cron Event', 'wp-crontrol' );
			}

			do_tabs();

			printf(
				'<h1>%s</h1>',
				esc_html( $heading )
			);

			if ( $is_editing_php ) {
				$cron_type = 'php';
			} elseif ( $is_editing_url ) {
				$cron_type = 'url';
			} else {
				$cron_type = 'standard';
			}
			?>
		<form method="post" action="<?php echo esc_url( admin_url( 'tools.php?page=wp-crontrol' ) ); ?>" class="crontrol-edit-event crontrol-edit-event-<?php echo esc_attr( $cron_type ); ?>">
			<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $other_fields;
			?>
			<table class="form-table"><tbody>
				<?php
				if ( $editing ) {
					if ( $is_editing_php ) {
						$action = 'edit_php_cron';
					} elseif ( $is_editing_url ) {
						$action = 'edit_url_cron';
					} else {
						$action = 'edit_cron';
					}

					printf(
						'<input type="hidden" name="crontrol_action" value="%s"/>',
						esc_attr( $action )
					);
				} else {
					?>
					<tr class="hide-if-no-js">
						<th scope="row">
							<?php esc_html_e( 'Event Type', 'wp-crontrol' ); ?>
						</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<?php esc_html_e( 'Event Type', 'wp-crontrol' ); ?>
								</legend>
								<p>
									<label>
										<input type="radio" name="crontrol_action" value="new_cron" checked>
										<?php esc_html_e( 'Standard cron event', 'wp-crontrol' ); ?>
									</label>
								</p>
								<p>
									<label>
										<input type="radio" name="crontrol_action" value="new_url_cron">
										<?php esc_html_e( 'URL cron event', 'wp-crontrol' ); ?>
									</label>
								</p>
								<?php if ( $can_add_php ) { ?>
									<p>
										<label>
											<input type="radio" name="crontrol_action" value="new_php_cron">
											<?php esc_html_e( 'PHP cron event', 'wp-crontrol' ); ?>
										</label>
									</p>
								<?php } ?>
							</fieldset>
						</td>
					</tr>
					<?php
				}

				if ( $is_editing_url || ! $editing ) {
					?>
					<tr class="crontrol-event-url">
						<th valign="top" scope="row">
							<label for="crontrol_url">
								<?php esc_html_e( 'URL', 'wp-crontrol' ); ?>
							</label>
						</th>
						<td>
							<?php
							if ( $is_editing_url && ! check_integrity( $existing['args'][0]['url'], $existing['args'][0]['hash'] ) ) {
								printf(
									'<div class="notice notice-error inline"><p>%1$s</p><p><a href="%2$s">%3$s</a></p></div>',
									esc_html__( 'The URL in this event needs to be checked for integrity. This event will not run until you re-save it.', 'wp-crontrol' ),
									'https://wp-crontrol.com/help/check-cron-events/',
									esc_html__( 'Read what to do', 'wp-crontrol' )
								);
							}
							?>
							<input type="url" class="regular-text code" id="crontrol_url" name="crontrol_url" value="<?php echo esc_url( $is_editing_url ? $existing['args'][0]['url'] : '' ); ?>" />
							<?php do_action( 'crontrol/manage/url', $existing ); ?>
						</td>
					</tr>
					<tr class="crontrol-event-url">
						<th valign="top" scope="row">
							<label for="crontrol_method">
								<?php esc_html_e( 'HTTP Method', 'wp-crontrol' ); ?>
							</label>
						</th>
						<td>
							<select id="crontrol_method" name="crontrol_method">
								<option value="GET">GET</option>
								<option value="POST" <?php selected( $editing ? $existing['args'][0]['method'] === 'POST' : false ); ?>>POST</option>
								<option value="HEAD" <?php selected( $editing ? $existing['args'][0]['method'] === 'HEAD' : false ); ?>>HEAD</option>
								<option value="DELETE" <?php selected( $editing ? $existing['args'][0]['method'] === 'DELETE' : false ); ?>>DELETE</option>
							</select>
							<?php do_action( 'crontrol/manage/method', $existing ); ?>
						</td>
					</tr>
					<tr class="crontrol-event-url">
						<th valign="top" scope="row">
							<label for="crontrol_eventname">
								<?php esc_html_e( 'Event Name (optional)', 'wp-crontrol' ); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" id="crontrol_eventname" name="crontrol_eventname" value="<?php echo esc_attr( $editing ? $existing['args'][0]['name'] : '' ); ?>"/>
							<?php do_action( 'crontrol/manage/eventname', $existing ); ?>
						</td>
					</tr>
					<?php
				}

				if ( $is_editing_php || $can_add_php ) {
					?>
					<tr class="crontrol-event-php">
						<th scope="row">
							<label for="crontrol_hookcode">
								<?php esc_html_e( 'PHP Code', 'wp-crontrol' ); ?>
							</label>
						</th>
						<td>
							<?php
							if ( $is_editing_php && ! check_integrity( $existing['args'][0]['code'], $existing['args'][0]['hash'] ) ) {
								printf(
									'<div class="notice notice-error inline"><p>%1$s</p><p><a href="%2$s">%3$s</a></p></div>',
									esc_html__( 'The PHP code in this event needs to be checked for integrity. This event will not run until you re-save it.', 'wp-crontrol' ),
									'https://wp-crontrol.com/help/check-cron-events/',
									esc_html__( 'Read what to do', 'wp-crontrol' )
								);
							}
							?>
							<p class="description">
								<?php
									printf(
										/* translators: The PHP tag name */
										esc_html__( 'The opening %s tag must not be included.', 'wp-crontrol' ),
										'<code>&lt;?php</code>'
									);
								?>
							</p>
							<p><textarea class="large-text code" rows="10" cols="50" id="crontrol_hookcode" name="crontrol_hookcode"><?php echo esc_textarea( $editing ? $existing['args'][0]['code'] : '' ); ?></textarea></p>
							<?php do_action( 'crontrol/manage/hookcode', $existing ); ?>
						</td>
					</tr>
					<tr class="crontrol-event-php">
						<th scope="row">
							<label for="crontrol_eventname">
								<?php esc_html_e( 'Event Name (optional)', 'wp-crontrol' ); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" id="crontrol_eventname" name="crontrol_eventname" value="<?php echo esc_attr( $editing ? $existing['args'][0]['name'] : '' ); ?>"/>
							<?php do_action( 'crontrol/manage/eventname', $existing ); ?>
						</td>
					</tr>
					<?php
				}

				if ( ! $is_editing_url && ! $is_editing_php ) {
					?>
					<tr class="crontrol-event-standard">
						<th scope="row">
							<label for="crontrol_hookname">
								<?php esc_html_e( 'Hook Name', 'wp-crontrol' ); ?>
							</label>
						</th>
						<td>
							<input type="text" autocorrect="off" autocapitalize="off" spellcheck="false" class="regular-text" id="crontrol_hookname" name="crontrol_hookname" value="<?php echo esc_attr( $existing['hookname'] ); ?>" required />
							<?php do_action( 'crontrol/manage/hookname', $existing ); ?>
						</td>
					</tr>
					<tr class="crontrol-event-standard">
						<th scope="row">
							<label for="crontrol_args">
								<?php esc_html_e( 'Arguments (optional)', 'wp-crontrol' ); ?>
							</label>
						</th>
						<td>
							<input type="text" autocorrect="off" autocapitalize="off" spellcheck="false" class="regular-text code" id="crontrol_args" name="crontrol_args" value="<?php echo esc_attr( $display_args ); ?>" aria-describedby="crontrol_args_description"/>
							<?php do_action( 'crontrol/manage/args', $existing ); ?>
							<p class="description" id="crontrol_args_description">
								<?php
									printf(
										/* translators: 1, 2, and 3: Example values for an input field. */
										esc_html__( 'Use a JSON encoded array, e.g. %1$s, %2$s, or %3$s', 'wp-crontrol' ),
										'<code>[25]</code>',
										'<code>["asdf"]</code>',
										'<code>["i","want",25,"cakes"]</code>'
									);
								?>
							</p>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<th scope="row">
						<label for="crontrol_next_run_date_local">
							<?php esc_html_e( 'Next Run', 'wp-crontrol' ); ?>
						</label>
					</th>
					<td>
						<?php if ( $editing ) { ?>
							<input type="hidden" name="crontrol_next_run_date_local" value="custom">
							<?php
							printf(
								'<input type="date" autocorrect="off" autocapitalize="off" spellcheck="false" name="crontrol_next_run_date_local_custom_date" id="crontrol_next_run_date_local_custom_date" value="%1$s" placeholder="yyyy-mm-dd" pattern="\d{4}-\d{2}-\d{2}" />
								<input type="time" autocorrect="off" autocapitalize="off" spellcheck="false" name="crontrol_next_run_date_local_custom_time" id="crontrol_next_run_date_local_custom_time" value="%2$s" step="1" placeholder="hh:mm:ss" pattern="\d{2}:\d{2}:\d{2}" />',
								esc_attr( $next_run_date_local ),
								esc_attr( $next_run_time_local )
							);
							?>
						<?php } else { ?>
							<fieldset>
								<legend class="screen-reader-text">
									<?php esc_html_e( 'Next Run', 'wp-crontrol' ); ?>
								</legend>
								<p>
									<label>
										<input type="radio" name="crontrol_next_run_date_local" value="now" checked>
										<?php esc_html_e( 'Now', 'wp-crontrol' ); ?>
									</label>
								</p>
								<p>
									<label>
										<input type="radio" name="crontrol_next_run_date_local" value="+1 day">
										<?php esc_html_e( 'Tomorrow', 'wp-crontrol' ); ?>
									</label>
								</p>
								<p>
									<label>
										<input type="radio" name="crontrol_next_run_date_local" value="custom" id="crontrol_next_run_date_local_custom" <?php checked( $editing ); ?>>
										<?php
										printf(
											/* translators: %s: An input field for specifying a date and time */
											esc_html__( 'At this time: %s', 'wp-crontrol' ),
											sprintf(
												'<br><br>
												<input type="date" autocorrect="off" autocapitalize="off" spellcheck="false" name="crontrol_next_run_date_local_custom_date" id="crontrol_next_run_date_local_custom_date" value="%1$s" placeholder="yyyy-mm-dd" pattern="\d{4}-\d{2}-\d{2}" />
												<input type="time" autocorrect="off" autocapitalize="off" spellcheck="false" name="crontrol_next_run_date_local_custom_time" id="crontrol_next_run_date_local_custom_time" value="%2$s" step="1" placeholder="hh:mm:ss" pattern="\d{2}:\d{2}:\d{2}" />',
												esc_attr( $next_run_date_local ),
												esc_attr( $next_run_time_local )
											)
										);
										?>
									</label>
								</p>
							</fieldset>
						<?php } ?>

						<?php do_action( 'crontrol/manage/next_run', $existing ); ?>

						<p class="description">
							<?php
								printf(
									/* translators: %s Timezone name. */
									esc_html__( 'Timezone: %s', 'wp-crontrol' ),
									esc_html( get_timezone_name() )
								);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="crontrol_schedule">
							<?php echo esc_html_x( 'Schedule', 'noun', 'wp-crontrol' ); ?>
						</label>
					</th>
					<td>
						<?php Schedule\dropdown( $existing['schedule'] ); ?>
						<?php do_action( 'crontrol/manage/schedule', $existing ); ?>
					</td>
				</tr>
			</tbody></table>
			<p class="submit">
				<input type="submit" class="button button-primary" value="<?php echo esc_attr( $button ); ?>"/>
			</p>
			<p class="description">
				<?php
					echo esc_html( sprintf(
						/* translators: 1: Date and time, 2: Timezone */
						__( 'Site time when page loaded: %1$s, %2$s', 'wp-crontrol' ),
						date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
						get_timezone_name()
					) );
				?>
			</p>
		</form>
		<?php } else { ?>
			<div class="error inline">
				<p><?php esc_html_e( 'You cannot add, edit, or delete PHP cron events because your user account does not have the ability to edit files.', 'wp-crontrol' ); ?></p>
			</div>
		<?php } ?>
	</div>
	<?php
}

/**
 * Displays the Cron Events page for the plugin.
 *
 * @return void
 */
function admin_manage_page() {
	$messages = array(
		'1'  => array(
			/* translators: %s: The name of the cron event. */
			__( 'Scheduled the cron event %s to run now. The original event will not be affected.', 'wp-crontrol' ),
			'success',
		),
		'2'  => array(
			/* translators: %s: The name of the cron event. */
			__( 'Deleted all %s cron events.', 'wp-crontrol' ),
			'success',
		),
		'3'  => array(
			/* translators: %s: The name of the cron event. */
			__( 'There are no %s cron events to delete.', 'wp-crontrol' ),
			'info',
		),
		'4'  => array(
			/* translators: %s: The name of the cron event. */
			__( 'Saved the cron event %s.', 'wp-crontrol' ),
			'success',
		),
		'6'  => array(
			/* translators: %s: The name of the cron event. */
			__( 'Deleted the cron event %s.', 'wp-crontrol' ),
			'success',
		),
		'7'  => array(
			/* translators: %s: The name of the cron event. */
			__( 'Failed to the delete the cron event %s.', 'wp-crontrol' ),
			'error',
		),
		'8'  => array(
			/* translators: %s: The name of the cron event. */
			__( 'Failed to the execute the cron event %s.', 'wp-crontrol' ),
			'error',
		),
		'9'  => array(
			__( 'Deleted the selected cron events.', 'wp-crontrol' ),
			'success',
		),
		'10' => array(
			/* translators: %s: The name of the cron event. */
			__( 'Failed to save the cron event %s.', 'wp-crontrol' ),
			'error',
		),
		'11' => array(
			/* translators: %s: The name of the cron event. */
			__( 'Paused the %s hook.', 'wp-crontrol' ),
			'success',
		),
		'12' => array(
			/* translators: %s: The name of the cron event. */
			__( 'Resumed the %s hook.', 'wp-crontrol' ),
			'success',
		),
		'13' => array(
			__( 'URL cron event saved.', 'wp-crontrol' ),
			'success',
		),
		'14' => array(
			__( 'PHP cron event saved.', 'wp-crontrol' ),
			'success',
		),
		'error' => array(
			__( 'An unknown error occurred.', 'wp-crontrol' ),
			'error',
		),
	);

	if ( isset( $_GET['crontrol_name'] ) && isset( $_GET['crontrol_message'] ) && isset( $messages[ $_GET['crontrol_message'] ] ) ) {
		$hook    = wp_unslash( $_GET['crontrol_name'] );
		$message = wp_unslash( $_GET['crontrol_message'] );
		$link    = '';

		if ( 'error' === $message ) {
			$error = get_message();

			if ( $error ) {
				$messages['error'][0] = $error;
			}
		}

		printf(
			'<div id="crontrol-message" class="notice notice-%1$s is-dismissible"><p>%2$s%3$s</p></div>',
			esc_attr( $messages[ $message ][1] ),
			sprintf(
				esc_html( $messages[ $message ][0] ),
				'<strong>' . esc_html( $hook ) . '</strong>'
			),
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$link
		);
	}

	$tabs = get_tab_states();

	switch ( true ) {
		case $tabs['events']:
			$table = Event\get_list_table();
			?>
			<div class="wrap">
				<?php do_tabs(); ?>

				<h1 class="wp-heading-inline"><?php esc_html_e( 'Cron Events', 'wp-crontrol' ); ?></h1>

				<?php echo '<a href="' . esc_url( admin_url( 'tools.php?page=wp-crontrol&crontrol_action=new-cron' ) ) . '" class="page-title-action">' . esc_html__( 'Add New Cron Event', 'wp-crontrol' ) . '</a>'; ?>

				<hr class="wp-header-end">

				<?php $table->views(); ?>

				<form id="posts-filter" method="get" action="tools.php">
					<input type="hidden" name="page" value="wp-crontrol" />
					<?php $table->search_box( esc_html__( 'Search Hook Names', 'wp-crontrol' ), 'cron-event' ); ?>
				</form>

				<form method="post" action="tools.php?page=wp-crontrol">
					<div class="table-responsive">
						<?php $table->display(); ?>
					</div>
				</form>

				<p class="description">
					<?php
						echo esc_html( sprintf(
							/* translators: 1: Date and time, 2: Timezone */
							__( 'Site time when page loaded: %1$s, %2$s', 'wp-crontrol' ),
							date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
							get_timezone_name()
						) );
					?>
				</p>
			</div>
			<?php

			break;

		case $tabs['add-event']:
			show_cron_form( false );
			break;

		case $tabs['edit-event']:
			show_cron_form( true );
			break;

	}
}

/**
 * Get the states of the various cron-related tabs.
 *
 * @return array<string,bool> Array of states keyed by tab name.
 */
function get_tab_states() {
	$tabs = array(
		'events'     => ( ! empty( $_GET['page'] ) && 'wp-crontrol' === $_GET['page'] && empty( $_GET['crontrol_action'] ) ),
		'schedules'  => ( ! empty( $_GET['page'] ) && 'wp-crontrol-schedules' === $_GET['page'] ),
		'add-event'  => ( ! empty( $_GET['crontrol_action'] ) && 'new-cron' === $_GET['crontrol_action'] ),
		'edit-event' => ( ! empty( $_GET['crontrol_action'] ) && 'edit-cron' === $_GET['crontrol_action'] ),
	);

	$tabs = apply_filters( 'crontrol/tabs', $tabs );

	return $tabs;
}

/**
 * Output the cron-related tabs if we're on a cron-related admin screen.
 *
 * @return void
 */
function do_tabs() {
	$tabs = get_tab_states();
	$tab  = array_filter( $tabs );

	if ( ! $tab ) {
		return;
	}

	$tab   = array_keys( $tab );
	$tab   = reset( $tab );
	$links = array(
		'events'    => array(
			'tools.php?page=wp-crontrol',
			__( 'Cron Events', 'wp-crontrol' ),
		),
		'schedules' => array(
			'options-general.php?page=wp-crontrol-schedules',
			__( 'Cron Schedules', 'wp-crontrol' ),
		),
	);

	?>
	<div id="crontrol-header">
		<nav class="nav-tab-wrapper">
			<?php
			foreach ( $links as $id => $link ) {
				if ( ! empty( $tabs[ $id ] ) ) {
					printf(
						'<a href="%s" class="nav-tab nav-tab-active">%s</a>',
						esc_url( $link[0] ),
						esc_html( $link[1] )
					);
				} else {
					printf(
						'<a href="%s" class="nav-tab">%s</a>',
						esc_url( $link[0] ),
						esc_html( $link[1] )
					);
				}
			}

			if ( $tabs['add-event'] ) {
				printf(
					'<span class="nav-tab nav-tab-active">%s</span>',
					esc_html__( 'Add Cron Event', 'wp-crontrol' )
				);
			} elseif ( $tabs['edit-event'] ) {
				printf(
					'<span class="nav-tab nav-tab-active">%s</span>',
					esc_html__( 'Edit Cron Event', 'wp-crontrol' )
				);
			}
			?>
		</nav>
		<?php
		do_action( 'crontrol/tab-header', $tab, $tabs );
		?>
	</div>
	<?php
}

/**
 * Returns an array of the callback functions that are attached to the given hook name.
 *
 * @param string $name The hook name.
 * @return array<int,array<string,mixed>> Array of callbacks attached to the hook.
 * @phpstan-return array<int,array{
 *   priority: int,
 *   callback: array<string,mixed>,
 * }>
 */
function get_hook_callbacks( $name ) {
	global $wp_filter;

	$actions = array();

	if ( isset( $wp_filter[ $name ] ) ) {
		// See http://core.trac.wordpress.org/ticket/17817.
		$action = $wp_filter[ $name ];

		/**
		 * @var int $priority
		 */
		foreach ( $action as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$callback = populate_callback( $callback );

				if ( __NAMESPACE__ . '\\pauser()' === $callback['name'] ) {
					continue;
				}

				$actions[] = array(
					'priority' => $priority,
					'callback' => $callback,
				);
			}
		}
	}

	return $actions;
}

/**
 * Populates the details of the given callback function.
 *
 * @param array<string,mixed> $callback A callback entry.
 * @phpstan-param array{
 *   function: string|array<int,mixed>|object,
 *   accepted_args: int,
 * } $callback
 * @return array<string,mixed> The updated callback entry.
 */
function populate_callback( array $callback ) {
	// If Query Monitor is installed, use its rich callback analysis.
	if ( method_exists( '\QM_Util', 'populate_callback' ) ) {
		return \QM_Util::populate_callback( $callback );
	}

	if ( is_string( $callback['function'] ) && ( false !== strpos( $callback['function'], '::' ) ) ) {
		$callback['function'] = explode( '::', $callback['function'] );
	}

	if ( is_array( $callback['function'] ) ) {
		if ( is_object( $callback['function'][0] ) ) {
			$class  = get_class( $callback['function'][0] );
			$access = '->';
		} else {
			$class  = $callback['function'][0];
			$access = '::';
		}

		$callback['name'] = $class . $access . $callback['function'][1] . '()';
	} elseif ( is_object( $callback['function'] ) ) {
		if ( is_a( $callback['function'], 'Closure' ) ) {
			$callback['name'] = 'Closure';
		} else {
			$class = get_class( $callback['function'] );

			$callback['name'] = $class . '->__invoke()';
		}
	} else {
		$callback['name'] = $callback['function'] . '()';
	}

	if ( ! method_exists( '\QM_Util', 'populate_callback' ) && ! is_callable( $callback['function'] ) ) {
		$callback['error'] = new WP_Error(
			'not_callable',
			sprintf(
				/* translators: %s: Function name */
				__( 'Function %s does not exist', 'wp-crontrol' ),
				$callback['name']
			)
		);
	}

	return $callback;
}

/**
 * Returns a user-friendly representation of the callback function.
 *
 * @param mixed[] $callback The callback entry.
 * @return string The displayable version of the callback name.
 */
function output_callback( array $callback ) {
	$qm   = WP_PLUGIN_DIR . '/query-monitor/query-monitor.php';
	$html = plugin_dir_path( $qm ) . 'output/Html.php';

	if ( ! empty( $callback['callback']['error'] ) ) {
		$return  = '<code>' . $callback['callback']['name'] . '</code>';
		$return .= '<br><span class="status-crontrol-error"><span class="dashicons dashicons-warning" aria-hidden="true"></span> ';
		$return .= esc_html( $callback['callback']['error']->get_error_message() );
		$return .= '</span>';
		return $return;
	}

	// If Query Monitor is installed, use its rich callback output.
	if ( class_exists( '\QueryMonitor' ) && file_exists( $html ) ) {
		require_once $html;

		if ( class_exists( '\QM_Output_Html' ) ) {
			return \QM_Output_Html::output_filename(
				$callback['callback']['name'],
				$callback['callback']['file'],
				$callback['callback']['line']
			);
		}
	}

	return '<code>' . $callback['callback']['name'] . '</code>';
}

/**
 * Pretty-prints the difference in two times.
 *
 * @param int $older_date Unix timestamp.
 * @param int $newer_date Unix timestamp.
 * @return string The pretty time_since value
 * @link http://binarybonsai.com/code/timesince.txt
 */
function time_since( $older_date, $newer_date ) {
	return interval( $newer_date - $older_date );
}

/**
 * Converts a period of time in seconds into a human-readable format representing the interval.
 *
 * Example:
 *
 *     echo \Crontrol\interval( 90 );
 *     // 1 minute 30 seconds
 *
 * @param  int|float $since A period of time in seconds.
 * @return string An interval represented as a string.
 */
function interval( $since ) {
	// Array of time period chunks.
	$chunks = array(
		/* translators: %s: The number of years in an interval of time. */
		array( YEAR_IN_SECONDS, _n_noop( '%s year', '%s years', 'wp-crontrol' ) ),
		/* translators: %s: The number of months in an interval of time. */
		array( MONTH_IN_SECONDS, _n_noop( '%s month', '%s months', 'wp-crontrol' ) ),
		/* translators: %s: The number of weeks in an interval of time. */
		array( WEEK_IN_SECONDS, _n_noop( '%s week', '%s weeks', 'wp-crontrol' ) ),
		/* translators: %s: The number of days in an interval of time. */
		array( DAY_IN_SECONDS, _n_noop( '%s day', '%s days', 'wp-crontrol' ) ),
		/* translators: %s: The number of hours in an interval of time. */
		array( HOUR_IN_SECONDS, _n_noop( '%s hour', '%s hours', 'wp-crontrol' ) ),
		/* translators: %s: The number of minutes in an interval of time. */
		array( MINUTE_IN_SECONDS, _n_noop( '%s minute', '%s minutes', 'wp-crontrol' ) ),
		/* translators: %s: The number of seconds in an interval of time. */
		array( 1, _n_noop( '%s second', '%s seconds', 'wp-crontrol' ) ),
	);

	if ( $since <= 0 ) {
		return __( 'now', 'wp-crontrol' );
	}

	/**
	 * We only want to output two chunks of time here, eg:
	 * x years, xx months
	 * x days, xx hours
	 * so there's only two bits of calculation below:
	 */

	// Step one: the first chunk.
	foreach ( array_keys( $chunks ) as $i ) {
		$seconds = $chunks[ $i ][0];
		$name    = $chunks[ $i ][1];

		// Finding the biggest chunk (if the chunk fits, break).
		$count = (int) floor( $since / $seconds );
		if ( $count ) {
			break;
		}
	}

	// Set output var.
	$output = sprintf( translate_nooped_plural( $name, $count, 'wp-crontrol' ), $count );

	// Step two: the second chunk.
	if ( $i + 1 < count( $chunks ) ) {
		$seconds2 = $chunks[ $i + 1 ][0];
		$name2    = $chunks[ $i + 1 ][1];
		$count2   = (int) floor( ( $since - ( $seconds * $count ) ) / $seconds2 );
		if ( $count2 ) {
			// Add to output var.
			$output .= ' ' . sprintf( translate_nooped_plural( $name2, $count2, 'wp-crontrol' ), $count2 );
		}
	}

	return $output;
}

/**
 * Sets up the Events listing screen.
 *
 * @return void
 */
function setup_manage_page() {
	// Initialise the list table
	Event\get_list_table();
}

/**
 * Registers the stylesheet and JavaScript for the admin areas.
 *
 * @param string $hook_suffix The admin screen ID.
 * @return void
 */
function enqueue_assets( $hook_suffix ) {
	$tab = get_tab_states();

	if ( ! array_filter( $tab ) ) {
		return;
	}

	wp_enqueue_style(
		'wp-crontrol',
		plugin_dir_url( PLUGIN_FILE ) . 'css/wp-crontrol.css',
		array(
			'dashicons',
		),
		WP_CRONTROL_VERSION
	);
	wp_enqueue_script(
		'wp-crontrol',
		plugin_dir_url( PLUGIN_FILE ) . 'js/wp-crontrol.js',
		array(),
		WP_CRONTROL_VERSION,
		true
	);

	$vars = array();

	if ( ! empty( $tab['add-event'] ) || ! empty( $tab['edit-event'] ) ) {
		if ( current_user_can( 'edit_files' ) ) {
			$settings = wp_enqueue_code_editor( array(
				'type' => 'text/x-php',
			) );

			if ( false !== $settings ) {
				$vars['codeEditor'] = $settings;
			}
		}
	}

	wp_localize_script( 'wp-crontrol', 'wpCrontrol', $vars );
}

/**
 * Filters the list of query arguments which get removed from admin area URLs in WordPress.
 *
 * @param array<int,string> $args List of removable query arguments.
 * @return array<int,string> Updated list of removable query arguments.
 */
function filter_removable_query_args( array $args ) {
	return array_merge( $args, array(
		'crontrol_message',
		'crontrol_name',
	) );
}

/**
 * Returns an array of cron event hooks that are persistently added by WordPress core.
 *
 * @return array<int,string> Array of hook names.
 */
function get_persistent_core_hooks() {
	return array(
		'wp_update_plugins', // 2.7.0
		'wp_update_themes', // 2.7.0
		'wp_version_check', // 2.7.0
		'wp_scheduled_delete', // 2.9.0
		'update_network_counts', // 3.1.0
		'wp_scheduled_auto_draft_delete', // 3.4.0
		'delete_expired_transients', // 4.9.0
		'wp_privacy_delete_old_export_files', // 4.9.6
		'recovery_mode_clean_expired_keys', // 5.2.0
		'wp_site_health_scheduled_check', // 5.4.0
		'wp_https_detection', // 5.7.0
		'wp_update_user_counts', // 6.0.0
	);
}

/**
 * Returns an array of all cron event hooks that are added by WordPress core.
 *
 * @return array<int,string> Array of hook names.
 */
function get_all_core_hooks() {
	return array_merge(
		get_persistent_core_hooks(),
		array(
			'do_pings', // 2.1.0
			'publish_future_post', // 2.1.0
			'importer_scheduled_cleanup', // 2.5.0
			'upgrader_scheduled_cleanup', // 3.2.2
			'wp_maybe_auto_update', // 3.7.0
			'wp_split_shared_term_batch', // 4.3.0
			'wp_update_comment_type_batch', // 5.5.0
			'wp_delete_temp_updater_backups', // 6.3.0
		)
	);
}

/**
 * Returns an array of cron schedules that are added by WordPress core.
 *
 * @return array<int,string> Array of schedule names.
 */
function get_core_schedules() {
	return array(
		'hourly',
		'twicedaily',
		'daily',
		'weekly',
	);
}

/**
 * Encodes some input as JSON for output.
 *
 * @param mixed $input  The input.
 * @param bool  $pretty Whether to pretty print the output. Default true.
 * @return string The JSON-encoded output.
 */
function json_output( $input, $pretty = true ) {
	$json_options = 0;

	if ( defined( 'JSON_UNESCAPED_SLASHES' ) ) {
		// phpcs:ignore PHPCompatibility.Constants.NewConstants.json_unescaped_slashesFound
		$json_options |= JSON_UNESCAPED_SLASHES;
	}

	if ( $pretty && defined( 'JSON_PRETTY_PRINT' ) ) {
		$json_options |= JSON_PRETTY_PRINT;
	}

	$output = wp_json_encode( $input, $json_options );

	if ( false === $output ) {
		$output = '';
	}

	return $output;
}

/**
 * Fetches the URL in a URL cron event using the HTTP API.
 *
 * The URL that's saved in a URL cron event is protected with an integrity check which prevents it from being fetched
 * if the URL is tampered with.
 *
 * URL cron events are secured via an integrity check that makes use of an HMAC to store a hash of the URL alongside
 * the code when the event is saved. When the event runs, the hash is checked to ensure the integrity of the URL and
 * confirm that it has not been tampered with. WP Crontrol will not fetch the URL if the hashes do not match or if
 * a stored hash is not present.
 *
 * If an attacker with database-level access were to modify the URL in an event in an attempt to fetch an arbitrary
 * URL (for example to perform an SSRF), the HTTP request would not be performed.
 *
 * @link https://wp-crontrol.com/docs/url-cron-events/
 *
 * @throws Exception If the request fails.
 *
 * @param array<string,string> $args The event args array.
 * @phpstan-param array{
 *   url: string,
 *   name: string,
 *   method: string,
 *   hash: string,
 * } $args
 */
function action_url_cron_event( array $args ): void {
	list(
		'url' => $url,
		'method' => $method,
		'hash' => $hash,
	) = $args;

	if ( empty( $hash ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error(
			sprintf(
				'WP Crontrol: The stored hash is missing for a URL cron event; for more information see %s',
				esc_url_raw( admin_url( 'tools.php?page=wp-crontrol&crontrol_hooks_type=url' ) ),
			),
			E_USER_WARNING
		);
		return;
	}

	// Check the integrity of the URL.
	if ( ! check_integrity( $url, $hash ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error(
			sprintf(
				'WP Crontrol: The stored hash for a URL cron event is not valid; for more information see %s',
				esc_url_raw( admin_url( 'tools.php?page=wp-crontrol&crontrol_hooks_type=url' ) ),
			),
			E_USER_WARNING
		);
		return;
	}

	$request_args = array(
		'timeout' => 30,
		'method'  => $method,
		'user-agent' => sprintf(
			'WP Crontrol; %s',
			home_url( '/' )
		),
	);
	$response = wp_remote_request( $url, $request_args );

	if ( is_wp_error( $response ) ) {
		throw new Exception(
			esc_html(
				sprintf(
					'WP Crontrol: Failed to fetch URL %s: %s',
					$url,
					$response->get_error_message()
				)
			)
		);
	}

	$code = wp_remote_retrieve_response_code( $response );
	$message = wp_remote_retrieve_response_message( $response );

	if ( $code < 200 || $code >= 300 ) {
		throw new Exception(
			esc_html(
				sprintf(
					'WP Crontrol: Unexpected response code for URL %s: HTTP %s %s',
					$url,
					$code,
					$message
				)
			)
		);
	}
}

/**
 * Evaluates the code in a PHP cron event using eval.
 *
 * Security: Only users with the `edit_files` capability can manage PHP cron events. This means if a user cannot edit
 * files on the site (eg. through the Plugin Editor or Theme Editor) then they cannot edit or add a PHP cron event. By
 * default, only Administrators have this capability, and with Multisite enabled only Super Admins have this capability.
 *
 * If file editing has been disabled via the `DISALLOW_FILE_MODS` or `DISALLOW_FILE_EDIT` configuration constants then
 * no user will have the `edit_files` capability, which means editing or adding a PHP cron event will not be permitted.
 *
 * Therefore, the user access level required to execute arbitrary PHP code does not change with WP Crontrol activated.
 *
 * The PHP code that's saved in a PHP cron event is protected with an integrity check which prevents it from being executed
 * if the code is tampered with.
 *
 * PHP cron events are secured via an integrity check that makes use of an HMAC to store a hash of the PHP code alongside
 * the code when the event is saved. When the event runs, the hash is checked to ensure the integrity of the PHP code and
 * confirm that it has not been tampered with. WP Crontrol will not execute the PHP code if the hashes do not match or if
 * a stored hash is not present.
 *
 * If an attacker with database-level access were to modify the PHP code in an event in an attempt to execute arbitrary
 * code, the code would no longer execute.
 *
 * @link https://wp-crontrol.com/docs/php-cron-events/
 *
 * @param array<string,string>|string $args The event args array, or a string containing the PHP code to evaluate.
 * @phpstan-param array{
 *   code?: string,
 *   name?: string,
 *   hash?: string,
 * }|string $args
 */
function action_php_cron_event( $args ): void {
	if ( is_string( $args ) ) {
		// Prior to WP Crontrol 1.16.2, PHP cron events were saved with the associative arguments array at the top
		// level. This means arguments are passed as individual parameters to this function and the first parameter
		// contains the PHP code.
		$code = $args;
		$hash = null;
	} else {
		// Since WP Crontrol 1.16.2, PHP cron events are stored with the associative arguments array as the first element
		// in the args list. This means arguments are passed as a single associative array parameter to this function.
		$code = $args['code'] ?? null;
		$hash = $args['hash'] ?? null;
	}

	if ( empty( $hash ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error(
			sprintf(
				'WP Crontrol: The stored hash is missing for a PHP cron event; for more information see %s',
				esc_url_raw( admin_url( 'tools.php?page=wp-crontrol&crontrol_hooks_type=php' ) ),
			),
			E_USER_WARNING
		);
		return;
	}

	// Check the integrity of the PHP code.
	if ( ! check_integrity( $code, $hash ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error(
			sprintf(
				'WP Crontrol: The stored hash for a PHP cron event is not valid; for more information see %s',
				esc_url_raw( admin_url( 'tools.php?page=wp-crontrol&crontrol_hooks_type=php' ) ),
			),
			E_USER_WARNING
		);
		return;
	}

	// Please see the function description above for information about the safety of this code.
	// phpcs:ignore Squiz.PHP.Eval.Discouraged
	eval( $code );
}
