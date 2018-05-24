<?php

/**
 * cloudstatuses_init function.
 * 
 * @access public
 * @return void
 */
function cloudstatuses_init() {
	register_post_type( 'cloudstatuses', array(
		'labels'            => array(
			'name'                => __( 'Statuses', 'boomi-trust' ),
			'singular_name'       => __( 'Status', 'boomi-trust' ),
			'all_items'           => __( 'All Statuses', 'boomi-trust' ),
			'new_item'            => __( 'New Status', 'boomi-trust' ),
			'add_new'             => __( 'Add New', 'boomi-trust' ),
			'add_new_item'        => __( 'Add New Status', 'boomi-trust' ),
			'edit_item'           => __( 'Edit Status', 'boomi-trust' ),
			'view_item'           => __( 'View Statuses', 'boomi-trust' ),
			'search_items'        => __( 'Search Statuses', 'boomi-trust' ),
			'not_found'           => __( 'No Statuses found', 'boomi-trust' ),
			'not_found_in_trash'  => __( 'No Statuses found in trash', 'boomi-trust' ),
			'parent_item_colon'   => __( 'Parent Status', 'boomi-trust' ),
			'menu_name'           => __( 'Statuses', 'boomi-trust' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'supports'          => array( 'editor' ),
		'has_archive'       => true,
		'rewrite'           => true,
		'query_var'         => true,
		'menu_icon'         => 'dashicons-format-status',
		'show_in_rest'      => true,
		'rest_base'         => 'scevents',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'cloudstatuses_init' );

/**
 * cloudstatuses_updated_messages function.
 * 
 * @access public
 * @param mixed $messages
 * @return void
 */
function cloudstatuses_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['cloudstatuses'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Status updated. <a target="_blank" href="%s">View Status</a>', 'boomi-trust'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'boomi-trust'),
		3 => __('Custom field deleted.', 'boomi-trust'),
		4 => __('Status updated.', 'boomi-trust'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Status restored to revision from %s', 'boomi-trust'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Status published. <a href="%s">View Status</a>', 'boomi-trust'), esc_url( $permalink ) ),
		7 => __('Status saved.', 'boomi-trust'),
		8 => sprintf( __('Status submitted. <a target="_blank" href="%s">Preview Status</a>', 'boomi-trust'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Status scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Status</a>', 'boomi-trust'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Status draft updated. <a target="_blank" href="%s">Preview Status</a>', 'boomi-trust'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'cloudstatuses_updated_messages' );

/**
 * cloudstatuses_update_title function.
 * 
 * @access public
 * @param mixed $post_id
 * @return void
 */
function cloudstatuses_update_title($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return $post_id;
    
    if (get_post_type($post_id)!='cloudstatuses')
    	return $post_id;

	// unhook this function so it doesn't loop infinitely.
	remove_action('save_post', 'cloudstatuses_update_title');
	
	// update the post, which calls save_post again.
	$service=get_the_title(get_post_meta($post_id, '_service', true));

    // prevents overwriting title on import.
    if (!empty($service)) :
    	wp_update_post(array(
    		'ID' => $post_id,
    		'post_title' => $service.' - '.get_post_meta($post_id, '_date_and_time_of_occurance', true),
    		'post_name' => sanitize_title($service.' '.get_post_meta($post_id, '_date_and_time_of_occurance', true)),
    	));
	endif;

	// re-hook this function
	add_action('save_post', 'cloudstatuses_update_title');	

	return $post_id;
}
//add_action('save_post', 'cloudstatuses_update_title');

if (is_admin()) :    
	add_filter('manage_edit-cloudstatuses_columns', 'cloudstatuses_columns');
    add_filter('manage_cloudstatuses_posts_custom_column', 'cloudstatuses_rows', 10, 2);
    add_filter('manage_edit-cloudstatuses_sortable_columns', 'cloudstatuses_sortable_columns');
    add_action('pre_get_posts', 'cloudstatuses_custom_orderby');
endif;

/**
 * cloudstatuses_columns function.
 * 
 * @access public
 * @param mixed $columns
 * @return void
 */
function cloudstatuses_columns($columns) {
	unset($columns['title']);
	unset($columns['date']);
	
	$columns['last_updated']=esc_html__('Date', '');
	//$columns['status']=esc_html__('Status', '');
	//$columns['service']=esc_html__('Service', '');
	//$columns['details']=esc_html__('Details', '');
	
	return $columns;
}

/**
 * cloudstatuses_rows function.
 * 
 * @access public
 * @param mixed $column
 * @param mixed $post_id
 * @return void
 */
function cloudstatuses_rows($column, $post_id) {	
	switch ($column) :
		case 'last_updated':
			echo '<div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>';
			echo '<strong><a href="'.get_edit_post_link($post_id).'">'.get_the_title($post_id).'</a></strong>';
			echo '<div class="row-actions">
					<span class="edit"><a href="'.get_edit_post_link($post_id).'" aria-label="Edit">Edit</a> | </span>
					<span class="inline hide-if-no-js"><a href="#" class="editinline" aria-label="Quick edit inline">Quick Edit</a> | </span>
					<span class="trash"><a href="'.get_delete_post_link($post_id).'" class="submitdelete" aria-label="Move “We Need to Work on This” to the Trash">Trash</a> | </span>
					<span class="view"><a href="'.get_permalink($post_id).'" rel="bookmark" aria-label="View">View</a></span>
				</div>
			';
			break;
		case 'status':
			echo get_the_title(get_post_meta($post_id, '_statustype', true));
			break;
		case 'service':
			echo get_the_title(get_post_meta($post_id, '_service', true));
			break;
		case 'details':
			$post=get_post($post_id);
			echo $post->post_content;
			break;			
		default:
			break;
	endswitch;
}

/**
 * cloudstatuses_sortable_columns function.
 * 
 * @access public
 * @param mixed $columns
 * @return void
 */
function cloudstatuses_sortable_columns($columns) {
	$columns['last_updated']='last_updated';
	
	return $columns;
}

/**
 * cloudstatuses_custom_orderby function.
 * 
 * @access public
 * @param mixed $query
 * @return void
 */
function cloudstatuses_custom_orderby($query) {
	if (!is_admin())
		return;
	
	$orderby=$query->get('orderby');
	
	switch ($orderby) :
		case 'last_updated':
			$query->set('meta_key', 'date_and_time_of_occurance');
			$query->set('orderby', 'meta_value');		
			break;
	endswitch;
}
?>