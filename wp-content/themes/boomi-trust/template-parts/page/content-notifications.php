<?php
/**
 * The default template for displaying page content on the notifcations page template
 *
 * @package WordPress
 * @subpackage boomi-trust
 * @since boomi-trust 1.0.0
 */
?>

<?php
$release_control=new EventTypeQuery(array('type' => 'control_date'));
$release_dates=new EventTypeQuery(array('type' => 'release_date'));
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		
		<?php the_content( __( 'Continue reading <span class="meta-nav">&raquo;</span>', 'boomi-trust' ) ); ?>
		
		<div class="row release-dates-wrap">

			<?php if ($release_control->have_posts()) : ?>
			
				<div class="col-xs-12 col-sm-3 release-control-dates">
					<div class="col-title">Release Control Date</div>
					
					<div class="row dates">
						
						<div class="col-xs-12">
							<div class="dates-wrap">
							
								<?php while ($release_control->have_posts()) : $release_control->the_post(); ?>

									<div class="col-xs-12">
										<?php echo date('F d, Y', strtotime(get_field('control_date', get_event_type_ID()))); ?>
									</div>
								
								<?php endwhile; ?>
							
							</div>
						</div>
						
					</div>
				</div>
				
			<?php endif; ?>

			<?php if ($release_dates->have_posts()) : ?>
			
				<div class="col-xs-12 col-sm-3 release-dates">
					<div class="col-title">Release Date</div>
					
					<div class="row dates">
						
						<div class="col-xs-12">
							<div class="dates-wrap">
							
								<?php while ($release_dates->have_posts()) : $release_dates->the_post(); ?>
								
									<div class="col-xs-12">
										<?php echo date('F d, Y', strtotime(get_field('release_date', get_event_type_ID()))); ?>
									</div>
								
								<?php endwhile; ?>
							
							</div>
						</div>
						
					</div>
				</div>
				
			<?php endif; ?>
			
		</div>
		
	</div><!-- .entry-content -->

</article><!-- #post-## -->