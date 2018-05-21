<?php
/**
 * Main Boomi Trust class
 *
 * @package Boomi_Trust
 * @since   1.0.0
 */

/**
 * Final Boomi_Trust class.
 *
 * @final
 */
final class Boomi_Trust {

    /**
     * Version
     *
     * @var string
     * @access public
     */
    public $version = '0.2.0';

    /**
     * Construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define constants function.
     *
     * @access private
     * @return void
     */
    private function define_constants() {
        $this->define( 'BOOMI_TRUST_PATH', plugin_dir_path( __FILE__ ) );
        $this->define( 'BOOMI_TRUST_URL', plugin_dir_url( __FILE__ ) );
        $this->define( 'BOOMI_TRUST_VERSION', $this->version );
    }

    /**
     * Define function.
     *
     * @access private
     * @param mixed $name (name).
     * @param mixed $value (value).
     * @return void
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Includes function.
     *
     * @access public
     * @return void
     */
    public function includes() {
        include_once(BOOMI_TRUST_PATH.'admin/admin.php');
        include_once(BOOMI_TRUST_PATH.'performance-history.php');
        include_once(BOOMI_TRUST_PATH.'functions.php');
        include_once(BOOMI_TRUST_PATH.'cloud-status.php');
        include_once(BOOMI_TRUST_PATH.'init.php');
        include_once(BOOMI_TRUST_PATH.'logger.php');
        include_once(BOOMI_TRUST_PATH.'cron.php');
    }

    /**
     * Init hooks function.
     *
     * @access private
     * @return void
     */
    private function init_hooks() {
        register_activation_hook(BOOMI_TRUST_PLUGIN_FILE, array($this, 'boomi_trust_activate_plugin'));
        register_deactivation_hook(BOOMI_TRUST_PLUGIN_FILE, array($this, 'boomi_trust_deactivate_plugin'));  
    }

    public function boomi_trust_activate_plugin() {
    	$tomorrow=strtotime('tomorrow');
    	
    	if (!wp_next_scheduled('boomi_trust_status_cron_run')) :
    		wp_schedule_event($tomorrow, 'daily', 'boomi_trust_status_cron_run');
    	endif;
    }
    
    public function boomi_trust_deactivate_plugin() {
    	wp_clear_scheduled_hook('boomi_trust_status_cron_run');
    }

}

/**
 * Main function.
 *
 * @access public
 * @return class
 */
function boomitrust() {
    return new Boomi_Trust();
}

// Global for backwards compatibility.
$GLOBALS['boomitrust'] = boomitrust();