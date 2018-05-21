<div class="container">
	<div class="row">
		<div class="col-md-7">
			
			<div class="row cloud-statuses">
			
				<?php foreach (boomi_trust_cloud_statuses() as $status) : ?>
					<div class="col-md-4">
						<div class="cloud-status <?php boomi_trust_cloud_status_class($status->ID); ?>">
							<div class="service"><?php boomi_trust_cloud_service($status->ID); ?></div>
							<div class="status"><?php boomi_trust_cloud_status($status->ID); ?></div>
						</div>
					</div>
				<?php endforeach; ?>			
			</div>
	
			<div class="row">
				<div class="col-xs-12">
					<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
						<?php the_content(); ?>
					<?php endwhile; endif; ?>
				</div>
			</div>
		</div>
		
		<div class="col-md-5">
			<?php echo do_shortcode('[pickle_calendar]'); ?>
		</div>
	</div>
</div><!-- .container -->