<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->

<head>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	
	<div class="wrapper">
	
		<div class="container-fluid primary-nav">
			<div class="container">
				<nav class="navbar navbar-default" role="navigation">
					<div class="navbar-header">
        				<a href="https://status.boomi.com"><img src="https://cdn.brandfolder.io/2E9KWBX8/as/mmc6hh5kqjsk4jmv8wtgsj/Boomi_Logo_Tagline_2-Color_Reversed.svg?position=2" height="" width="" alt="boomi-logo" /></a>
					</div>
					<?php get_template_part('template-parts/navigation/primary', 'menu'); ?>
				</nav>
			</div><!-- .container -->
		</div><!-- .navigation -->
