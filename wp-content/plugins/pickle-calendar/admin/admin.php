<?php

/**
 * Pickle_Calendar_Admin class.
 */
class Pickle_Calendar_Admin {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_styles' ) );
        add_action( 'admin_init', array( $this, 'process_events_export' ) );
        add_action( 'admin_init', array( $this, 'process_events_import' ) );
        add_action( 'admin_init', array( $this, 'process_settings_export' ) );
        add_action( 'admin_init', array( $this, 'process_settings_import' ) );
        add_action( 'admin_init', array( $this, 'update_settings' ) );
        add_action( 'admin_init', array( $this, 'update_taxonomy' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * admin_scripts_styles function.
     *
     * @access public
     * @return void
     */
    public function admin_scripts_styles() {
        wp_enqueue_script( 'pickle-calendar-admin-script', PICKLE_CALENDAR_URL . 'admin/js/admin.js', array( 'jquery' ), picklecalendar()->version, true );

        wp_enqueue_style( 'pickle-calendar-admin-css', PICKLE_CALENDAR_URL . 'admin/css/admin.css', '', picklecalendar()->version );
    }

    /**
     * admin_menu function.
     *
     * @access public
     * @return void
     */
    public function admin_menu() {
        add_options_page( 'Pickle Calendar', 'Pickle Calendar', 'manage_options', 'pickle-calendar', array( $this, 'admin_page' ) );
    }

    /**
     * admin_page function.
     *
     * @access public
     * @return void
     */
    public function admin_page() {
        $html = null;
        $tabs = array(
            'settings' => 'Settings',
            'taxonomies' => 'Categories',
        );
        $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'settings';

        $html .= '<div class="wrap pickle-calendar-admin">';
            $html .= '<h1>Pickle Calendar</h1>';

            $html .= '<h2 class="nav-tab-wrapper">';
        foreach ( $tabs as $key => $name ) :
            if ( $active_tab == $key ) :
                $class = 'nav-tab-active';
            else :
                $class = null;
            endif;

            $html .= '<a href="?page=pickle-calendar&tab=' . $key . '" class="nav-tab ' . $class . '">' . $name . '</a>';
                endforeach;
            $html .= '</h2>';

        switch ( $active_tab ) :
            case 'taxonomies':
                if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) :
                    $html .= $this->get_admin_page( 'taxonomies-single' );
                    else :
                        $html .= $this->get_admin_page( 'taxonomies' );
                    endif;
                break;
            default:
                $html .= $this->get_admin_page( 'settings' );
            endswitch;

        $html .= '</div>';

        echo $html;
    }

    /**
     * update_settings function.
     *
     * @access public
     * @return void
     */
    public function update_settings() {
        if ( ! isset( $_POST['pickle_calendar_admin'] ) || ! wp_verify_nonce( $_POST['pickle_calendar_admin'], 'update_settings' ) ) {
            return false;
        }

        $new_settings = picklecalendar()->parse_args( $_POST['settings'], picklecalendar()->settings );

        // for checkboxes //
        foreach ( $new_settings as $key => $value ) :
            if ( is_array( $value ) ) :
                foreach ( $value as $sub_key => $sub_value ) :
                    if ( ! isset( $_POST['settings'][ $key ][ $sub_key ] ) ) :
                        $new_settings[ $key ][ $sub_key ] = 0;
                    endif;
                endforeach;
            else :
                if ( ! isset( $_POST['settings'][ $key ] ) ) :
                    $new_settings[ $key ] = 0;
                endif;
            endif;
        endforeach;

        update_option( 'pickle_calendar_settings', $new_settings );

        wp_redirect( site_url( $_POST['_wp_http_referer'] ) );
        exit;
    }

    /**
     * update_taxonomy function.
     *
     * @access public
     * @return void
     */
    public function update_taxonomy() {
        if ( ! isset( $_POST['pickle_calendar_admin'] ) || ! wp_verify_nonce( $_POST['pickle_calendar_admin'], 'update_taxonomy' ) ) {
            return false;
        }

        $taxonomies = get_option( 'pickle_calendar_taxonomies' );
        $exists = false;

        // we added a check if tax exists, fixes a bug and provides some built in clean up.
        if ( ! empty( $_POST['tax_details']['slug'] ) ) :
            // search for existing, update or add.
            foreach ( $taxonomies as $key => $tax_details ) :
                if ( $_POST['tax_details']['slug'] == $tax_details['slug'] ) :
                    $taxonomies[ $key ] = $_POST['tax_details'];
                    $exists = true;
                endif;
            endforeach;

            // remove dups.
            $taxonomies = array_map( 'unserialize', array_unique( array_map( 'serialize', $taxonomies ) ) );

            // update if not exists.
            if ( ! $exists ) {
                $taxonomies[] = $_POST['tax_details'];
            }
        endif;

        update_option( 'pickle_calendar_taxonomies', $taxonomies );

        picklecalendar()->update_settings();

        wp_redirect( admin_url( 'options-general.php?page=pickle-calendar&tab=taxonomies&action=edit&slug=' . $_POST['tax_details']['slug'] ) );
        exit;
    }

    /**
     * process_settings_export function.
     *
     * @access public
     * @return void
     */
    public function process_settings_export() {
        if ( empty( $_POST['pc_action'] ) || $_POST['pc_action'] != 'export_settings' ) {
            return;
        }

        if ( ! isset( $_POST['pickle_calendar_export_nonce'] ) || ! wp_verify_nonce( $_POST['pickle_calendar_export_nonce'], 'pickle_calendar_export_nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $settings = get_option( 'pickle_calendar_settings' );

        ignore_user_abort( true );

        nocache_headers();

        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=pickle-calendar-settings-export-' . date( 'm-d-Y' ) . '.json' );
        header( 'Expires: 0' );

        echo json_encode( $settings );

        exit;
    }

    /**
     * process_settings_import function.
     *
     * @access public
     * @return void
     */
    public function process_settings_import() {
        if ( empty( $_POST['pc_action'] ) || $_POST['pc_action'] != 'import_settings' ) {
            return;
        }

        if ( ! isset( $_POST['pickle_calendar_import_nonce'] ) || ! wp_verify_nonce( $_POST['pickle_calendar_import_nonce'], 'pickle_calendar_import_nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $extension = end( explode( '.', $_FILES['import_file']['name'] ) );

        if ( $extension != 'json' ) {
            wp_die( __( 'Please upload a valid .json file' ) );
        }

        $import_file = $_FILES['import_file']['tmp_name'];

        if ( empty( $import_file ) ) {
            wp_die( __( 'Please upload a file to import' ) );
        }

        // Retrieve the settings from the file and convert the json object to an array.
        $settings = json_decode( file_get_contents( $import_file ), true );

        update_option( 'pickle_calendar_settings', $settings );

        wp_safe_redirect( admin_url( 'options-general.php?page=pickle-calendar' ) );

        exit;
    }

    /**
     * process_events_export function.
     *
     * @access public
     * @return void
     */
    public function process_events_export() {
        if ( empty( $_POST['pc_action'] ) || $_POST['pc_action'] != 'export_events' ) {
            return;
        }

        if ( ! isset( $_POST['pickle_calendar_export_events_nonce'] ) || ! wp_verify_nonce( $_POST['pickle_calendar_export_events_nonce'], 'pickle_calendar_export_events_nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        picklecalendar()->import_export_events->export();
    }

    public function process_events_import() {
        if ( empty( $_POST['pc_action'] ) || $_POST['pc_action'] != 'import_events' ) {
            return;
        }

        if ( ! isset( $_POST['pickle_calendar_import_events_nonce'] ) || ! wp_verify_nonce( $_POST['pickle_calendar_import_events_nonce'], 'pickle_calendar_import_events_nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $extension = end( explode( '.', $_FILES['import_file']['name'] ) );

        if ( $extension != 'json' ) {
            wp_die( __( 'Please upload a valid .json file' ) );
        }

        $import_file = $_FILES['import_file']['tmp_name'];

        if ( empty( $import_file ) ) {
            wp_die( __( 'Please upload a file to import' ) );
        }

        // Retrieve the settings from the file.
        $events = json_decode( file_get_contents( $import_file ) );

        $imported = picklecalendar()->import_export_events->import( $events );

        if ( $imported ) :
            $imported_url_var = 1;
        else :
            $imported_url_var = 0;
        endif;

        wp_safe_redirect( admin_url( 'options-general.php?page=pickle-calendar&import-events=' . $imported_url_var ) );

        exit;
    }

    public function admin_notices() {
        $html = '';
        $screen = get_current_screen();

        if ( $screen->id !== 'settings_page_pickle-calendar' ) {
            return;
        }

        if ( isset( $_GET['import-events'] ) ) :

            if ( $_GET['import-events'] ) :

                $html .= '<div class="notice notice-success is-dismissible">';
                    $html .= '<p>' . __( 'Events successfully imported.', 'pickle-calendar' ) . '</p>';
                $html .= '</div>';

            else :

                $html .= '<div class="notice notice-error is-dismissible">';
                    $html .= '<p>' . __( 'Events not imported.', 'pickle-calendar' ) . '</p>';
                $html .= '</div>';

            endif;

        endif;

        echo $html;
    }

    public function get_admin_page( $template_name = false ) {
        if ( ! $template_name ) {
            return false;
        }

        ob_start();

        do_action( 'pickle_calendar_before_admin_' . $template_name );

        include( PICKLE_CALENDAR_PATH . 'admin/pages/' . $template_name . '.php' );

        do_action( 'pickle_calendar_after_admin_' . $template_name );

        $html = ob_get_contents();

        ob_end_clean();

        return $html;
    }

}

new Pickle_Calendar_Admin();
