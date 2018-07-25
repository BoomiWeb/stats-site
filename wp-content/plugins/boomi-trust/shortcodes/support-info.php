<?php

/**
 * Boomit stats support info.
 * 
 * @access public
 * @param mixed $atts array.
 * @return html
 */
function boomi_stats_support_info( $atts ) {
    $atts = shortcode_atts( array(), $atts, 'stats-support-info' );
    $html = '';

    $html .= '<div class="row support-info">';

        $html .= '<div class="col-xs-12">';
            $html .= '<div class="report-text">To report <span>Severity 1</span> issues, please call:</div>';
            $html .= '<div class="phone-number"><a href="tel:1-866-407-6599">1-866-407-6599</a> (Toll free in US)</div>';
            $html .= '<div class="phone-number">a href="tel:1-503-470-5056">1-503-470-5056</a> (International)</div>';
        $html .= '</div>';

    $html .= '</div>';

    return $html;
}
add_shortcode( 'stats-support-info', 'boomi_stats_support_info' );
