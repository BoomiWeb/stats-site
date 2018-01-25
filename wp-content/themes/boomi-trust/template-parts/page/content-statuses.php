<?php
$date=get_query_var('status_date');
$cloud_status=new Boomi_Trust_Cloud_Status();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content statuses-single">

		<div class="row date">
			<div class="col-xs-12">
				<div class="date-inner">
					<h2><?php echo date(get_option('date_format'), strtotime($date)); ?></h2>
				</div>
			</div>
		</div>
		
		<?php foreach (boomi_trust_cloud_services() as $service_id) : ?>
		
			<?php 
			$statuses=$cloud_status->get_status_list(array(
				'service' => $service_id,
				'date' => $date,
			)); 
			?>
			
			<div id="service-<?php echo $service_id; ?>" class="row service">
				
				<div class="col-xs-12">
					<h2><?php echo get_the_title($service_id); ?></h2>
				</div>
				
			</div>

			<?php foreach ($statuses as $status) : ?>
			
				<div id="status-<?php echo $status->ID; ?>" class="row status">
					<div class="col-xs-12 col-sm-6 col-md-3">
						<?php echo date(get_option('date_format').' h:i:s a', strtotime($status->date_time)); ?>
					</div>
					
					<div class="col-xs-12 col-sm-6 col-md-9">
						<h3><?php echo $status->status_type; ?></h3>
						
						<div class="message">
							<?php echo apply_filters('the_content', $status->post_content); ?>
						</div>
					</div>
				</div>
			
			<?php endforeach; ?>
			
		<?php endforeach; ?>
		
	</div><!-- .entry-content -->

</article><!-- #post-## -->