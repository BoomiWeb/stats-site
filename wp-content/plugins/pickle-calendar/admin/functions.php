<?php

class Pickle_Calendar_Admin_Functions {

    public function __construct() {}

    /**
     * check_remove_taxonomy function.
     *
     * @access public
     * @return void
     */
    public function check_remove_taxonomy() {
        // remove single taxonomy //
        if ( ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' ) && isset( $_GET['slug'] ) ) :
            $this->remove_taxonomy( $_GET['slug'] );
        endif;

        // bulk actions //
        if ( isset( $_POST['action'] ) ) :
            switch ( $_POST['action'] ) :
                case 'deleteall':
                    $this->taxonomy_bulk_delete( $_POST['pickle_calendar_taxonomy'] );
                    break;
            endswitch;
        endif;
    }

    /**
     * remove_taxonomy function.
     *
     * @access public
     * @param string $tax_slug (default: '')
     * @return void
     */
    public function remove_taxonomy( $tax_slug = '' ) {
        $taxonomies = get_option( 'pickle_calendar_taxonomies' );

        foreach ( $taxonomies as $key => $taxonomy ) :
            if ( $taxonomy['slug'] == $tax_slug ) :
                unset( $taxonomies[ $key ] );

                break;
            endif;
        endforeach;

        $taxonomies = array_values( $taxonomies );

        update_option( 'pickle_calendar_taxonomies', $taxonomies );

        picklecalendar()->update_settings();
    }

    /**
     * taxonomy_bulk_delete function.
     *
     * @access protected
     * @param string $taxonomies (default: '')
     * @return void
     */
    protected function taxonomy_bulk_delete( $taxonomies = '' ) {
        if ( empty( $taxonomies ) ) {
            return;
        }

        foreach ( $taxonomies as $taxonomy ) :
            $this->remove_taxonomy( $taxonomy );
        endforeach;
    }

}
