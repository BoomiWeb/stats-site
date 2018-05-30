<?php

class Boomi_Trust_Performance {
	
	public function __construct() {		
        add_action('init', array($this, 'custom_rewrite_rule'), 10, 0);
		add_action('wp_enqueue_scripts', array($this, 'scripts_styles'));
		
		add_filter( 'query_vars', array($this, 'register_query_vars' ) );
		
		add_shortcode('trust-performance', array($this, 'shortcode'));
	}
	
	public function scripts_styles() {
		wp_enqueue_style('boomi-trust-perf-history-css', BOOMI_TRUST_URL.'css/performance-history.css', '', '0.1.0');
	}
	
	public function shortcode($atts) {
    	if (get_query_var('performance-history-date')) :
    	    $html = $this->single_view();
        else :
            $html = $this->list_view();
        endif;
		
		return $html;
	}
	
	public function list_view() {
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
				    $datestr = strtotime(get_post_meta($status->ID, '_status_timestamp', true));
				    
					$html.='<div class="row">';
						$html.='<div class="col-sm-2 date"><a href="'.date('Y-m-d', $datestr).'">'.date('M. d, Y', $datestr).'</a></div>';
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
	
	public function single_view() {
		$html='';
		$date = get_query_var('performance-history-date');
		$status = $this->get_single_day_statuses($date);

		$html.='<div class="container performance-single">';
		
		    $html.='<div class="row header">';
                $html.='<div class="col-sm-12 date">'.date('F j, Y', strtotime($date)).'</div>';
            $html.='</div>';

            foreach ($status->statuses as $platform_slug => $timestamps) :
                $page = get_page_by_path($platform_slug, 'OBJECT', 'services');
                
    			$html.='<div class="row">';
	    			$html.='<div class="col-sm-12 platform">'.$page->post_title.'</div>';
                $html.='</div>';
                
                foreach ($timestamps as $timestamp) :
        			$html.='<div class="row">';
    	    			$html.='<div class="col-sm-12 date">'.date('m/d/Y h:i:s A', strtotime($timestamp)).'</div>';
                    $html.='</div>';  
                    
                    $html.='<div class="row">';
    	    			$html.='<div class="col-sm-12">'.get_post_meta($status->ID, "_{$platform_slug}_{$timestamp}-status", true).'</div>';
                    $html.='</div>';
                    
                    $html.='<div class="row">';
    	    			$html.='<div class="col-sm-12">'.get_post_meta($status->ID, "_{$platform_slug}_{$timestamp}-details", true).'</div>';
                    $html.='</div>';
                endforeach;
            endforeach;
				
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

    protected function get_single_day_statuses($date = '') {
        $status = $this->get_post_by_meta( array( 'meta_key' => '_status_timestamp', 'meta_value' => str_replace('-', '', $date) ) );
        
        if (empty($status))
            return '';
        
        // append platforms and statuses.       
        $status->statuses = get_post_meta($status->ID, '_timestamp_slugs', true );
        
        // sort status alphabetically.
        ksort($status->statuses);

        return $status;
	}
	
	protected function get_post_by_meta( $args = array() ) {   
        // Parse incoming $args into an array and merge it with $defaults - caste to object ##
        $args = ( object )wp_parse_args( $args );
       
        // grab page - polylang will take take or language selection ##
        $args = array(
            'meta_query'        => array(
                array(
                    'key'       => $args->meta_key,
                    'value'     => $args->meta_value,
                ),
            ),
            'post_type'         => 'cloudstatuses',
            'posts_per_page'    => 1,
        );
       
        // run query ##
        $posts = get_posts( $args );
      
        // check results ##
        if ( ! $posts || is_wp_error( $posts ) ) return false;
       
        // test it ##
        #pr( $posts[0] );
       
        // kick back results ##
        return $posts[0];
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
	
	public function register_query_vars( $vars ) {
	    $vars[] = 'performance-history-date';
	
        return $vars;
    }

    public function custom_rewrite_rule() {
        add_rewrite_rule('^performance-history/([^/]*)/?','index.php?page_id=11&performance-history-date=$matches[1]','top');
    }
	
}	

new Boomi_Trust_Performance();