<?php

function boomi_trust_statistics($atts) {
	$atts = shortcode_atts( array(), $atts, 'trust-statistics' ); 
	
	$html = '';
	
	
	
	return $html;   
}
add_shortcode('trust-statistics', 'boomi_trust_statistics');