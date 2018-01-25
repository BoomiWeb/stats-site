<?php
/**
 * boomi_trust_theme_meta function.
 *
 * adds default theme meta to header
 * hooks directly after meta robots
 *
 * @access public
 * @return void
 */
function boomi_trust_theme_meta() {

	echo apply_filters('boomi_trust_meta_charset', '<meta charset="'.get_bloginfo( 'charset' ).'" />'."\n");
	echo apply_filters('boomi_trust_meta_http-equiv', '<meta http-equiv="X-UA-Compatible" content="IE=edge">'."\n");
	echo apply_filters('boomi_trust_meta_viewport', '<meta name="viewport" content="width=device-width, initial-scale=1.0">'."\n");
	echo apply_filters('boomi_trust_meta_description', '<meta name="description" content="'.boomi_trust_display_meta_description().'">'."\n");
	echo apply_filters('boomi_trust_meta_author', '<meta name="author" content="">'."\n");

}
add_action('wp_head', 'boomi_trust_theme_meta', 1);

/**
 * boomi_trust_disable_seo_meta function.
 *
 * checks for Yoast SEO and removes description meta
 * fires on 0 so that's it's before our meta
 *
 * @access public
 * @return void
 */
function boomi_trust_disable_seo_meta() {
	if ( defined('WPSEO_VERSION') ) :
		add_filter('boomi_trust_meta_description', 'disable_boomi_trust_meta_description', 10, 1);
	endif;
}
add_action('wp_head','boomi_trust_disable_seo_meta',0);

/**
 * disable_boomi_trust_meta_description function.
 *
 * simply returns a null value so no description is output
 *
 * @access public
 * @param mixed $meta
 * @return null
 */
function disable_boomi_trust_meta_description($meta) {
	return null;
}
?>