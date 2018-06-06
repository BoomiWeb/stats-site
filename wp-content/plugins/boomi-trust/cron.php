<?php

/**
 * boomi_trust_cron_jobs function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_cron_jobs() {
	Boomi_Trust_Update_Statistics();
	Boomi_Trust_Update_Daily_Integrations();
}
add_action('boomi_trust_statistics_cron_run', 'boomi_trust_cron_jobs');	