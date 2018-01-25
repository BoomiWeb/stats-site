<?php
global $simcoe_calendar_admin;

class SimcoeCalendarAdmin {
	
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts_styles'));
		add_action('admin_menu', array($this, 'admin_menu'));
	}

	public function admin_scripts_styles() {
		//wp_enqueue_script('simcoe-calendar-script', BOOMI_TRUST_URL.'calendar/js/calendar.js', array('jquery'), $this->version, true);
		
		//wp_enqueue_style('simcoe-calendar-style', BOOMI_TRUST_URL.'calendar/css/calendar.css', '', $this->version);
	}
	
	public function admin_menu() {
		add_options_page('Simcoe Calendar', 'Simcoe Calendar', 'manage_options', 'simcoe-calendar', array($this, 'settings_page'));
	}
	
	public function settings_page() {
		$html='';
		
		$html.='<div class="wrap">';
			$html.='<h1>Simcoe Calendar</h1>';
		$html.='</div>';
		
		echo $html;
	}
}	

$simcoe_calendar_admin=new SimcoeCalendarAdmin();
?>