<div class="collapse navbar-collapse primary-menu">
	<?php
	wp_nav_menu(array(
		'theme_location' => 'primary',
		'container' => false,
		'menu_class' => 'nav navbar-nav pull-right',
		'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
		'walker' => new wp_bootstrap_navwalker()
	));
	?>
</div> <!-- .primary-menu -->