<?php

/**
 * boomi_trust_cron_jobs function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_cron_jobs() {
echo 'boomi_trust_cron_jobs';    
    php_custom_logger()->log('begin cron jobs');
echo "CROOOON";            
	$update_statistics=new Boomi_Trust_Update_Statistics();
	$update_daily_integrations=new Boomi_Trust_Update_Daily_Integrations();
	
    php_custom_logger()->log('end cron jobs');;
}
add_action('boomi_trust_statistics_cron_run', 'boomi_trust_cron_jobs');	



/*
add_filter( 'cron_schedules', 'bl_add_cron_intervals' );

function bl_add_cron_intervals( $schedules ) {

   $schedules['5seconds'] = array( // Provide the programmatic name to be used in code
      'interval' => 5, // Intervals are listed in seconds
      'display' => __('Every 5 Seconds') // Easy to read display name
   );
   return $schedules; // Do not forget to give back the list of schedules!
}

add_action( 'bl_cron_hook', 'bl_cron_exec' );

if( !wp_next_scheduled( 'bl_cron_hook' ) ) {
   wp_schedule_event( time(), '5seconds', 'bl_cron_hook' );
}

function bl_cron_exec() {
   echo "Oh Lookie! This is your scheduled cron, grinding out some hardcore tasks...And now a kitty!<br/><figure><img src='http://wpengine.com/wp-content/uploads/2012/04/lolcat-stealin-ur-heart.jpg'/><figcaption>Photo courtesy WCOC 2012, Stolen from WPEngine</figcaption></figure>";
}
*/