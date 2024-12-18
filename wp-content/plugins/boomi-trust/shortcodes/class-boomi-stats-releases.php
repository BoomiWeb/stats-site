<?php
/**
 * Boomi Stats Release shortcode
 *
 * @package Boomi_Trust
 * @since   0.3.0
 */


/**
 * Boomi_Stats_Releases class.
 */
class Boomi_Stats_Releases {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_shortcode( 'boomi-stats-notifications', array( $this, 'shortcode' ) );
    }

    /**
     * shortcode function.
     *
     * @access public
     * @param mixed $atts array.
     * @return html
     */
    public function shortcode( $atts ) {
        $atts = shortcode_atts( array(), $atts, 'boomi-stats-notifications' );

        ob_start();

        include_once dirname( __DIR__ ) . '/templates/notifications.php';
        
        return ob_get_clean();
    }

}

new Boomi_Stats_Releases();
