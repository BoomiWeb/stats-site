<?php

class Boomi_Stats_Releases {
    
    public function __construct() {
        add_shortcode( 'boomi-stats-notifications', array($this, 'shortcode') );    
    }
    
    public function shortcode($atts) {
        $atts = shortcode_atts( array(), $atts, 'boomi-stats-notifications' );
        $html = '';
        $upcoming_releases = get_field('upcoming_releases');
        
        $html.='<div class="boomi-stats-notifications">';
            $html .= '<div class="upcoming-releases">';
        		$html .= '<div class="upcoming-releases-header">';
        		    $html .= '<h2>Upcoming Releases</h2>';
        		$html .= '</div>';
    
                $html.='<div class="row">';
                    if( have_rows('upcoming_releases') ):
                        while ( have_rows('upcoming_releases') ) : the_row();
                            
                            if (have_rows('upcoming_release_control_dates')) :
                                $html .='<div class="col-xs-12 col-sm-6 release-control-dates">';
                                    $html .= '<h3>Upcoming Release Control Dates</h3>';
                            
                                    while ( have_rows('upcoming_release_control_dates') ) : the_row();
                                        $html .= '<div class="row">';
                                            $html.='<div class="col-xs-12 date">'.get_sub_field('date').'</div>';
                                        $html.='</div>';
                                    endwhile;
                                
                                $html.='</div>';
                            endif;
                            
                            if (have_rows('upcoming_release_dates')) :
                                $html .='<div class="col-xs-12 col-sm-6 release-dates">';
                                    $html .= '<h3>Upcoming Release Dates</h3>';
                            
                                    while ( have_rows('upcoming_release_dates') ) : the_row();
                                        $html .= '<div class="row">';
                                            $html.='<div class="col-xs-12 date">'.get_sub_field('date').'</div>';
                                        $html.='</div>';
                                    endwhile;
                                
                                $html.='</div>';
                            endif;
        
                        endwhile;
                    endif;  
                $html.='</div>';  		
        		
            $html .= '</div>';
    	
            $html .= '<div class="release-archive">';
        		$html .= '<div class="release-archive-header">';
        		    $html .= '<h2>Release Archive</h2>';
        		$html .= '</div>';
        		
                $html.='<div class="row">';
                    if( have_rows('release_archive') ):
                        while ( have_rows('release_archive') ) : the_row();
                            
                            if (have_rows('completed_release_control_dates')) :
                                $html .='<div class="col-xs-12 col-sm-6 completed-release-control-dates">';
                                    $html .= '<h3>Completed Release Control Dates</h3>';
                            
                                    while ( have_rows('completed_release_control_dates') ) : the_row();
                                        $html .= '<div class="row">';
                                            $html.='<div class="col-xs-12 date">'.get_sub_field('date').'</div>';
                                        $html.='</div>';
                                    endwhile;
                                
                                $html.='</div>';
                            endif;
                            
                            if (have_rows('completed_release_dates')) :
                                $html .='<div class="col-xs-12 col-sm-6 completed-release-dates">';
                                    $html .= '<h3>Completed Release Dates</h3>';
                            
                                    while ( have_rows('completed_release_dates') ) : the_row();
                                        $html .= '<div class="row">';
                                            $html.='<div class="col-xs-12 date">'.get_sub_field('date').'</div>';
                                        $html.='</div>';
                                    endwhile;
                                
                                $html.='</div>';
                            endif;
        
                        endwhile;
                    endif;  
                $html.='</div>';    		
            $html .= '</div>';
        $html.='</div>';
        
        echo $html;
            
    }
    
}

new Boomi_Stats_Releases();