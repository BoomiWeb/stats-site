<?php
/**
 * The default template for displaying page content
 *
 * @since boomi-trust 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<!--
	<header class="entry-header">
		<?php //the_title( '<h1 class="entry-title">', '</h1>'); ?>
	</header>
	-->

	<div class="entry-content">
		<?php the_content( __( 'Continue reading <span class="meta-nav">&raquo;</span>', 'boomi-trust' ) ); ?>
	</div><!-- .entry-content -->

</article><!-- #post-## -->