<?php

/**
 * Plugin Name: Pickle Calendar
 * Plugin URI:
 * Description: Pickle Calendar
 * Version: 1.2.0-beta.4
 * Author: Erik Mitchell
 * Author URI:
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: pickle-calendar
 * Domain Path: /languages
 *
 * @package PickleCalendar
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define PICKLE_CALENDAR_PLUGIN_FILE.
if ( ! defined( 'PICKLE_CALENDAR_PLUGIN_FILE' ) ) {
    define( 'PICKLE_CALENDAR_PLUGIN_FILE', __FILE__ );
}

// Include the main PickleCalendar class.
if ( ! class_exists( 'PickleCalendar' ) ) {
    include_once dirname( __FILE__ ) . 'class-picklecalendar.php';
}
