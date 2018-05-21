<?php

class Boomi_Trust_Cloud_Status {
	
	public function __construct() {
		
	}
	
	public function statuses() {
		$posts=array();
		$services=get_posts(array(
			'posts_per_page' => -1,
			'post_type' => 'services',
			'order_by' => 'menu_order',
			'order' => 'ASC',
		));
		
		foreach ($services as $service) :
			$post=get_posts(array(
				'post_per_page' => 1,
				'post_type' => 'cloudstatuses',
				'meta_query' => array(
					array(
						'key' => '_service',
						'value' => $service->ID,
					),
				),
			));
			$posts[]=$post[0];
		endforeach;
		
		return $posts;
	}
	
	public function get_status_list($args='') {
		$default_args=array(
			'post_type' => 'cloudstatuses',
			'service' => 89, //atomsphere
			'date' => date('Y-m-d'),
		);
		$args=wp_parse_args($args, $default_args);

		$posts=get_posts(array(
			'post_per_page' => -1,
			'post_type' => 'cloudstatuses',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => '_service',
					'value' => $args['service'],
				),
				array(
					'key' => 'date_and_time_of_occurance',
					'value' => $args['date'],
					'type' => 'DATE',
				),
			),
			
		));
		
		foreach ($posts as $post) :
			$post->service=$this->get_status_service($post->ID);
			$post->status_type=$this->get_status_type($post->ID);
			$post->date_time=get_field('date_and_time_of_occurance', $post->ID);
		endforeach;

		return $posts;
	}
	
	protected function get_status_service($status_id=0) {
		$service_id=get_post_meta($status_id, '_service', true);
		$service=get_post($service_id);
	
		return $service->post_title;		
	}

	protected function get_status_type($status_id=0) {
		$status_type_id=get_post_meta($status_id, '_statustype', true);
		$status_type=get_post($status_type_id);
	
		return $status_type->post_title;		
	}
	
}