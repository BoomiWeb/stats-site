<?php

function services_init() {
	register_post_type( 'services', array(
		'labels'            => array(
			'name'                => __( 'Services', 'boomi-trust' ),
			'singular_name'       => __( 'Service', 'boomi-trust' ),
			'all_items'           => __( 'All Services', 'boomi-trust' ),
			'new_item'            => __( 'New Service', 'boomi-trust' ),
			'add_new'             => __( 'Add New', 'boomi-trust' ),
			'add_new_item'        => __( 'Add New Service', 'boomi-trust' ),
			'edit_item'           => __( 'Edit Service', 'boomi-trust' ),
			'view_item'           => __( 'View Services', 'boomi-trust' ),
			'search_items'        => __( 'Search Services', 'boomi-trust' ),
			'not_found'           => __( 'No Services found', 'boomi-trust' ),
			'not_found_in_trash'  => __( 'No Services found in trash', 'boomi-trust' ),
			'parent_item_colon'   => __( 'Parent Service', 'boomi-trust' ),
			'menu_name'           => __( 'Services', 'boomi-trust' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'supports'          => array( 'title', 'editor' ),
		'has_archive'       => true,
		'rewrite'           => true,
		'query_var'         => true,
		'menu_icon'         => 'dashicons-category',
		'show_in_rest'      => true,
		'rest_base'         => 'scevents',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'services_init' );

function services_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['cloudServices'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Service updated. <a target="_blank" href="%s">View Service</a>', 'boomi-trust'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'boomi-trust'),
		3 => __('Custom field deleted.', 'boomi-trust'),
		4 => __('Service updated.', 'boomi-trust'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Service restored to revision from %s', 'boomi-trust'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Service published. <a href="%s">View Service</a>', 'boomi-trust'), esc_url( $permalink ) ),
		7 => __('Service saved.', 'boomi-trust'),
		8 => sprintf( __('Service submitted. <a target="_blank" href="%s">Preview Service</a>', 'boomi-trust'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Service scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Service</a>', 'boomi-trust'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Service draft updated. <a target="_blank" href="%s">Preview Service</a>', 'boomi-trust'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'services_updated_messages' );