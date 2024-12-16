<?php
/**
 * General functions
 *
 * @package Boomi_Trust
 * @since   0.1.0
 */

/**
 * Load files function.
 *
 * @access public
 * @return void
 */
function boomi_trust_load_files() {
    $dirs = array(
        'metaboxes',
        'taxonomies',
        'shortcodes',
    );

    foreach ( $dirs as $dir ) :
        foreach ( glob( BOOMI_TRUST_PATH . $dir . '/*.php' ) as $file ) :
            include_once( $file );
        endforeach;
    endforeach;
}
add_action( 'init', 'boomi_trust_load_files', 1 );

/**
 * Mime types function.
 *
 * @access public
 * @param mixed $mimes array
 * @return array
 */
function boomi_mime_types( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';

    return $mimes;
}
add_filter( 'upload_mimes', 'boomi_mime_types' );


function boomi_trust_has_schedule_section() {
    if ( have_rows( 'infrastructure_releases' ) ) {
        while ( have_rows( 'infrastructure_releases' ) ) { 
            the_row();

            if ( have_rows( 'release_month' ) || have_rows( 'change_type' ) ) {
                return true;
            }
        }
    }

    return false;
}