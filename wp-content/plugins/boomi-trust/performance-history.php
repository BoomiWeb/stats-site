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
				foreach ($statuses as $status) :
					$html.='<div class="row">';
						$html.='<div class="col-sm-2 date"><a href="#">'.date('M. d, Y', strtotime(get_post_meta($status->ID, '_status_timestamp', true))).'</a></div>';
						$html.='<div class="col-sm-3 circle"><span class="status-circle '.$this->get_status_circle_class($status->statuses['atomsphere-platform']).'"></span></div>';
						$html.='<div class="col-sm-2 circle"><span class="status-circle '.$this->get_status_circle_class($status->statuses['atom-cloud']).'"></span></div>';
						$html.='<div class="col-sm-2 cirlce"><span class="status-circle '.$this->get_status_circle_class($status->statuses['mdm-cloud']).'"></span></div>';
						$html.='<div class="col-sm-3 daily-metrics dm-number"></div>';
					$html.='</div>';
				endforeach;
			endif;
				
		$html.='</div>';
		
		return $html;
	}
	
    protected function get_statuses() {
        global $wpdb;
        
        $statuses = get_posts(array(
           'posts_per_page' => 15,
           'post_type' => 'cloudstatuses',
           'meta_key' => '_status_timestamp',
           'order_by' => 'meta_value',
           'order' => 'DESC', 
        ));
        
        if (empty($statuses))
            return $statuses;
        
        // append platforms and statuses.
        foreach ($statuses as $status) :       
            $status_arr = array();
            $final_status = 'operating-normally';
            $status_statuses = array_keys( get_post_meta($status->ID, '_timestamp_slugs', true) ); 
            
            // our keys above give us the plat form, we must find if there's a non operating normally status and set it as final for display.
            foreach ($status_statuses as $ss) :
                $status_slug = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $status->ID AND meta_key LIKE '_{$ss}%-status'");
             
                if ($status_slug != 'operating-normally') :
                    $final_status = $status_slug; 
                else :
                    $final_status = 'operating-normally';
                endif;
                   
                $status_arr[$ss] = $final_status;
            endforeach;

            $status->statuses = $status_arr; 
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