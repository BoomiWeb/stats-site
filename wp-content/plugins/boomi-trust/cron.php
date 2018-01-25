<?php

/**
 * boomi_trust_status_update function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_status_update() {
	$update_statuses=new Boomi_Trust_Update_Statuses();
	$update_statuses->duplicate_statuses();
}
add_action('boomi_trust_status_cron_run', 'boomi_trust_status_update');	
?>