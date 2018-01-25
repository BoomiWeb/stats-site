<?php
global $simcoe_calendar;

class Simcoe_Calendar {
	
	public $version;
	
	public function __construct() {
		include_once(BOOMI_TRUST_PATH.'calendar/post-type.php');
		include_once(BOOMI_TRUST_PATH.'calendar/event-type-query.php');
		
		$this->version='0.1.2';
		
		add_action('wp_ajax_bscal_nav', array($this, 'ajax_nav'));
		add_action('wp_ajax_nopriv_bscal_nav', array($this, 'ajax_nav'));
		add_action('wp_enqueue_scripts', array($this, 'scripts_styles'));
	}
	
	public function scripts_styles() {
		wp_register_script('simcoe-calendar-script', BOOMI_TRUST_URL.'calendar/js/calendar.js', array('jquery'), $this->version, true);
		
		wp_localize_script('simcoe-calendar-script', 'simcoeCalOpts', array(
			'ajax_url' => admin_url('admin-ajax.php')
		));
		
		wp_enqueue_script('simcoe-calendar-script');
		
		wp_register_style('simcoe-calendar-style', BOOMI_TRUST_URL.'calendar/css/calendar.css', '', $this->version);		
	}
	
	/**
	 * calendar function.
	 * 
	 * @access public
	 * @param string $args (default: '')
	 * @return void
	 */
	public function calendar($args='') {
		$html=null;
		
		$default_args=array(
			'month' => date('n'),
			'year' => date('Y'),
			'days_of_week_format' => 'D',
			'month_format' => 'F',
			'echo' => true,
		);
		$args=wp_parse_args($args, $default_args);	
		
		wp_enqueue_style('simcoe-calendar-style');
		
		$html.=apply_filters('simcoe_calendar_before_calendar', '', $args);
		
		$html.='<div class="col-xs-12 simcoe-calendar">';
			$html.=$this->create_header($args['month'], $args['year'], $args['month_format']);
			$html.=$this->days_of_week($args['days_of_week_format']);
			$html.=$this->draw_calendar($args['month'], $args['year']);
			
			$html.=apply_filters('simcoe_calendar_after_calendar', '', $args);
		$html.='</div>';
		
		if ($args['echo'])
			echo $html;
		
		return $html;
	}
	
	/**
	 * create_header function.
	 * 
	 * @access protected
	 * @param mixed $month
	 * @param mixed $year
	 * @param mixed $month_format
	 * @return void
	 */
	protected function create_header($month, $year, $month_format) {
		$html=null;
		
		$html.='<div class="row header">';
			$html.='<div class="col-xs-2 prev"><a class="cal-nav" href="#" data-month="'.$this->prev_month($month).'" data-year="'.$this->nav_year($month, $year, 'prev').'">&#10094;</a></div>';
			$html.='<div class="col-xs-5 month">'.$this->month($month, $month_format).'</div>';
			$html.='<div class="col-xs-3 year">'.$year.'</div>';
			$html.='<div class="col-xs-2 next"><a class="cal-nav" href="#" data-month="'.$this->next_month($month).'" data-year="'.$this->nav_year($month, $year, 'next').'">&#10095;</a></div>';			
		$html.='</div>';

		return apply_filters('simcoe_calendar_create_header', $html, $month, $year);
	}
	
	/**
	 * days_of_week function.
	 * 
	 * @access protected
	 * @param mixed $format
	 * @return void
	 */
	protected function days_of_week($format) {
		$html=null;
		$dow=array(0, 1, 2, 3, 4, 5, 6);
		$dow_formatted=array_map(array($this, 'format_day'), $dow);

		$html.='<div class="row weekdays">';
		
			foreach ($dow_formatted as $day) :
				$html.='<div class="dow">'.$day.'</div>';
			endforeach;
		
		$html.='</div>';
		
		return apply_filters('simcoe_calendar_weekdays', $html, $dow);
	}
	
