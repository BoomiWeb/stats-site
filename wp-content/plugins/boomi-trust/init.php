<?php
	
/**
 * boomi_trust_rewrite_rules function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_rewrite_rules() {
	add_rewrite_rule('statuses/([^/]*)/?', 'index.php?page_id=116&status_date=$matches[1]', 'top');
}
add_action('init', 'boomi_trust_rewrite_rules', 10, 0);

/**
 * boomi_trust_register_query_vars function.
 * 
 * @access public
 * @param mixed $vars
 * @return void
 */
function boomi_trust_register_query_vars($vars) {
  $vars[]='status_date';

  return $vars;
}
add_filter('query_vars', 'boomi_trust_register_query_vars');
?>