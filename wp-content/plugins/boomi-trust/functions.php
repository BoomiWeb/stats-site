<?php

/**
 * boomi_trust_scripts_styles function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_scripts_styles() {
	wp_enqueue_script('boomi-trust-tooltip', BOOMI_TRUST_URL.'js/tooltip.js', array('jquery'), '0.1.0', true);
	
	wp_enqueue_style('boomi-trust-calendar-style', BOOMI_TRUST_URL.'css/calendar.css', '', '0.1.0');
}
add_action('wp_enqueue_scripts', 'boomi_trust_scripts_styles');

/**
 * boomi_trust_add_date_info function.
 * 
 * @access public
 * @param mixed $content
 * @param mixed $date
 * @return void
 */
function boomi_trust_add_date_info($content, $date) {
	$events=boomi_trust_get_events($date);

	foreach ($events as $event) :
		$content.='<div class="trust-cal-icon-wrap"><a href="'.site_url("/statuses/$date").'">'.boomi_trust_event_type_icon($event->type).'</a></div>';
	endforeach;
	
	return $content;
}
add_filter('simcoe_calendar_single_day', 'boomi_trust_add_date_info', 10, 2);

/**
 * boomi_trust_calendar_key function.
 * 
 * @access public
 * @param mixed $content
 * @param mixed $args
 * @return void
 */
function boomi_trust_calendar_key($content, $args) {
	$html='';
	$terms=array(
		array(
			'slug' => 'release_date',
			'name' => 'Release Date',
		),
		array(
			'slug' => 'control_date',
			'name' => 'Release Control Date',
		),
	);
	
	$html.='<div class="col-xs-12 trust-calendar-key">';
		
		foreach ($terms as $term) :
			$html.='<div class="cal-key-term">';
				$html.=boomi_trust_event_type_icon($term['slug']);
				$html.=$term['name'];
			$html.='</div>';
		endforeach;
		
	$html.='</div>';
	
	return $html;
}
add_filter('simcoe_calendar_after_calendar', 'boomi_trust_calendar_key', 10, 2);

/**
 * boomi_trust_get_events function.
 * 
 * @access public
 * @param string $date (default: '')
 * @return void
 */
function boomi_trust_get_events($date='') {
	$posts=get_posts(array(
		'posts_per_page' => -1,
		'post_type' => 'scevent',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'     => 'control_date',
				'value' => $date,
				'type' => 'DATE'
			),
			array(
				'key'     => 'release_date',
				'value' => $date,
				'type' => 'DATE'
			),
		),				
	));
	
	if (!count($posts))
		return $posts;
		
	foreach ($posts as $post) :
		if (get_field('release_date', $post->ID)==$date) :
			$post->type='release_date';
		elseif (get_field('control_date', $post->ID)==$date) :
			$post->type='control_date';
		endif;
	endforeach;
		
	return $posts;
}

/**
 * boomi_trust_event_type_icon function.
 * 
 * @access public
 * @param string $type (default: '')
 * @return void
 */
function boomi_trust_event_type_icon($type='') {
	$icon='';

	switch($type) :
		case 'release_date':
			$icon='<i class="trust-cal-icon orange" title="Release Date"></i>';
			break;
		case 'control_date':
			$icon='<i class="trust-cal-icon aqua" title="Release Control Date"></i>';
			break;
		case 'maintenance_window':
			$icon='<i class="trust-cal-icon purple" title="Maintenance Date"></i>';
			break;			
	endswitch;
	
	return $icon;
}

/**
 * boomi_trust_load_files function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_load_files() {
	$dirs=array(
		'post-types',
		'metaboxes',
		'taxonomies',
	);

	foreach ($dirs as $dir) :
		foreach(glob(BOOMI_TRUST_PATH.$dir.'/*.php') as $file) :
			include_once($file);
		endforeach;
	endforeach;
}
add_action('init', 'boomi_trust_load_files', 1);

/**
 * boomi_trust_cloud_statuses function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_cloud_statuses() {
	$cloud_status = new Boomi_Trust_Cloud_Status();
	$posts = $cloud_status->statuses();
	
	return $posts;
}

/**
 * boomi_trust_cloud_status function.
 * 
 * @access public
 * @param int $post_id (default: 0)
 * @return void
 */
function boomi_trust_cloud_status($post_id=0) {
	$status_id=get_post_meta($post_id, '_statustype', true);
	$status=get_post($status_id);

	echo $status->post_title;	
}

/**
 * boomi_trust_cloud_service function.
 * 
 * @access public
 * @param int $post_id (default: 0)
 * @return void
 */
function boomi_trust_cloud_service($post_id=0) {
	$service_id=get_post_meta($post_id, '_service', true);
	$service=get_post($service_id);

	echo $service->post_title;		
}

/**
 * boomi_trust_cloud_status_class function.
 * 
 * @access public
 * @param int $post_id (default: 0)
 * @return void
 */
function boomi_trust_cloud_status_class($post_id=0) {
	$status_id=get_post_meta($post_id, '_statustype', true);
	$status=get_post($status_id);

	echo $status->post_name;	
}

/**
 * boomi_trust_cloud_services function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_cloud_services() {
	$post_ids=get_posts(array(
		'posts_per_page' => -1,
		'post_type' => 'services',
		'fields' => 'ids',
		'order_by' => 'menu_order',
		'order' => 'ASC',
	));
	
	return $post_ids;
}

function boomi_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    
    return $mimes;
}
add_filter('upload_mimes', 'boomi_mime_types');