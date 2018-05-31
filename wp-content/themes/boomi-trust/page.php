<?php
/**
 * The template for displaying a page
 *
 * @since boomi-trust 1.0.0
 */
?>

<?php get_header(); ?>
	
	<div class="container">
		<div class="row">
			<div class="col-md-12">
	
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
					<?php get_template_part('template-parts/page/content', boomi_trust_page_slug(get_the_ID())); ?>
				<?php endwhile; else: ?>
					<p><?php _e('Sorry, this page does not exist.', 'boomi-trust'); ?></p>
				<?php endif; ?>
				
			</div>
		</div>
	</div><!-- .container -->

<?php get_footer(); ?>