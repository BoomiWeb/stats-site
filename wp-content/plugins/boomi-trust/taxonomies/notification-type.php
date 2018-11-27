<?php
/**
 * Creates notifications taxonomy
 *
 * @package Boomi_Trust
 * @since   0.2.0
 */

/**
 * Notificationtype_init function.
 *
 * @access public
 * @return void
 */
function notificationtype_init() {
    register_taxonomy(
        'notificationtype', array( 'notifications' ), array(
            'hierarchical'      => true,
            'public'            => true,
            'show_in_nav_menus' => true,
            'show_ui'           => true,
            'show_admin_column' => false,
            'query_var'         => true,
            'rewrite'           => true,
            'capabilities'      => array(
                'manage_terms'  => 'edit_posts',
                'edit_terms'    => 'edit_posts',
                'delete_terms'  => 'edit_posts',
                'assign_terms'  => 'edit_posts',
            ),
            'labels'            => array(
                'name'                       => __( 'Types', 'boomi-trust' ),
                'singular_name'              => _x( 'Type', 'taxonomy general name', 'boomi-trust' ),
                'search_items'               => __( 'Search Types', 'boomi-trust' ),
                'popular_items'              => __( 'Popular Types', 'boomi-trust' ),
                'all_items'                  => __( 'All Types', 'boomi-trust' ),
                'parent_item'                => __( 'Parent Type', 'boomi-trust' ),
                'parent_item_colon'          => __( 'Parent Type:', 'boomi-trust' ),
                'edit_item'                  => __( 'Edit Type', 'boomi-trust' ),
                'update_item'                => __( 'Update Type', 'boomi-trust' ),
                'add_new_item'               => __( 'New Type', 'boomi-trust' ),
                'new_item_name'              => __( 'New Type', 'boomi-trust' ),
                'separate_items_with_commas' => __( 'Separate Types with commas', 'boomi-trust' ),
                'add_or_remove_items'        => __( 'Add or remove Types', 'boomi-trust' ),
                'choose_from_most_used'      => __( 'Choose from the most used Types', 'boomi-trust' ),
                'not_found'                  => __( 'No Types found.', 'boomi-trust' ),
                'menu_name'                  => __( 'Types', 'boomi-trust' ),
            ),
            'rewrite' => array( 'slug' => 'notifications/notificationtype' ),
            'show_in_rest'      => true,
            'rest_base'         => 'Type',
            'rest_controller_class' => 'WP_REST_Terms_Controller',
        )
    );

}
add_action( 'init', 'notificationtype_init' );

