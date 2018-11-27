<?php

class Boomi_Stats_Releases {
    
    public function __construct() {
        add_shortcode( 'boomi-stats-notifications', array($this, 'shortcode') );    
    }
    
    public function shortcode($atts) {
        $atts = shortcode_atts( array(), $atts, 'boomi-stats-notifications' );
        $html = '';
        $release_control = get_page_by_title('Release Control Date', OBJECT, 'release');
        $release_dates = get_page_by_title('Release Date', OBJECT, 'release'); 


        $html.='<article id="post-'.get_the_ID().'" '.get_post_class().'>';
        
        	$html.='<div class="entry-content">';
        		
        		//the_content( __( 'Continue reading <span class="meta-nav">&raquo;</span>', 'boomi-trust' ) );
        		
        		$html.='<div class="row release-dates-wrap">';
        			
        			if (!empty($release_control)) :
        			
            			$html.='<div class="col-xs-12 col-sm-6 release-control-dates">';
            				$html.='<div class="title">Release Control Date</div>';
            				
            				$html.='<div class="dates">';
        
        						$html.='<div class="dates-wrap">';
                                    						
                                    if( have_rows('dates', $release_control->ID) ):
                                    
                                        while ( have_rows('dates', $release_control->ID) ) : the_row();
                                            
                                            $html.= '<div class="col-xs-12">';
                                            
                                                if ('Date' === get_sub_field('display')) :
                                                    $html.= get_sub_field('date');
                                                else :
                                                    $html.= get_sub_field('text');
                                                endif;
                                            
                                            $html.= '</div>';
                                    
                                        endwhile;
                                    
                                    endif;
                                    						
        						$html.='</div>';
            					
            				$html.='</div>';
            			$html.='</div>';
        			
        			endif;	
        				
        			if (!empty($release_dates)) :
        			
        				$html.='<div class="col-xs-12 col-sm-6 release-dates">';
        					$html.='<div class="title">Release Date</div>';
        					
        					$html.='<div class="dates">';
        
        						$html.='<div class="dates-wrap">';
                                    						
                                    if( have_rows('dates', $release_dates->ID) ):
                                    
                                        while ( have_rows('dates', $release_dates->ID) ) : the_row();
                                            
                                            $html.= '<div class="col-xs-12">';
                                            
                                                if ('Date' === get_sub_field('display')) :
                                                    $html.= get_sub_field('date');
                                                else :
                                                    $html.= get_sub_field('text');
                                                endif;
                                            
                                            $html.= '</div>';
                                    
                                        endwhile;
                                    
                                    endif;
                                    	   						
        						$html.='</div>';
        						
        					$html.='</div>';
        				$html.='</div>';
        				
        			endif;
        			
        		$html.='</div>';
        		
        	$html.='</div><!-- .entry-content -->';
        
        $html.='</article><!-- #post-## -->  ';             
    }
    
}