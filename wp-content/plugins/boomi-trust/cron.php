<?php

/**
 * boomi_trust_cron_jobs function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_cron_jobs() {
    php_custom_logger()->log('begin cron jobs');
            
	$update_statistics=new Boomi_Trust_Update_Statistics();
	$update_daily_integrations=new Boomi_Trust_Update_Daily_Integrations();
	
    php_custom_logger()->log('end cron jobs');
}
add_action('boomi_trust_statistics_cron_run', 'boomi_trust_cron_jobs');	