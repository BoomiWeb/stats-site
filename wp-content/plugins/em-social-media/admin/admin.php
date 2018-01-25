<?php
global $emsm_admin;

class EMSocialMediaAdmin {

	public $social_media;
	public $icons;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action('admin_init', array($this, 'update_settings'));
		add_action('admin_menu', array($this, 'add_plugin_page'));
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts_styles'));

		$this->social_media=$this->setup_social_media();
		$this->icons=$this->get_fa_arr();
	}

	/**
	 * admin_scripts_styles function.
	 * 
	 * @access public
	 * @return void
	 */
	public function admin_scripts_styles() {
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('emsm-admin-script', EMSM_URL.'admin/js/admin.js', array('jquery-ui-dialog', 'jquery-ui-sortable'), '0.1.0', true);
		
		wp_enqueue_style('wp-jquery-ui-dialog');
		wp_enqueue_style('font-awesome-style', EMSM_URL.'font-awesome/font-awesome.min.css', '', '4.7.0');
		wp_enqueue_style('emsm-admin-style', EMSM_URL.'admin/css/admin.css', '', '0.1.0');
	}

	/**
	 * add_plugin_page function.
	 * 
	 * @access public
	 * @return void
	 */
	public function add_plugin_page() {
    	add_options_page('Social Media','Social Media','manage_options','em-social-media', array($this, 'admin_page'));
	}

	/**
	 * setup_settings function.
	 * 
	 * @access public
	 * @return void
	 */
	public function setup_social_media() {
		$args=get_option('emsm_social_media', $this->_default_args());

		// sort by order //
		@usort($args, function($a, $b) {
			return $a['order'] - $b['order'];
		});

		return $args;
	}
	
	/**
	 * _default_args function.
	 * 
	 * @access protected
	 * @return void
	 */
	protected function _default_args() {
		return array(
			'facebook' => array(
				'name' => 'Facebook',
				'url' => 'https://www.facebook.com/WordPress',
				'icon' => 'fa-facebook-square',
				'order' => 1,
			),
			'twitter' => array(
				'name' => 'Twitter',
				'url' => 'https://twitter.com/wordpress',
				'icon' => 'fa-twitter-square',
				'order' => 2,
			)
		);
	}

	/**
	 * admin_page function.
	 * 
	 * @access public
	 * @return void
	 */
	public function admin_page() {
		echo $this->get_admin_page('settings');
	}

	/**
	 * update_settings function.
	 * 
	 * @access public
	 * @return void
	 */
	public function update_settings() {
		$smo=array();
		
		if (!isset($_POST['emsm_admin']) || !wp_verify_nonce($_POST['emsm_admin'], 'update_settings'))
			return; 

		foreach ($_POST['social_media_options'] as $slug => $data) :
			if (empty($data['name']))
				continue;
				
			$new_slug=sanitize_key($data['name']);
			$smo[$new_slug]=$data;
		endforeach;	

		update_option('emsm_social_media', $smo);
		
		$this->social_media=$this->setup_social_media();
	}

	/**
	 * get_fa_arr function.
	 * 
	 * @access public
	 * @return void
	 */
	public function get_fa_arr() {
		$fa_css_prefix='fa';
		$css = file_get_contents(EMSM_PATH.'font-awesome/font-awesome.min.css');
		$pattern='/\.('.$fa_css_prefix.'-(?:\w+(?:-)?)+):/';
		$icons=array();

		preg_match_all($pattern, $css, $matches, PREG_SET_ORDER);

		foreach($matches as $match) :
			$clean_match=preg_replace('/(fa-)/','',$match[1]);
			$clean_match=preg_replace('/(-)/',' ',$clean_match);

			$icons[]=array(
				'name' => ucwords($clean_match),
				'class' => $match[1]
			);
		endforeach;

		usort($icons, function ($a, $b) {
	    	return strcmp($a['class'], $b['class']);
		});

		return $icons;
	}
	
	/**
	 * get_admin_page function.
	 * 
	 * @access public
	 * @param bool $template_name (default: false)
	 * @return void
	 */
	public function get_admin_page($template_name=false) {
		if (!$template_name)
			return false;
	
		ob_start();
	
		do_action('emsm_before_admin_'.$template_name);
	
		include(EMSM_PATH.'admin/pages/'.$template_name.'.php');
	
		do_action('emsm_after_admin_'.$template_name);
	
		$html=ob_get_contents();
	
		ob_end_clean();
	
		return $html;
	}

}

$emsm_admin=new EMSocialMediaAdmin();

/**
 * wp_parse_args_multi function.
 * 
 * Similar to wp_parse_args() just a bit extended to work with multidimensional arrays
 *
 * @access public
 * @param mixed &$a
 * @param mixed $b
 * @return void
 */
function wp_parse_args_multi( &$a, $b ) {
	$a = (array) $a;
	$b = (array) $b;
	$result = $b;
	foreach ( $a as $k => &$v ) {
		if ( is_array( $v ) && isset( $result[ $k ] ) ) {
			$result[ $k ] = wp_parse_args_multi( $v, $result[ $k ] );
		} else {
			$result[ $k ] = $v;
		}
	}
	return $result;
}
?>