	/**
	 * format_day function.
	 * 
	 * @access protected
	 * @param mixed $day
	 * @return void
	 */
	protected function format_day($day) {
		$format=apply_filters('bscal_day_format', 'D');
		
		return date($format, strtotime("Sunday + $day Days"));
	}
	
	/**
	 * month function.
	 * 
	 * @access protected
	 * @param mixed $month
	 * @param string $format (default: 'F')
	 * @return void
	 */
	protected function month($month, $format='F') {
		return date($format, mktime(0, 0, 0, $month));
	}
	
	/**
	 * draw_calendar function.
	 * 
	 * @access protected
	 * @param mixed $month
	 * @param mixed $year
	 * @return void
	 */
	protected function draw_calendar($month, $year) {
		$html=null;
		
		// days and weeks vars now //
		$running_day = date('w',mktime(0,0,0,$month,1,$year)); // numeric rep of week
		$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
		$days_in_this_week = 1;
		$day_counter = 0;
		$dates_array = array();
		$current_date=date('Y-m-d');
	
		$html.='<div class="cal-wrap">';
	
		// row for week one //
		$html.='<div class="row">';
	
		// print "blank" days until the first of the current week //
		for ($x = 0; $x < $running_day; $x++) :
			$html.= '<div class="calendar-day np"></div>';
			$days_in_this_week++;
		endfor;
	
		// keep going with days //
		for ($list_day = 1; $list_day <= $days_in_month; $list_day++) :
			
			if (date('Y-m-d', strtotime("$year-$month-$list_day"))==$current_date) :
				$class='calendar-day today';
			else :
				$class='calendar-day';
			endif;
		
			$html.= '<div class="'.$class.'">';
				$html.= '<div class="day-number">'.$list_day.'</div>'; // add day number
	
				$html.=apply_filters('simcoe_calendar_single_day', '', date('Y-m-d', strtotime("$year-$month-$list_day")));
				
			$html.= '</div>';
			
			if ($running_day == 6) :
				$html.= '</div>';
				
				if (($day_counter+1) != $days_in_month):
					$html.= '<div class="row">';
				endif;
				
				$running_day = -1;
				$days_in_this_week = 0;
			endif;
			
			$days_in_this_week++; 
			$running_day++; 
			$day_counter++;
		endfor;
	
		// finish the rest of the days in the week //
		if ($days_in_this_week < 8) :
			for($x = 1; $x <= (8 - $days_in_this_week); $x++):
				$html.= '<div class="calendar-day np"></div>';
			endfor;
		endif;
	
		// final row //
		$html.= '</div>';
		
		$html.= '</div>';
		
		return $html;
	}
	
	/**
	 * next_month function.
	 * 
	 * @access protected
	 * @param mixed $month
	 * @return void
	 */
	protected function next_month($month) {
		$next_month=$month+1;
		
		if ($next_month > 12)
			$next_month=1;
		
		return $next_month;
	}

	/**
	 * prev_month function.
	 * 
	 * @access protected
	 * @param mixed $month
	 * @return void
	 */
	protected function prev_month($month) {
		$prev_month=$month-1;
		
		if ($prev_month < 1)
			$prev_month=12;
		
		return $prev_month;
	}
	
	/**
	 * nav_year function.
	 * 
	 * @access protected
	 * @param mixed $month
	 * @param mixed $year
	 * @param mixed $action
	 * @return void
	 */
	protected function nav_year($month, $year, $action) {
		$nav_year=$year;
		
		switch ($action) :
			case 'prev' :
				if ($this->prev_month($month)==12)
					$nav_year=$year-1;
				break;
			case 'next' :
				if ($this->next_month($month)==1)
					$nav_year=$year+1;
				break;
		endswitch;
		
		return $nav_year;
	}
	
	/**
	 * ajax_nav function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_nav() {	
		$args=array(
			'month' => $_POST['month'],
			'year' => $_POST['year'],
			'echo' => false
		);

		echo $this->calendar($args);	
		
		wp_die();
	}
	
}	

$simcoe_calendar=new Simcoe_Calendar();
?>