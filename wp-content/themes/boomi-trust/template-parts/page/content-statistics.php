<?php
/**
 * The default template for displaying page content on the statistics page template
 *
 * @since boomi-trust 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		
		<?php the_content( __( 'Continue reading <span class="meta-nav">&raquo;</span>', 'boomi-trust' ) ); ?>
		
		<div class="row statistics">
			
			<?php if ( have_rows( 'statistic' ) ) : ?>
			
				<?php while ( have_rows( 'statistic' ) ) : the_row(); ?>
				
					<div class="col-xs-6 col-sm-3">
						<div class="stat-wrap">
							<div class="row stat-number">
								<div class="col-xs-12"><?php echo number_format(get_sub_field('number')); ?></div>
							</div>
	
							<div class="row stat-title">
								<div class="col-xs-12"><?php the_sub_field('title'); ?></div>
							</div>
							
							<div class="row stat-description">
								<div class="col-xs-12"><?php the_sub_field('description'); ?></div>
							</div>
						</div>
					</div>
					
				<?php endwhile; ?>
				
			<?php endif; ?>
			
		</div>
		
	</div><!-- .entry-content -->

</article><!-- #post-## -->