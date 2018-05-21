<?php

class Pickle_Calendar_Post_Types {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );

        add_filter( 'post_updated_messages', array( __CLASS__, 'updated_messages' ) );

        add_action( 'wp_print_scripts', array( __CLASS__, 'pcevents_dequeue_wp_seo_scripts' ), 100 );
        add_action( 'add_meta_boxes', array( __CLASS__, 'pcevents_remove_wp_seo_meta_box' ), 100 );
        // potential rewrite flush rules hook/action here
    }

    public static function register_post_types() {
        if ( post_type_exists( 'pcevent' ) ) {
            return;
        }

        register_post_type(
            'pcevent', array(
                'labels'            => array(
                    'name'                => __( picklecalendar()->settings['cpt_plural'], 'pickle-calendar' ),
                    'singular_name'       => __( picklecalendar()->settings['cpt_single'], 'pickle-calendar' ),
                    'all_items'           => __( 'All ' . picklecalendar()->settings['cpt_plural'], 'pickle-calendar' ),
                    'new_item'            => __( 'New ' . picklecalendar()->settings['cpt_single'], 'pickle-calendar' ),
                    'add_new'             => __( 'Add New', 'pickle-calendar' ),
                    'add_new_item'        => __( 'Add New ' . picklecalendar()->settings['cpt_single'], 'pickle-calendar' ),
                    'edit_item'           => __( 'Edit ' . picklecalendar()->settings['cpt_single'], 'pickle-calendar' ),
                    'view_item'           => __( 'View ' . picklecalendar()->settings['cpt_single'], 'pickle-calendar' ),
                    'search_items'        => __( 'Search ' . picklecalendar()->settings['cpt_plural'], 'pickle-calendar' ),
                    'not_found'           => __( 'No ' . picklecalendar()->settings['cpt_plural'] . ' found', 'pickle-calendar' ),
                    'not_found_in_trash'  => __( 'No ' . picklecalendar()->settings['cpt_plural'] . ' found in trash', 'pickle-calendar' ),
                    'parent_item_colon'   => __( 'Parent ' . picklecalendar()->settings['cpt_single'], 'pickle-calendar' ),
                    'menu_name'           => __( picklecalendar()->settings['adminlabel'], 'pickle-calendar' ),
                ),
                'public'            => true,
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_nav_menus' => true,
                // 'supports'          => array( 'title', 'editor', 'thumbnail' ),
                'supports'          => array( 'title' ),
                'has_archive'       => true,
                'rewrite'           => true,
                'query_var'         => true,
                'menu_icon'         => 'dashicons-calendar-alt',
                'show_in_rest'      => true,
                'rest_base'         => 'pcevent',
                'rest_controller_class' => 'WP_REST_Posts_Controller',
            )
        );

    }

    public static function register_taxonomies() {
        foreach ( picklecalendar()->settings['taxonomies'] as $taxonomy ) :
            self::generate_taxonomy( $taxonomy );
        endforeach;
    }

    protected static function generate_taxonomy( $taxonomy = array() ) {
        if ( empty( $taxonomy ) ) {
            return;
        }

        $defaults = array(
            'post_type' => 'pcevent',
            'textdomain' => 'pickle-calendar',
            'slug' => '',
        );
        $args = wp_parse_args( $taxonomy, $defaults );

        if ( empty( $args['slug'] ) ) {
            return;
        }

        if ( empty( $args['label'] ) ) :
            preg_replace( '/_|-/', ' ', strtolower( $args['slug'] ) );
        endif;

        if ( empty( $args['label_plural'] ) ) :
            preg_replace( '/_|-/', ' ', strtolower( $args['slug'] ) );
        endif;

        extract( $args );

        register_taxonomy(
            $slug, array( $post_type ), array(
                'hierarchical'      => true,
                'public'            => true,
                'show_in_nav_menus' => true,
                'show_ui'           => true,
                'show_admin_column' => false,
                'query_var'         => true,
                'rewrite'           => true,
                'capabilities'      => array(
                    'manage_terms'  => 'edit_posts',
                    'edit_terms'    => 'edit_posts',
                    'delete_terms'  => 'edit_posts',
                    'assign_terms'  => 'edit_posts',
                ),
                'labels'            => array(
                    'name'                       => __( ucwords( $label_plural ), $textdomain ),
                    'singular_name'              => _x( ucwords( $label ), 'taxonomy general name', $textdomain ),
                    'search_items'               => __( 'Search ' . ucwords( $label_plural ), $textdomain ),
                    'popular_items'              => __( 'Popular ' . ucwords( $label_plural ), $textdomain ),
                    'all_items'                  => __( 'All ' . ucwords( $label_plural ), $textdomain ),
                    'parent_item'                => __( 'Parent ' . ucwords( $label ), $textdomain ),
                    'parent_item_colon'          => __( 'Parent ' . ucwords( $label ) . ':', $textdomain ),
                    'edit_item'                  => __( 'Edit ' . ucwords( $label ), $textdomain ),
                    'update_item'                => __( 'Update ' . ucwords( $label ), $textdomain ),
                    'add_new_item'               => __( 'New ' . ucwords( $label ), $textdomain ),
                    'new_item_name'              => __( 'New ' . ucwords( $label ), $textdomain ),
                    'separate_items_with_commas' => __( 'Separate ' . ucwords( $label_plural ) . ' with commas', $textdomain ),
                    'add_or_remove_items'        => __( 'Add or remove ' . ucwords( $label_plural ), $textdomain ),
                    'choose_from_most_used'      => __( 'Choose from the most used ' . ucwords( $label_plural ), $textdomain ),
                    'not_found'                  => __( 'No ' . ucwords( $label_plural ) . ' found.', $textdomain ),
                    'menu_name'                  => __( ucwords( $label_plural ), $textdomain ),
                ),
                'show_in_rest'      => true,
                'rest_base'         => $slug,
                'rest_controller_class' => 'WP_REST_Terms_Controller',
            )
        );
    }

    public static function updated_messages( $messages ) {
        global $post;

        $permalink = get_permalink( $post );

        $messages['pcevent'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf( __( picklecalendar()->settings['cpt_single'] . ' updated. <a target="_blank" href="%s">View ' . picklecalendar()->settings['cpt_single'] . '</a>', 'pickle-calendar' ), esc_url( $permalink ) ),
            2 => __( 'Custom field updated.', 'pickle-calendar' ),
            3 => __( 'Custom field deleted.', 'pickle-calendar' ),
            4 => __( picklecalendar()->settings['cpt_single'] . ' updated.', 'pickle-calendar' ),
            /* translators: %s: date and time of the revision */
            5 => isset( $_GET['revision'] ) ? sprintf( __( picklecalendar()->settings['cpt_single'] . ' restored to revision from %s', 'pickle-calendar' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => sprintf( __( picklecalendar()->settings['cpt_single'] . ' published. <a href="%s">View ' . picklecalendar()->settings['cpt_single'] . '</a>', 'pickle-calendar' ), esc_url( $permalink ) ),
            7 => __( picklecalendar()->settings['cpt_single'] . ' saved.', 'pickle-calendar' ),
            8 => sprintf( __( picklecalendar()->settings['cpt_single'] . ' submitted. <a target="_blank" href="%s">Preview ' . picklecalendar()->settings['cpt_single'] . '</a>', 'pickle-calendar' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
            9 => sprintf(
                __( picklecalendar()->settings['cpt_single'] . ' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . picklecalendar()->settings['cpt_single'] . '</a>', 'pickle-calendar' ),
                // translators: Publish box date format, see http://php.net/date
                date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink )
            ),
            10 => sprintf( __( picklecalendar()->settings['cpt_single'] . ' draft updated. <a target="_blank" href="%s">Preview ' . picklecalendar()->settings['cpt_single'] . '</a>', 'pickle-calendar' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
        );

        return $messages;
    }

    public static function pcevents_dequeue_wp_seo_scripts() {
        global $post;

        if ( isset( $post->post_type ) && $post->post_type != 'pcevents' ) {
            return;
        }

        wp_dequeue_script( 'yoast-seo-post-scraper' );
    }

    public static function pcevents_remove_wp_seo_meta_box() {
        global $post;

        if ( $post->post_type != 'pcevents' ) {
            return;
        }

        remove_meta_box( 'wpseo_meta', 'pcevent', 'normal' );
    }

    public static function flush_rewrite_rules() {
        flush_rewrite_rules();
    }

}

Pickle_Calendar_Post_Types::init();
