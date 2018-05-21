<?php
/**
 * Pickle Calendar Event Details
 *
 * @package PickleCalendar
 * @since   1.0.0
 */
 
/**
 * Pickle_Calendar_Event_Details class.
 */
class Pickle_Calendar_Event_Details {

    /**
     * Construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        if ( is_admin() ) :
            add_action( 'load-post.php', array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
        endif;
    }

    /**
     * Init function.
     *
     * @access public
     * @return void
     */
    public function init_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_styles' ) );
    }

    /**
     * Admin scripts/styles.
     *
     * @access public
     * @return void
     */
    public function admin_scripts_styles() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'pced-script', PICKLE_CALENDAR_URL . 'js/event-details.js', array( 'jquery-ui-datepicker' ), '0.1.0', true );
        wp_enqueue_script( 'pc-repeat-field-script', PICKLE_CALENDAR_URL . 'js/repeat-field.js', array( 'jquery' ), '0.1.0', true );

        wp_enqueue_style( 'jquery-ui-style', PICKLE_CALENDAR_URL . 'css/jquery-ui.min.css', '', '1.12.1' );
        wp_enqueue_style( 'bted-style', PICKLE_CALENDAR_URL . 'css/event-details.css', '', '0.1.0' );
    }

    /**
     * Add metabox function.
     *
     * @access public
     * @return void
     */
    public function add_metabox() {
        add_meta_box(
            'event-details',
            __( 'Event Details', 'pickle-calendar' ),
            array( $this, 'render_metabox' ),
            'pcevent',
            'advanced',
            'default'
        );

    }

    /**
     * Render metabox function.
     *
     * @access public
     * @param mixed $post (post data).
     * @return html
     */
    public function render_metabox( $post ) {
        $html = '';
        $default_dates = array(
            array(
                'start_date' => '',
                'end_date' => '',
            ),
        );
        $event_dates = picklecalendar()->calendar->get_event_dates( $post->ID );
        $dates = $this->_wp_parse_args( $event_dates, $default_dates );

        $html .= wp_nonce_field( 'update_settings', 'boomi_trust_admin', true, false );

        foreach ( $dates as $key => $date ) :

            $html .= '<div class="event-date-wrap" data-row-id="' . $key . '">';

                    $html .= '<label for="start_date">Start Date</label>';
                    $html .= '<input type="text" name="details[dates][' . $key . '][start_date]" id="" class="pcdetail-pickr start-date" value="' . $date['start_date'] . '" />';

                    $html .= '<label for="end_date">End Date</label>';
                    $html .= '<input type="text" name="details[dates][' . $key . '][end_date]" id="" class="pcdetail-pickr end-date" value="' . $date['end_date'] . '" />';

                    $html .= '<button class="pc-remove-row">-</button>';

            $html .= '</div>';

        endforeach;

        $html .= '<a href="" class="button pc-repeater" data-field=".event-date-wrap">+</a>';

        echo $html;
    }

    /**
     * Save metabox function.
     *
     * @access public
     * @param mixed $post_id (integer).
     * @param mixed $post (post).
     * @return void
     */
    public function save_metabox( $post_id, $post ) {
        global $wpdb;

        $nonce_name   = isset( $_POST['boomi_trust_admin'] ) ? $_POST['boomi_trust_admin'] : '';
        $nonce_action = 'update_settings';

        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }

        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        // delete all existing dates //
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->postmeta WHERE post_id = %d AND ( meta_key LIKE %s OR meta_key LIKE %s )",
                $post_id,
                '_start_date_%',
                '_end_date_%'
            )
        );

        foreach ( $_POST['details']['dates'] as $key => $dates ) :
            add_post_meta( $post_id, '_start_date_' . sanitize_key( $key ), $dates['start_date'] );
            add_post_meta( $post_id, '_end_date_' . sanitize_key( $key ), $dates['end_date'] );
        endforeach;
    }

    /**
     * Parse args function.
     *
     * @access public
     * @param mixed &$a (array).
     * @param mixed $b (array).
     * @return void
     */
    public function _wp_parse_args( &$a, $b ) {
        $a = (array) $a;
        $b = (array) $b;
        $result = $b;
        foreach ( $a as $k => &$v ) {
            if ( is_array( $v ) && isset( $result[ $k ] ) ) {
                $result[ $k ] = $this->_wp_parse_args( $v, $result[ $k ] );
            } else {
                $result[ $k ] = $v;
            }
        }
        return $result;
    }
}

new Pickle_Calendar_Event_Details();
