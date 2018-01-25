<?php

function statustypes_init() {
	register_post_type( 'statustypes', array(
		'labels'            => array(
			'name'                => __( 'Status Types', 'boomi-trust' ),
			'singular_name'       => __( 'Status Type', 'boomi-trust' ),
			'all_items'           => __( 'All Status Types', 'boomi-trust' ),
			'new_item'            => __( 'New Status Type', 'boomi-trust' ),
			'add_new'             => __( 'Add New', 'boomi-trust' ),
			'add_new_item'        => __( 'Add New Status Type', 'boomi-trust' ),
			'edit_item'           => __( 'Edit Status Type', 'boomi-trust' ),
			'view_item'           => __( 'View Status Types', 'boomi-trust' ),
			'search_items'        => __( 'Search Status Types', 'boomi-trust' ),
			'not_found'           => __( 'No Status Types found', 'boomi-trust' ),
			'not_found_in_trash'  => __( 'No Status Types found in trash', 'boomi-trust' ),
			'parent_item_colon'   => __( 'Parent Status Type', 'boomi-trust' ),
			'menu_name'           => __( 'Status Types', 'boomi-trust' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'supports'          => array( 'title', 'editor' ),
		'has_archive'       => true,
		'rewrite'           => true,
		'query_var'         => true,
		'menu_icon'         => 'dashicons-format-aside',
		'show_in_rest'      => true,
		'rest_base'         => 'scevents',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'statustypes_init' );

function statustypes_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['cloudStatus Types'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Status Type updated. <a target="_blank" href="%s">View Status Type</a>', 'boomi-trust'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'boomi-trust'),
		3 => __('Custom field deleted.', 'boomi-trust'),
		4 => __('Status Type updated.', 'boomi-trust'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Status Type restored to revision from %s', 'boomi-trust'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Status Type published. <a href="%s">View Status Type</a>', 'boomi-trust'), esc_url( $permalink ) ),
		7 => __('Status Type saved.', 'boomi-trust'),
		8 => sprintf( __('Status Type submitted. <a target="_blank" href="%s">Preview Status Type</a>', 'boomi-trust'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Status Type scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Status Type</a>', 'boomi-trust'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Status Type draft updated. <a target="_blank" href="%s">Preview Status Type</a>', 'boomi-trust'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'statustypes_updated_messages' );