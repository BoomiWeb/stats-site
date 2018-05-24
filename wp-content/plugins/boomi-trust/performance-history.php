<?php

class Boomi_Trust_Performance {
	
	public function __construct() {		
		add_shortcode('trust-performance', array($this, 'shortcode'));
		add_action('wp_enqueue_scripts', array($this, 'scripts_styles'));
	}
	
	public function scripts_styles() {
		wp_enqueue_style('boomi-trust-perf-history-css', BOOMI_TRUST_URL.'css/performance-history.css', '', '0.1.0');
	}
	
	public function shortcode($atts) {
		$html='';
		$statuses=$this->get_statuses();

		$html.='<div class="container performance">';

			$html.='<div class="row header">';
				$html.='<div class="col-sm-9 status">System Status</div>';
				$html.='<div class="col-sm-3 daily-metrics">Daily Metrics</div>';
			$html.='</div>';

			$html.='<div class="row sub-header">';
				$html.='<div class="col-sm-offset-2 col-sm-3 atomsphere">AtomSphere Platform</div>';
				$html.='<div class="col-sm-2 atom-cloud">Atom Cloud</div>';
				$html.='<div class="col-sm-2 mdm-cloud">MDM Cloud</div>';
				$html.='<div class="col-sm-3 daily-metrics integration">Integration Processes</div>';
			$html.='</div>';
			
			if (!empty($statuses)) :				
				foreach ($statuses as $timestamp => $service) :
					$html.='<div class="row">';
						$html.='<div class="col-sm-2 date">'.date('M. d, Y', strtotime($timestamp)).'</div>';
						$html.='<div class="col-sm-3 circle"><span class="status-circle '.$this->get_status_circle_class($service['atomsphere-platform']).'"></span></div>';
						$html.='<div class="col-sm-2 circle"><span class="status-circle '.$this->get_status_circle_class($service['atom-cloud']).'"></span></div>';
						$html.='<div class="col-sm-2 cirlce"><span class="status-circle '.$this->get_status_circle_class($service['mdm-cloud']).'"></span></div>';
						$html.='<div class="col-sm-3 daily-metrics dm-number"></div>';
					$html.='</div>';
				endforeach;
			endif;
				
		$html.='</div>';
		
		return $html;
	}
	
    protected function get_statuses() {
        global $wpdb;
        
        $statuses = array();
        $service_order = array('atomsphere-platform', 'atom-cloud', 'mdm-cloud');        
        $db_statuses = $wpdb->get_results("
            SELECT $wpdb->posts.ID, pm.meta_value AS timestamp, pm1.meta_value AS service, pm2.meta_value AS status
            FROM $wpdb->posts 
            INNER JOIN $wpdb->postmeta AS pm ON ($wpdb->posts.ID = pm.post_id)
            INNER JOIN $wpdb->postmeta AS pm1 ON ($wpdb->posts.ID = pm1.post_id)
            INNER JOIN $wpdb->postmeta AS pm2 ON ($wpdb->posts.ID = pm2.post_id)
            WHERE $wpdb->posts.post_type = 'cloudstatuses'
            AND pm.meta_key = '_date_and_time_of_occurance'
            AND pm1.meta_key = '_service'
            AND pm2.meta_key = '_statustype'
            ORDER BY pm.meta_value DESC   
        ");
        
        // sertup statuses.
        foreach ($db_statuses as $status) :
            $service = get_post($status->service);
            $status_post = get_post($status->status);
            
            $statuses[sanitize_title($status->timestamp)][$service->post_name] = $status_post->post_name;
        endforeach;
        
        // update statuses order.
        foreach ($statuses as $timestamp => $services) :
            $statuses[$timestamp] = array_merge(array_flip($service_order), $services);
        endforeach;

        return $statuses;
	}
	
	protected function get_status_circle_class($status='') {
		$class='';
		
		switch ($status) :
		    case 'informational-message':
		        $class='glyphicon glyphicon-info-sign info';
		        break;
            case 1:
                $class = 'operating-normally';
                break;
		    default:
		        $class = $status;
		endswitch;
		
		return $class;
	}
	
}	

new Boomi_Trust_Performance();