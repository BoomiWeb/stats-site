<?php
/**
 * Pickle Calendar install
 *
 * @package PickleCalendar
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Pickle_Calendar_Install class.
 */
class Pickle_Calendar_Install {

    /**
     * Updates
     *
     * @var mixed
     * @access private
     * @static
     */
    private static $updates = array(
        '1.2.0' => array(
            'pcl_update_120_taxonomies',
        ),
    );

    /**
     * Init function.
     *
     * @access public
     * @static
     * @return void
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
    }

    /**
     * Check version function.
     *
     * @access public
     * @static
     * @return void
     */
    public static function check_version() {
        if ( self::is_new_install() ) :
            self::install();
        elseif ( get_option( 'pickle_calendar_version' ) !== picklecalendar()->version ) :
            self::update_version();
            self::update();
        endif;
    }

    /**
     * Install function.
     *
     * @access public
     * @static
     * @return void
     */
    public static function install() {
        if ( ! is_blog_installed() ) {
            return;
        }

        // Check if we are not already running this routine.
        if ( 'yes' === get_transient( 'pickle_calendar_installing' ) ) {
            return;
        }

        // If we made it till here nothing is running yet, lets set the transient now.
        set_transient( 'pickle_calendar_installing', 'yes', MINUTE_IN_SECONDS * 10 );

        // install stuff.
        self::update_version();
        self::update();

        delete_transient( 'pickle_calendar_installing' );
    }

    /**
     * Update function.
     *
     * @access private
     * @static
     * @return void
     */
    private static function update() {
        $current_version = get_option( 'pickle_calendar_version' );

        foreach ( self::get_update_callbacks() as $version => $update_callbacks ) :
            if ( version_compare( $current_version, $version, '<=' ) ) :
                foreach ( $update_callbacks as $update_callback ) :
                    $update_callback();
                endforeach;
            endif;
        endforeach;
    }

    /**
     * Get update callbacks function.
     *
     * @access public
     * @static
     * @return self
     */
    public static function get_update_callbacks() {
        return self::$updates;
    }

    /**
     * Update version function.
     *
     * @access private
     * @static
     * @return void
     */
    private static function update_version() {
        delete_option( 'pickle_calendar_version' );

        add_option( 'pickle_calendar_version', picklecalendar()->version );
    }

    /**
     * Is new install function.
     *
     * @access protected
     * @static
     * @return boolean
     */
    protected static function is_new_install() {
        if ( ! get_option( 'pickle_calendar_version', 0 ) ) {
            return true;
        }

        return false;
    }

}

Pickle_Calendar_Install::init();
