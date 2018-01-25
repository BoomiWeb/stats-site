<?php

/**
 * emsm_shortcode function.
 * 
 * @access public
 * @param mixed $atts
 * @return void
 */
function emsm_shortcode($atts) {
	$atts=shortcode_atts(array(
	), $atts, 'emsm');

	return emsm_display_social_media();	
}
add_shortcode('emsm', 'emsm_shortcode');	
?>