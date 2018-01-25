<?php
/*
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

define('BOOMI_TRUST_PATH', plugin_dir_path(__FILE__));
define('BOOMI_TRUST_URL', plugin_dir_url(__FILE__));

include_once(BOOMI_TRUST_PATH.'admin/admin.php');
include_once(BOOMI_TRUST_PATH.'performance-history.php');
include_once(BOOMI_TRUST_PATH.'calendar/calendar.php');
include_once(BOOMI_TRUST_PATH.'functions.php');
include_once(BOOMI_TRUST_PATH.'cloud-status.php');
include_once(BOOMI_TRUST_PATH.'init.php');
include_once(BOOMI_TRUST_PATH.'logger.php');
include_once(BOOMI_TRUST_PATH.'cron.php');

function boomi_trust_activate_plugin() {
	$tomorrow=strtotime('tomorrow');
	
	if (!wp_next_scheduled('boomi_trust_status_cron_run')) :
		wp_schedule_event($tomorrow, 'daily', 'boomi_trust_status_cron_run');
	endif;
}
register_activation_hook(__FILE__, 'boomi_trust_activate_plugin');

function boomi_trust_deactivate_plugin() {
	wp_clear_scheduled_hook('boomi_trust_status_cron_run');
}
register_deactivation_hook(__FILE__, 'boomi_trust_deactivate_plugin');
?>
