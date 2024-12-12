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
			<div class="col-12 heading-title">
    			<?php the_title('<h1>', '</h1>'); ?>
			</div>
		</div>
	</div>
	
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<?php get_template_part('template-parts/page/content', ''); ?>
	<?php endwhile; else: ?>
		<p><?php _e('Sorry, this page does not exist.', 'boomi-trust'); ?></p>
	<?php endif; ?>

<?php get_footer(); ?>