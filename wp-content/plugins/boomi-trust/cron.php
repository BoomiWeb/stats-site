<?php

/**
 * boomi_trust_statistics_update function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_statistics_update() {
	$update_statistics=new Boomi_Trust_Update_Statistics();
}
add_action('boomi_trust_statistics_cron_run', 'boomi_trust_statistics_update');	