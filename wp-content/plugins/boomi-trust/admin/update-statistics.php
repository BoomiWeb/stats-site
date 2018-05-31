<?php
class Boomi_Trust_Update_Statuses {
	
	public function __construct() {
		
	}
	
	public function duplicate_statuses($args='') {
		$default_args=array();
		$args=wp_parse_args($args, $default_args);
		
		$cloud_services=boomi_trust_cloud_services();		
		
		foreach ($cloud_services as $meta_id) :
			$posts=get_posts(array(
				'posts_per_page' => 1,
				'post_type' => 'cloudstatuses',
				'meta_query' => array(
					array(
						'key' => '_service',
						'value' => $meta_id,
					),
				),
				'fields' => 'ids',	
				'meta_key' => 'date_and_time_of_occurance',
				'orderby' => 'meta_value',
				'order' => 'DESC'								
			));

			if (isset($posts[0])) :
				$this->duplicate_post(array(
					'post_id' => $posts[0],
				));	
			endif;		
		endforeach;
	}

	protected function duplicate_post($args='') {
		global $wpdb;
		
		$default_args=array(
			'post_id' => 0, 
			'status' => 'draft',
		);
		$args=wp_parse_args($args, $default_args);
	
		if (!$args['post_id'])
			return;
	 
		// get id and post //
		$post=get_post($args['post_id']);
	 
		// duplicate post //
		if (isset($post) && $post!=null) :

			// build new post //
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $post->post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => $args['status'],
				'post_title'     => $post->post_title,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);
			
			// insert the post //
			$new_post_id=wp_insert_post($args);			

			$org_service=get_post_meta($post->ID, '_service', true);
			$org_statustype=get_post_meta($post->ID, '_statustype', true); 
		
			update_post_meta($new_post_id, '_service', $org_service);
			update_post_meta($new_post_id, '_statustype', $org_statustype);	
			update_field('date_and_time_of_occurance', date('Y-m-d H:i:s'), $new_post_id);

			wp_update_post(array(
				'ID' => $new_post_id,
				'post_title' => $service.' - '.get_field('date_and_time_of_occurance', $post_id),
				'post_name' => sanitize_title($service.' '.get_field('date_and_time_of_occurance', $post_id)),
			));

			boomi_trust_logger()->log("Post ($new_post_id) created.");
		else :
			boomi_trust_logger()->log("Post creation failed, could not find original post: $post_id");
		endif;
		
		return;
	}
	
}
?>