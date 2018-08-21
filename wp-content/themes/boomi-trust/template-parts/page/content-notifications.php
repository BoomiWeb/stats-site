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
$release_dates = get_page_by_title('Release Date', OBJECT, 'release');
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
                            if( have_rows('dates', $release_control->ID) ):
                            
                                while ( have_rows('dates', $release_control->ID) ) : the_row();
                                    
                                    echo '<div class="col-xs-12">';
                                    
                                        if ('Date' === get_sub_field('display')) :
                                            echo get_sub_field('date');
                                        else :
                                            echo get_sub_field('text');
                                        endif;
                                    
                                    echo '</div>';
                            
                                endwhile;
                            
                            endif;
                            ?>						
						</div>
    					
    				</div>
    			</div>
			
			<?php endif; ?>	
				
			<?php if (!empty($release_dates)) : ?>
			
				<div class="col-xs-12 col-sm-6 release-dates">
					<div class="title">Release Date</div>
					
					<div class="dates">

						<div class="dates-wrap">
                            <?php						
                            if( have_rows('dates', $release_dates->ID) ):
                            
                                while ( have_rows('dates', $release_dates->ID) ) : the_row();
                                    
                                    echo '<div class="col-xs-12">';
                                    
                                        if ('Date' === get_sub_field('display')) :
                                            echo get_sub_field('date');
                                        else :
                                            echo get_sub_field('text');
                                        endif;
                                    
                                    echo '</div>';
                            
                                endwhile;
                            
                            endif;
                            ?>	   						
						</div>
						
					</div>
				</div>
				
			<?php endif; ?>
			
		</div>
		
	</div><!-- .entry-content -->

</article><!-- #post-## -->