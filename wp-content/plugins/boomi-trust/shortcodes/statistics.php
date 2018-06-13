<?php

function boomi_trust_statistics( $atts ) {
    $atts = shortcode_atts( array(), $atts, 'trust-statistics' );

    $html = '';
    $statistic_types = array(
        'processes' => array(
            'title' => 'Integrations Processed',
            'description' => 'Total integrations that have been processed in the last 30 days.',
        ),
        'atoms' => array(
            'title' => 'Atoms Deployed',
            'description' => 'Total Atoms deployed in the Cloud and on-premise.',
        ),
        'total-mappings' => array(
            'title' => 'Mappings Indexed',
            'description' => 'Total number of mappings indexed by Boomi Suggest.',
        ),
        'total-functions' => array(
            'title' => 'Functions Indexed',
            'description' => 'Total number of functions indexed by Boomi Suggest.',
        ),
    );

    $html .= '<div class="row row-eq-height statistics">';

    if ( ! empty( get_option( '_trust_statistic_updated' ) ) ) :

        foreach ( $statistic_types as $slug => $arr ) :

            $html .= '<div class="col-xs-12 col-sm-6 col-md-3">';
                $html .= '<div class="row stat-wrap">';
                    $html .= '<div class="stat-number">';
                        $html .= number_format( get_option( '_trust_statistic_' . $slug, 0 ) );
                    $html .= '</div>';

                    $html .= '<div class="stat-title">';
                        $html .= $arr['title'];
                    $html .= '</div>';

                    $html .= '<div class="stat-description">';
                        $html .= $arr['description'];
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

            endforeach;

        endif;

    $html .= '</div>';

    return $html;
}
add_shortcode( 'trust-statistics', 'boomi_trust_statistics' );
