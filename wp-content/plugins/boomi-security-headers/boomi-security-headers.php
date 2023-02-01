<?php

/**
 * Plugin Name:       Boomi Security Headers
 * Plugin URI:        https://boomi.com
 * Description:       Security headers for Boomi WordPress websites.
 * Version:           1.0.0
 * Author:            Erik Mitchell
 * Author URI:        https://boomi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       boomi-security-headers
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'BOOMI_SECURITY_HEADERS_VERSION', '1.0.0' );

/**
*
* Add the HSTS header while rendering a response.
*
*/
function boomi_add_header_hsts() {
    //Content-Security-Policy
    //Strict-Transport-Security    
    header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains;' );
    
    //X-Frame-Options
    header( 'X-Frame-Options: DENY' );
    
    //X-XSS-Protection
    header( 'X-XSS-Protection: 1; mode=block' );
    
    //X-Content-Type-Options
    header( 'X-Content-Type-Options: nosniff' );
}
add_action( 'send_headers', 'boomi_add_header_hsts' );

/**
 * Add Multiple HTTP Headers to admin.
 * 
 * Note - this is the same as above.
 *
 * @return void
 */
function shapeSpace_add_headers() {
	
	if (is_admin()) {
        //Content-Security-Policy
        //Strict-Transport-Security    
        header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains;' );
        
        //X-Frame-Options
        header( 'X-Frame-Options: DENY' );
        
        //X-XSS-Protection
        header( 'X-XSS-Protection: 1; mode=block' );
        
        //X-Content-Type-Options
        header( 'X-Content-Type-Options: nosniff' );
	}
	
}
add_action('init', 'shapeSpace_add_headers');