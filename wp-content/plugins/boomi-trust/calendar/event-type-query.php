<?php
global $event_type_post;

/**
 * EventTypeQuery class.
 */
class EventTypeQuery {

	public $posts;

	public $query_vars;

	public $current_post=-1;

	public $post_count=0;

	public $post;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param string $query (default: '')
	 * @return void
	 */
	public function __construct($query='') {
		$this->query($query);
	}

	/**
	 * default_query_vars function.
	 * 
	 * @access public
	 * @return void
	 */
	public function default_query_vars() {
		$array=array(
			'post_type' => 'scevent',
			'type' => '',
		);

		return $array;
	}

	/**
	 * set_query_vars function.
	 * 
	 * @access public
	 * @param string $query (default: '')
	 * @return void
	 */
	public function set_query_vars($query='') {
		$args=wp_parse_args($query, $this->default_query_vars());

		return $args;
	}

	/**
	 * query function.
	 * 
	 * @access public
	 * @param string $query (default: '')
	 * @return void
	 */
	public function query($query='') {
		$this->query_vars=$this->set_query_vars($query);
		
		$this->get_posts();

		return $this;
	}

	/**
	 * get_posts function.
	 * 
	 * @access public
	 * @return void
	 */
	public function get_posts() {		
		$this->posts=get_posts(
			array(
				'posts_per_page' => -1,
				'post_type' => $this->query_vars['post_type'],
				'orderby' => 'meta_vlue',
				'order' => 'ASC',
				'meta_query' => array(
					array(
						'key' => $this->query_vars['type'],
						'type' => 'DATE'
					),
				),			
			)
		);

		$this->post_count=count($this->posts);

		return $this->posts;
	}

	/**
	 * have_posts function.
	 * 
	 * @access public
	 * @return void
	 */
	public function have_posts() {
		if ($this->current_post + 1 < $this->post_count) :
			return true;
		elseif ( $this->current_post + 1 == $this->post_count && $this->post_count > 0 ) :
			$this->rewind_posts();
		endif;

		return false;
	}

	/**
	 * the_post function.
	 * 
	 * @access public
	 * @return void
	 */
	public function the_post() {
		global $event_type_post;

		$event_type_post=$this->next_post();
	}

	/**
	 * next_post function.
	 * 
	 * @access public
	 * @return void
	 */
	public function next_post() {
		$this->current_post++;

		$this->post=$this->posts[$this->current_post];

		return $this->post;
	}

	/**
	 * rewind_posts function.
	 * 
	 * @access public
	 * @return void
	 */
	public function rewind_posts() {
		$this->current_post=-1;

		if ( $this->post_count > 0 )
			$this->post = $this->posts[0];
	}

}

/**
 * event_type_ID function.
 * 
 * @access public
 * @return void
 */
function event_type_ID() {
	echo get_event_type_ID();
}

/**
 * get_event_type_ID function.
 * 
 * @access public
 * @return void
 */
function get_event_type_ID() {
	global $event_type_post;
	
	return $event_type_post->ID;	
}
?>