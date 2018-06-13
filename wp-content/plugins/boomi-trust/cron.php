<?php

/**
 * boomi_trust_cron_jobs function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_cron_jobs() {    
    php_custom_logger()->log('begin trust cron jobs');

    $update_daily_integrations=boomi_trust_update_daily_integrations()->run();
    $update_statistics = boomi_trust_update_statistics()->run();
    
	php_custom_logger()->log($update_statistics);
	php_custom_logger()->log($update_daily_integrations);
		
    php_custom_logger()->log('end trust cron jobs');
}
//add_action('boomi_trust_statistics_cron_run', 'boomi_trust_cron_jobs');	