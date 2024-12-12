<?php
/**
 * The default template for displaying page content
 *
 * @since boomi-trust 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php the_content( __( 'Continue reading <span class="meta-nav">&raquo;</span>', 'boomi-trust' ) ); ?>
</article><!-- #post-## -->