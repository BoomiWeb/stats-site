<?php
/**
 * Pickle Calendar functions
 *
 * @package PickleCalendar
 * @since   1.0.0
 */

/**
 * Pickle Calendar get taxonomy function.
 *
 * @access public
 * @param string $slug (default: '').
 * @return array
 */
function pickle_calendar_get_taxonomy( $slug = '' ) {
    $default = array(
        'slug' => '',
        'label' => '',
        'label_plural' => '',
        'display' => 1,
        'display_type' => 'checkbox',
        'hide_all_tab' => 0,
    );
    $tax = array();

    foreach ( picklecalendar()->settings['taxonomies'] as $taxonomy ) :
        if ( $taxonomy['slug'] == $slug ) :
            $tax = $taxonomy;
            break;
        endif;
    endforeach;

    $tax = picklecalendar()->parse_args( $tax, $default );

    return $tax;
}
