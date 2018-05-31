<?php

function boomi_trust_daily_integrations($atts) {
	$atts = shortcode_atts( array(), $atts, 'trust-daily-integrations' ); 
	
	$html = '';
	$statistic_types = array(
    	'processes' => array(
    	    'title' => 'Integrations Processed',
    	    'description' => 'Total integrations that have been processed in the last 30 days.',
        ),
    	'atoms' => array(
    	    'title' => 'Atoms Deployed',
    	    'description' => 'Total Atoms deployed in the Cloud and on-premise.',
        ),
    	'total-mappings' => array(
    	    'title' => 'Mappings Indexed',
    	    'description' => 'Total number of mappings indexed by Boomi Suggest.',
        ),
    	'total-functions' => array(
    	    'title' => 'Functions Indexed',
    	    'description' => 'Total number of functions indexed by Boomi Suggest.',
        ),
    );
		
	$html .= '<div class="row statistics">';
		
		if ( !empty(get_option('_trust_statistic_updated')) ) :
		
			foreach ($statistic_types as $slug => $arr) :
			
				$html .= '<div class="col-xs-6 col-sm-3">';
					$html .= '<div class="stat-wrap">';
						$html .= '<div class="row stat-number">';
							$html .= '<div class="col-xs-12">'.number_format(get_option('_trust_statistic_' . $slug, 0)).'</div>';
						$html .= '</div>';

						$html .= '<div class="row stat-title">';
							$html .= '<div class="col-xs-12">'.$arr['title'].'</div>';
						$html .= '</div>';
						
						$html .= '<div class="row stat-description">';
							$html .= '<div class="col-xs-12">'.$arr['description'].'</div>';
						$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';
				
			endforeach;
			
		endif;
		
	$html .= '</div>';
		

	
	return $html;   
}
add_shortcode('trust-daily-integrations', 'boomi_trust_daily_integrations');