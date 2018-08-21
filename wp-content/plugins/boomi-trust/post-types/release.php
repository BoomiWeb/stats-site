<?php

/**
 * release_init function.
 *
 * @access public
 * @return void
 */
function release_init() {
    register_post_type(
        'release', array(
            'labels'            => array(
                'name'                => __( 'Releases', 'boomi-trust' ),
                'singular_name'       => __( 'Release', 'boomi-trust' ),
                'all_items'           => __( 'All Releases', 'boomi-trust' ),
                'new_item'            => __( 'New Release', 'boomi-trust' ),
                'add_new'             => __( 'Add New', 'boomi-trust' ),
                'add_new_item'        => __( 'Add New Release', 'boomi-trust' ),
                'edit_item'           => __( 'Edit Release', 'boomi-trust' ),
                'view_item'           => __( 'View Releases', 'boomi-trust' ),
                'search_items'        => __( 'Search Releases', 'boomi-trust' ),
                'not_found'           => __( 'No Releases found', 'boomi-trust' ),
                'not_found_in_trash'  => __( 'No Releases found in trash', 'boomi-trust' ),
                'parent_item_colon'   => __( 'Parent Release', 'boomi-trust' ),
                'menu_name'           => __( 'Releases', 'boomi-trust' ),
            ),
            'public'            => true,
            'hierarchical'      => false,
            'show_ui'           => true,
            'show_in_nav_menus' => true,
            'supports'          => array( 'title' ),
            'has_archive'       => true,
            'rewrite'           => true,
            'query_var'         => true,
            'menu_icon'         => 'dashicons-calendar-alt',
            'show_in_rest'      => true,
            'rest_base'         => 'release',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        )
    );

}
add_action( 'init', 'release_init' );

/**
 * release_updated_messages function.
 *
 * @access public
 * @param mixed $messages
 * @return void
 */
function release_updated_messages( $messages ) {
    global $post;

    $permalink = get_permalink( $post );

    $messages['releases'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => sprintf( __( 'Release updated. <a target="_blank" href="%s">View Release</a>', 'boomi-trust' ), esc_url( $permalink ) ),
        2 => __( 'Custom field updated.', 'boomi-trust' ),
        3 => __( 'Custom field deleted.', 'boomi-trust' ),
        4 => __( 'Release updated.', 'boomi-trust' ),
        /* translators: %s: date and time of the revision */
        5 => isset( $_GET['revision'] ) ? sprintf( __( 'Release restored to revision from %s', 'boomi-trust' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
        6 => sprintf( __( 'Release published. <a href="%s">View Release</a>', 'boomi-trust' ), esc_url( $permalink ) ),
        7 => __( 'Release saved.', 'boomi-trust' ),
        8 => sprintf( __( 'Release submitted. <a target="_blank" href="%s">Preview Release</a>', 'boomi-trust' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
        9 => sprintf(
            __( 'Release scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Release</a>', 'boomi-trust' ),
            // translators: Publish box date format, see http://php.net/date
            date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink )
        ),
        10 => sprintf( __( 'Release draft updated. <a target="_blank" href="%s">Preview Release</a>', 'boomi-trust' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
    );

    return $messages;
}
add_filter( 'post_updated_messages', 'release_updated_messages' );

