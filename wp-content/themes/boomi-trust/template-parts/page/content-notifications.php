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
$release_control = get_page_by_title('Release Control Date', OBJECT, 'release');
$release_ = get_page_by_title('Release Date', OBJECT, 'release');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		
		<?php the_content( __( 'Continue reading <span class="meta-nav">&raquo;</span>', 'boomi-trust' ) ); ?>
		
		<div class="row release-dates-wrap">
			
			<?php if (!empty($release_control)) : ?>
			
    			<div class="col-xs-12 col-sm-6 release-control-dates">
    				<div class="title">Release Control Date</div>
    				
    				<div class="dates">

						<div class="dates-wrap">
<?php						
// check if the repeater field has rows of data
if( have_rows('dates') ):

 	// loop through the rows of data
    while ( have_rows('dates') ) : the_row();

        // display a sub field value
        the_sub_field('date');
        the_sub_field('text');

    endwhile;

else :

    // no rows found

endif;
?>						
						
							<?php foreach ($release_control_dates as $rcd) : ?>

								<div class="col-xs-12">
									<?php echo date('F d, Y', strtotime($rcd)); ?>
								</div>
							
							<?php endforeach; ?>
						
						</div>
    					
    				</div>
    			</div>
			
			<?php endif; ?>	
				
			<?php if (!empty($release_dates)) : ?>
			
				<div class="col-xs-12 col-sm-6 release-dates">
					<div class="title">Release Date</div>
					
					<div class="dates">

						<div class="dates-wrap">
						
							<?php foreach ($release_dates as $rd) : ?>
							
								<div class="col-xs-12">
									<?php echo date('F d, Y', strtotime($rd)); ?>
								</div>
							
							<?php endforeach; ?>
						
						</div>
						
					</div>
				</div>
				
			<?php endif; ?>
			
		</div>
		
	</div><!-- .entry-content -->

</article><!-- #post-## -->