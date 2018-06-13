<?php

class Boomi_Trust_Notifications_Meta_Box {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'load-post.php', array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
        }

    }

    /**
     * init_metabox function.
     *
     * @access public
     * @return void
     */
    public function init_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
        // add_action( 'edit_form_after_title', array($this, 'place_metabox'));
    }

    /**
     * add_metabox function.
     *
     * @access public
     * @return void
     */
    public function add_metabox() {
        add_meta_box(
            'notifications-metabox',
            __( 'Notifications Metabox', 'boomi-trust' ),
            array( $this, 'render_metabox' ),
            'notifications',
            'after_title',
            'high'
        );

    }

    /**
     * render_metabox function.
     *
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_metabox( $post ) {
        wp_nonce_field( 'update_notifications_meta', 'notifications_metabox' );

        $html = '';
        $terms = wp_get_post_terms( $post->ID, 'notificationtype', array( 'fields' => 'names' ) );

        $html .= '<div class="boomi-trust-metabox">';

            $html .= '<div class="mb-row">';
                $html .= '<div class="mb-label">';
                    $html .= '<label for="type">Notification Type</label>';
                $html .= '</div>';

                $html .= '<div class="mb-input">';
                    $html .= $this->type( $terms[0] );
                $html .= '</div>';
            $html .= '</div>';

        $html .= '</div>';

        echo $html;
    }

    /**
     * type function.
     *
     * @access protected
     * @param string $selected (default: '')
     * @return void
     */
    protected function type( $selected = '' ) {
        $html = '';
        $terms = get_terms(
            array(
                'taxonomy' => 'notificationtype',
                'hide_empty' => false,
            )
        );

        $html .= '<select name="notification_type" id="type">';
            $html .= '<option value="0">Select a Type</option>';

        foreach ( $terms as $term ) :
            $html .= '<option value="' . $term->name . '" ' . selected( $selected, $term->name, false ) . '>' . $term->name . '</option>';
            endforeach;

        $html .= '</select>';

        return $html;
    }

    /**
     * save_metabox function.
     *
     * @access public
     * @param mixed $post_id
     * @param mixed $post
     * @return void
     */
    public function save_metabox( $post_id, $post ) {
        // Add nonce for security and authentication.
        $nonce_name   = isset( $_POST['notifications_metabox'] ) ? $_POST['notifications_metabox'] : '';
        $nonce_action = 'update_notifications_meta';

        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }

        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        wp_set_object_terms( $post_id, $_POST['notification_type'], 'notificationtype' );
    }

    /**
     * place_metabox function.
     *
     * @access public
     * @return void
     */
    /*
    public function place_metabox() {
        global $post, $wp_meta_boxes;

        do_meta_boxes( get_current_screen(), 'after_title', $post );
    }
    */

}

new Boomi_Trust_Notifications_Meta_Box();

