<?php

function boomi_trust_daily_integrations( $atts ) {
    $atts = shortcode_atts( array(), $atts, 'trust-daily-integrations' );

    $html = '';
    $process_count = get_option( '_trust_process_count', '' );

    if ( empty( $process_count ) ) {
        return;
    }

    $html .= '<div class="process-count">';

        $html .= '<div class="title">';
            $html .= 'Daily Integration Count';
        $html .= '</div>';

        $html .= '<div class="col-xs-12">';
            $html .= '<div class="row header">';
                $html .= '<div class="col-xs-5 col-md-4 process-date">';
                    $html .= 'Date';
                $html .= '</div>';

                $html .= '<div class="col-xs-5 count">';
                    $html .= 'Count';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';

    foreach ( $process_count as $arr ) :
        $html .= '<div class="col-xs-12 process-row">';
            $html .= '<div class="row">';
                $html .= '<div class="col-xs-5 col-md-4 process-date">';
                    $html .= date( 'M j, Y', strtotime( $arr['process_date'] ) );
                $html .= '</div>';

                $html .= '<div class="col-xs-5 count">';
                    $html .= number_format( $arr['count'] );
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';
        endforeach;

    $html .= '</div>';

    return $html;
}
add_shortcode( 'trust-daily-integrations', 'boomi_trust_daily_integrations' );
