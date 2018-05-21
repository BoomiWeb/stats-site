<?php

/**
 * pcl_update_120_taxonomies function.
 *
 * @access public
 * @return void
 */
function pcl_update_120_taxonomies() {
    $taxonomies = array(
        array(
            'slug' => 'pctype',
            'label' => 'type',
            'label_plural' => 'types',
        ),
    );

    add_option( 'pickle_calendar_taxonomies', $taxonomies );
}

