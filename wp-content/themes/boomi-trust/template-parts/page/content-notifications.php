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
$release_control_dates = boomi_pc_get_rcd_dates();
$release_dates = boomi_pc_get_rd();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		
		<?php the_content( __( 'Continue reading <span class="meta-nav">&raquo;</span>', 'boomi-trust' ) ); ?>
		
		<div class="row release-dates-wrap">
			
			<?php if (!empty($release_control_dates)) : ?>
			
    			<div class="col-xs-12 col-sm-3 release-control-dates">
    				<div class="col-title">Release Control Date</div>
    				
    				<div class="row dates">
    					
    					<div class="col-xs-12">
    						<div class="dates-wrap">
    						
    							<?php foreach ($release_control_dates as $rcd) : ?>
    
    								<div class="col-xs-12">
    									<?php echo date('F d, Y', strtotime($rcd)); ?>
    								</div>
    							
    							<?php endforeach; ?>
    						
    						</div>
    					</div>
    					
    				</div>
    			</div>
			
			<?php endif; ?>	
				
			<?php if (!empty($release_dates)) : ?>
			
				<div class="col-xs-12 col-sm-3 release-dates">
					<div class="col-title">Release Date</div>
					
					<div class="row dates">
						
						<div class="col-xs-12">
							<div class="dates-wrap">
							
								<?php foreach ($release_dates as $rd) : ?>
								
									<div class="col-xs-12">
										<?php echo date('F d, Y', strtotime($rd)); ?>
									</div>
								
								<?php endforeach; ?>
							
							</div>
						</div>
						
					</div>
				</div>
				
			<?php endif; ?>
			
		</div>
		
	</div><!-- .entry-content -->

</article><!-- #post-## -->