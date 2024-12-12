<?php
/**
 * Boomi Stats Release shortcode
 *
 * @package Boomi_Trust
 * @since   0.3.0
 */


/**
 * Boomi_Stats_Releases class.
 */
class Boomi_Stats_Releases {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_shortcode( 'boomi-stats-notifications', array( $this, 'shortcode' ) );
    }

    /**
     * shortcode function.
     *
     * @access public
     * @param mixed $atts array.
     * @return html
     */
    public function shortcode( $atts ) {
        $atts = shortcode_atts( array(), $atts, 'boomi-stats-notifications' );
        $html = '';
        $upcoming_releases = get_field( 'upcoming_releases' );
        $upcoming_releases_object = get_field_object('upcoming_releases');
        $upcoming_releases_label = $upcoming_releases_object['label'];
        
        $html .= '<div class="boomi-stats-notifications">';
            $html .= '<div class="upcoming-releases container">';
                $html .= '<div class="upcoming-releases-header row">';
                    $html .= '<div class="col-12">';
                        $html .= '<h2>'.$upcoming_releases_label.'</h2>';
                    $html .= '</div>';
                $html .= '</div>';
                
                $html .= '<div class="row releases">';
                    if ( have_rows( 'upcoming_releases' ) ) :
                        $html .= '<div class="upcoming-releases-container">';
                            while ( have_rows( 'upcoming_releases' ) ) : the_row();
                    
                                $urcd_object = get_sub_field_object('upcoming_release_control_dates');
                                $urcd_label = $urcd_object['label'];
                                
                                $urd_object = get_sub_field_object('upcoming_release_dates');
                                $urd_label = $urd_object['label'];                
                    
                                if ( have_rows( 'upcoming_release_control_dates' ) ) :

                                    $html .= '<div class="upcoming-release-control-dates">';
                                        $html .= '<div class="release-control-dates border-wrap">';
                                            $html .= '<div class="sub-head"><h3>'.$urcd_label.'</h3></div>';
                                            
                                            while ( have_rows( 'upcoming_release_control_dates' ) ) : the_row();
                                                $html .= '<div class="date">' . get_sub_field( 'date' ) . ' '. get_sub_field( 'details' ) . '</div>';
                                            endwhile;

                                        $html .= '</div>';
                                    $html .= '</div>';
                                endif;

                                if ( have_rows( 'upcoming_release_dates' ) ) :
                                    $html .= '<div class="upcoming-release-dates">';
                                        $html .= '<div class="release-dates border-wrap">';
                                            $html .= '<div class="sub-head"><h3>'.$urd_label.'</h3></div>';

                                            while ( have_rows( 'upcoming_release_dates' ) ) : the_row();
                                                $html .= '<div class="date">' . get_sub_field( 'date' ) . ' '. get_sub_field( 'details' ) . '</div>';
                                            endwhile;

                                        $html .= '</div>';
                                    $html .= '</div>';
                                endif;
                            endwhile;
                        $html .= '</div>';
                    endif;
                $html .= '</div>';
                
                $html.='<div class="footer-notice row"><div class="col-12"><p>Boomi reserves the right to adjust these dates as needed.</p></div></div>';
            $html .= '</div>';

            $release_archive = get_field( 'release_archive' );
            $release_archive_object = get_field_object('release_archive');
            $release_archive_label = $release_archive_object['label'];

            $html .= '<div class="release-archive container">';
                $html .= '<div class="release-archive-header row">';
                    $html .= '<div class="col-12">';
                        $html .= '<h2>'.$release_archive_label.'</h2>';
                    $html .= '</div>';
                $html .= '</div>';              

                $html .= '<div class="row releases">';
                    if ( have_rows( 'release_archive' ) ) :
                        $html .= '<div class="release-archive-container">';
                        
                            while ( have_rows( 'release_archive' ) ) : the_row();
                                $crcd_object = get_sub_field_object('completed_release_control_dates');
                                $crcd_label = $crcd_object['label'];
                
                                $crd_object = get_sub_field_object('completed_release_dates');
                                $crd_label = $crd_object['label'];                 

                                if ( have_rows( 'completed_release_control_dates' ) ) :
                                    $html .= '<div class="completed-release-control-dates-container">';
                                        $html .= '<div class="completed-release-control-dates border-wrap">';
                                            $html .= '<div class="sub-head"><h3>'.$crcd_label.'</h3></div>';

                                            while ( have_rows( 'completed_release_control_dates' ) ) : the_row();
                                                $html .= '<div class="date">' . get_sub_field( 'date' ) . ' '. get_sub_field( 'details' ) . '</div>';
                                            endwhile;
                                        $html .= '</div>';
                                    $html .= '</div>';
                                endif;

                                if ( have_rows( 'completed_release_dates' ) ) :
                                    $html .= '<div class="completed-release-dates-container">';
                                        $html .= '<div class="completed-release-dates border-wrap">';
                                            $html .= '<div class="sub-head"><h3>'.$crd_label.'</h3></div>';

                                            while ( have_rows( 'completed_release_dates' ) ) : the_row();
                                                $html .= '<div class="date">' . get_sub_field( 'date' ) .' '. get_sub_field( 'details' ) . '</div>';
                                            endwhile;
                                        $html .= '</div>';
                                    $html .= '</div>';
                                endif;
                            endwhile;

                        $html .= '</div>';
                    endif;
                $html .= '</div>';
            $html .= '</div>';
            
        $html .= '</div>';

        return $html;
    }

}

new Boomi_Stats_Releases();
