<?php
/*
Plugin Name: Boomi Security Headers
Plugin URI: 
Description: Adds additional security headers to the site.
Author: Erik Mitchell
Version: 0.1.0
Author URI: 
License: GPLv2
*/

// only run if not admin.
if ( ! is_admin() ) {
    add_action( 'send_headers', 'boomi_add_header_hsts' );
}

/**
 * Add the HSTS header while rendering a response.
 *
 * @access public
 * @return void
 */
if (!function_exists('boomi_add_header_hsts')) {
    function boomi_add_header_hsts() {
        // X-Frame-Options.
        header( 'X-Frame-Options: DENY' );

        // X-XSS-Protection.
        header( 'X-XSS-Protection: 1; mode=block' );

        // X-Content-Type-Options.
        header( 'X-Content-Type-Options: nosniff' );

        // HSTS for missing subdomain and too short hsts age.
        header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload' );
    }
}