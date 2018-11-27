<?php

/**
 * boomi_trust_load_files function.
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

function boomi_mime_types( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';

    return $mimes;
}
add_filter( 'upload_mimes', 'boomi_mime_types' );
