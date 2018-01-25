<?php

/**
 * emsm_scripts_styles function.
 * 
 * @access public
 * @return void
 */
function emsm_scripts_styles() {
	wp_enqueue_style('font-awesome-style', EMSM_URL.'font-awesome/font-awesome.min.css', '', '4.7.0');
	wp_enqueue_style('emsm-style', EMSM_URL.'css/emsm.css', '', '0.1.0');
}
add_action('wp_enqueue_scripts', 'emsm_scripts_styles');

/**
 * emsm_display_social_media function.
 * 
 * @access public
 * @param string $args (default: '')
 * @return void
 */
function emsm_display_social_media($args='') {
	$default_args=array(
		'echo' => false
	);
	$args=wp_parse_args($args, $default_args);
	$emsm_social_media=get_option('emsm_social_media', array());
	$html=null;

	if (!count($emsm_social_media))
		return;
		
	$html='<div class="emsm-display-wrap social_links">';	
	
		$html.='<ul class="emsm-social-media-list">';
			foreach ($emsm_social_media as $slug => $sm) :
				$html.='<li id="emsm-'.$slug.'"><a href="'.$sm['url'].'" target="_blank"><i class="fa '.$sm['icon'].'"></i></a></li>';
			endforeach;
		$html.='</ul>';
	
	$html.='</div>';

	if ($args['echo'])
		echo $html;
		
	return $html;
}
?>
