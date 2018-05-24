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
		$data=$this->get_statuses();
echo '<pre>';		
print_r($data);	
echo '</pre>';
		$html.='<div class="container performance">';
			//$html.='<div class="performance">';
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
				
				if (!empty($data)) :				
    				foreach ($data->entries as $arr) :
    					$html.='<div class="row">';
    						$html.='<div class="col-sm-2 date">'.date('M. d, Y', strtotime($arr->date)).'</div>';
    						$html.='<div class="col-sm-3 circle"><span class="status-circle '.$this->get_status_circle_class($arr->SystemStatus->AtomSpherePlatform).'"></span></div>';
    						$html.='<div class="col-sm-2 circle"><span class="status-circle '.$this->get_status_circle_class($arr->SystemStatus->AtomCloud).'"></span></div>';
    						$html.='<div class="col-sm-2 cirlce"><span class="status-circle '.$this->get_status_circle_class($arr->SystemStatus->MDMCloud).'"></span></div>';
    						$html.='<div class="col-sm-3 daily-metrics dm-number">'.$arr->DailyMetrics->IntegrationProcesses.'</div>';
    					$html.='</div>';
    				endforeach;
				endif;
				
			//$html.='</div>';
		$html.='</div>';
		
		return $html;
	}
	
    protected function get_statuses() {
        $statuses = get_posts(array(
            'posts_per_page' => 5,
            'post_type' => 'cloudstatuses', 
            'meta_key' => '_date_and_time_of_occurance',
            'orderby' => 'meta_value',           
        ));
        
        return $statuses;
	}
	
	protected function get_status_circle_class($status='') {
		$class='';
		
		if ($status=='informational_message') :
			$class='glyphicon glyphicon-info-sign info';
		else :
			$class=$status;
		endif;
		
		return $class;
	}
	
}	

new Boomi_Trust_Performance();