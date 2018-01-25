<?php

/**
 * notificationtype_init function.
 * 
 * @access public
 * @return void
 */
function notificationtype_init() {
	register_taxonomy( 'notificationtype', array( 'notifications' ), array(
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
			'assign_terms'  => 'edit_posts'
		),
		'labels'            => array(
			'name'                       => __( 'Types', 'boomi' ),
			'singular_name'              => _x( 'Type', 'taxonomy general name', 'boomi' ),
			'search_items'               => __( 'Search Types', 'boomi' ),
			'popular_items'              => __( 'Popular Types', 'boomi' ),
			'all_items'                  => __( 'All Types', 'boomi' ),
			'parent_item'                => __( 'Parent Type', 'boomi' ),
			'parent_item_colon'          => __( 'Parent Type:', 'boomi' ),
			'edit_item'                  => __( 'Edit Type', 'boomi' ),
			'update_item'                => __( 'Update Type', 'boomi' ),
			'add_new_item'               => __( 'New Type', 'boomi' ),
			'new_item_name'              => __( 'New Type', 'boomi' ),
			'separate_items_with_commas' => __( 'Separate Types with commas', 'boomi' ),
			'add_or_remove_items'        => __( 'Add or remove Types', 'boomi' ),
			'choose_from_most_used'      => __( 'Choose from the most used Types', 'boomi' ),
			'not_found'                  => __( 'No Types found.', 'boomi' ),
			'menu_name'                  => __( 'Types', 'boomi' ),
		),
		'rewrite' => array( 'slug' => 'notifications/notificationtype' ),
		'show_in_rest'      => true,
		'rest_base'         => 'Type',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	) );

}
add_action( 'init', 'notificationtype_init' );
?>