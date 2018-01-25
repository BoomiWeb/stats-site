<?php

/**
 * notifications_init function.
 * 
 * @access public
 * @return void
 */
function notifications_init() {
	register_post_type( 'notifications', array(
		'labels'            => array(
			'name'                => __( 'Notifications', 'boomi-trust' ),
			'singular_name'       => __( 'Notification', 'boomi-trust' ),
			'all_items'           => __( 'All Notifications', 'boomi-trust' ),
			'new_item'            => __( 'New Notification', 'boomi-trust' ),
			'add_new'             => __( 'Add New', 'boomi-trust' ),
			'add_new_item'        => __( 'Add New Notification', 'boomi-trust' ),
			'edit_item'           => __( 'Edit Notification', 'boomi-trust' ),
			'view_item'           => __( 'View Notifications', 'boomi-trust' ),
			'search_items'        => __( 'Search Notifications', 'boomi-trust' ),
			'not_found'           => __( 'No Notifications found', 'boomi-trust' ),
			'not_found_in_trash'  => __( 'No Notifications found in trash', 'boomi-trust' ),
			'parent_item_colon'   => __( 'Parent Notification', 'boomi-trust' ),
			'menu_name'           => __( 'Notifications', 'boomi-trust' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'supports'          => array( 'editor' ),
		'has_archive'       => true,
		'rewrite'           => true,
		'query_var'         => true,
		'menu_icon'         => 'dashicons-testimonial',
		'show_in_rest'      => true,
		'rest_base'         => 'scevents',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'notifications_init' );

/**
 * notifications_updated_messages function.
 * 
 * @access public
 * @param mixed $messages
 * @return void
 */
function notifications_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['cloudNotifications'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Notification updated. <a target="_blank" href="%s">View Notification</a>', 'boomi-trust'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'boomi-trust'),
		3 => __('Custom field deleted.', 'boomi-trust'),
		4 => __('Notification updated.', 'boomi-trust'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Notification restored to revision from %s', 'boomi-trust'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Notification published. <a href="%s">View Notification</a>', 'boomi-trust'), esc_url( $permalink ) ),
		7 => __('Notification saved.', 'boomi-trust'),
		8 => sprintf( __('Notification submitted. <a target="_blank" href="%s">Preview Notification</a>', 'boomi-trust'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Notification scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Notification</a>', 'boomi-trust'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Notification draft updated. <a target="_blank" href="%s">Preview Notification</a>', 'boomi-trust'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'notifications_updated_messages' );

/**
 * notifications_update_title function.
 * 
 * @access public
 * @param mixed $post_id
 * @return void
 */
function notifications_update_title($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return $post_id;
    
    if (get_post_type($post_id)!='notifications')
    	return $post_id;
    	
	$terms=wp_get_post_terms($post_id, 'notificationtype', array('fields' => 'names'));

	// unhook this function so it doesn't loop infinitely
	remove_action('save_post', 'notifications_update_title');
	
		// update the post, which calls save_post again
	wp_update_post(array(
		'ID' => $post_id,
		'post_title' => $terms[0].' - '.get_field('publish_date', $post_id),
		'post_name' => sanitize_title($terms[0].' '.get_field('publish_date', $post_id)),
	));

	// re-hook this function
	add_action('save_post', 'notifications_update_title');	

	return $post_id;
}
add_action('save_post', 'notifications_update_title');
?>