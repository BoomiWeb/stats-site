<?php

/**
 * EMSMWidget class.
 * 
 * @extends WP_Widget
 */
class EMSMWidget extends WP_Widget {

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct(
			'emsm_widget',
			__('Social Media', 'emsm'),
			array('description' => __('Displays custom Social Media settings', 'emsm'))
		);
	}

	/**
	 * widget function.
	 * 
	 * @access public
	 * @param mixed $args
	 * @param mixed $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		
		if (!empty($instance['title']))
			echo $args['before_title'].apply_filters('emsm_widget_title', $instance['title']).$args['after_title'];

		emsm_display_social_media(array(
			'echo' => true
		));

		echo $args['after_widget'];
	}

	/**
	 * form function.
	 * 
	 * @access public
	 * @param mixed $instance
	 * @return void
	 */
	public function form($instance) {
		$title = !empty($instance['title']) ? $instance['title'] : __( 'Social Media', 'emsm');
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
		</p>
		<?php
	}

	/**
	 * update function.
	 * 
	 * @access public
	 * @param mixed $new_instance
	 * @param mixed $old_instance
	 * @return void
	 */
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title']=(!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

		return $instance;
	}

}

/**
 * emsm_register_widgets function.
 * 
 * @access public
 * @return void
 */
function emsm_register_widgets() {
	register_widget('EMSMWidget');
}
add_action('widgets_init', 'emsm_register_widgets');
?>