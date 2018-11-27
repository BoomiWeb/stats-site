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

        $html .= '<div class="boomi-stats-notifications">';
            $html .= '<div class="upcoming-releases">';
                $html .= '<div class="upcoming-releases-header">';
                    $html .= '<h2>Upcoming Releases</h2>';
                $html .= '</div>';

                $html .= '<div class="row releases">';
        if ( have_rows( 'upcoming_releases' ) ) :
            while ( have_rows( 'upcoming_releases' ) ) :
                the_row();

                if ( have_rows( 'upcoming_release_control_dates' ) ) :
                    $html .= '<div class="col-xs-12 col-sm-6">';
                        $html .= '<div class="release-control-dates border-wrap">';
                            $html .= '<div class="sub-head"><h3>Upcoming Release Control Dates</h3></div>';

                    while ( have_rows( 'upcoming_release_control_dates' ) ) :
                        the_row();
                            $html .= '<div class="date">' . get_sub_field( 'date' ) . '</div>';
                            endwhile;
                        $html .= '</div>';
                                $html .= '</div>';
                            endif;

                if ( have_rows( 'upcoming_release_dates' ) ) :
                    $html .= '<div class="col-xs-12 col-sm-6 ">';
                        $html .= '<div class="release-dates border-wrap">';
                            $html .= '<div class="sub-head"><h3>Upcoming Release Dates</h3></div>';

                    while ( have_rows( 'upcoming_release_dates' ) ) :
                        the_row();
                            $html .= '<div class="date">' . get_sub_field( 'date' ) . '</div>';
                            endwhile;
                        $html .= '</div>';
                                $html .= '</div>';
                            endif;

            endwhile;
                    endif;
                $html .= '</div>';

            $html .= '</div>';

            $html .= '<div class="release-archive">';
                $html .= '<div class="release-archive-header">';
                    $html .= '<h2>Release Archive</h2>';
                $html .= '</div>';

                $html .= '<div class="row releases">';
        if ( have_rows( 'release_archive' ) ) :
            while ( have_rows( 'release_archive' ) ) :
                the_row();

                if ( have_rows( 'completed_release_control_dates' ) ) :
                    $html .= '<div class="col-xs-12 col-sm-6">';
                        $html .= '<div class="completed-release-control-dates border-wrap">';
                            $html .= '<div class="sub-head"><h3>Completed Release Control Dates</h3></div>';

                    while ( have_rows( 'completed_release_control_dates' ) ) :
                        the_row();
                            $html .= '<div class="date">' . get_sub_field( 'date' ) . '</div>';
                            endwhile;
                        $html .= '</div>';
                                $html .= '</div>';
                            endif;

                if ( have_rows( 'completed_release_dates' ) ) :
                    $html .= '<div class="col-xs-12 col-sm-6">';
                        $html .= '<div class="completed-release-dates border-wrap">';
                            $html .= '<div class="sub-head"><h3>Completed Release Dates</h3></div>';

                    while ( have_rows( 'completed_release_dates' ) ) :
                        the_row();
                            $html .= '<div class="date">' . get_sub_field( 'date' ) . '</div>';
                            endwhile;
                        $html .= '</div>';
                                $html .= '</div>';
                            endif;

            endwhile;
                    endif;
                $html .= '</div>';
            $html .= '</div>';
            $html.='<p>Dell Boomi reserves the right to adjust these dates as needed.</p>';
        $html .= '</div>';

        echo $html;
    }

}

new Boomi_Stats_Releases();
