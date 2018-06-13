<?php

/**
 * Plugin Name: Boomi Trust CMS
 * Plugin URI:
 * Description: Add ons for Boomi Trust.
 * Version: 0.2.0
 * Author: Boomi
 * Author URI: https://boomi.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: boomi-trust
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define BOOMI_TRUST_PLUGIN_FILE.
if ( ! defined( 'BOOMI_TRUST_PLUGIN_FILE' ) ) {
    define( 'BOOMI_TRUST_PLUGIN_FILE', __FILE__ );
}

// Include the main Boomi_Trust class.
if ( ! class_exists( 'Boomi_Trust' ) ) {
    include_once dirname( __FILE__ ) . '/class-boomi-trust.php';
}
