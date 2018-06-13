<?php

/**
 * boomi_trust_load_files function.
 *
 * @access public
 * @return void
 */
function boomi_trust_load_files() {
    $dirs = array(
        'post-types',
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

function boomi_pc_get_rcd_dates() {
    global $wpdb;

    $today = date( 'Y-m-d' );
    $date = date( 'Y-m-d', strtotime( "$today -1 year" ) );
    $dates = $wpdb->get_col(
        "
        SELECT $wpdb->postmeta.meta_value
        FROM $wpdb->posts
        INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
        INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
        INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
        INNER JOIN $wpdb->terms ON ($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)        
        WHERE $wpdb->posts.post_type = 'pcevent'
            AND $wpdb->posts.post_status = 'publish'
            AND ( $wpdb->postmeta.meta_key LIKE '_start_date_%' AND CAST($wpdb->postmeta.meta_value AS DATE) >= '{$date}')
            AND $wpdb->terms.slug = 'release-control-date'
        ORDER BY $wpdb->postmeta.meta_value ASC
    "
    );

    return $dates;
}

function boomi_pc_get_rd() {
    global $wpdb;

    $today = date( 'Y-m-d' );
    $date = date( 'Y-m-d', strtotime( "$today -1 year" ) );
    $dates = $wpdb->get_col(
        "
        SELECT $wpdb->postmeta.meta_value
        FROM $wpdb->posts
        INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
        INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
        INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
        INNER JOIN $wpdb->terms ON ($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)        
        WHERE $wpdb->posts.post_type = 'pcevent'
            AND $wpdb->posts.post_status = 'publish'
            AND ( $wpdb->postmeta.meta_key LIKE '_start_date_%' AND CAST($wpdb->postmeta.meta_value AS DATE) >= '{$date}')
            AND $wpdb->terms.slug = 'release-date'
        ORDER BY $wpdb->postmeta.meta_value ASC
    "
    );

    return $dates;
}
