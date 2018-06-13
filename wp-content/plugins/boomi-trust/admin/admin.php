<?php
class Boomi_Trust_Admin {

    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init();
    }

    private function define_constants() {
        define( 'BOOMI_TRUST_ADMIN_PATH', BOOMI_TRUST_PATH . 'admin/' );
        define( 'BOOMI_TRUST_ADMIN_URL', BOOMI_TRUST_URL . 'admin/' );
    }

    protected function includes() {
        include_once( BOOMI_TRUST_ADMIN_PATH . 'update-statistics.php' );
        include_once( BOOMI_TRUST_ADMIN_PATH . 'update-daily-integrations.php' );
    }

    protected function init() {
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts_styles' ) );
    }

    public function scripts_styles() {
        wp_enqueue_style( 'boomi-trust-admin-style', BOOMI_TRUST_ADMIN_URL . 'css/admin.css', '', '0.1.0' );
    }

}

new Boomi_Trust_Admin();
