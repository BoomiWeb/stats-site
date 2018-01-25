<?php

class Boomi_Trust_Cloud_Statuses_Meta_Box {

    /**
     * __construct function.
     * 
     * @access public
     * @return void
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
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
        add_action( 'add_meta_boxes', array( $this, 'add_metabox'  )        );
        add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );
        add_action( 'edit_form_after_title', array($this, 'place_metabox'));
    }

    /**
     * add_metabox function.
     * 
     * @access public
     * @return void
     */
    public function add_metabox() {
        add_meta_box(
            'cloud-statuses-metabox',
            __( 'Statuses Metabox', 'boomi-trust' ),
            array( $this, 'render_metabox' ),
            'cloudstatuses',
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
        wp_nonce_field('update_cloud_status_meta', 'cloud_status_metabox' );
       
        $html='';
        
        $html.='<div class="boomi-trust-metabox">';
        
	        $html.='<div class="mb-row">';
	        	$html.='<div class="mb-label">';
	        		$html.='<label for="services">Service Affected</label>';
	        	$html.='</div>';
	
	        	$html.='<div class="mb-input">';
	        		$html.=$this->posts_dropdown('services', 'services', 'Service', get_post_meta($post->ID, '_service', true));
	        	$html.='</div>';
	        $html.='</div>';
	
	        $html.='<div class="mb-row">';
	        	$html.='<div class="mb-label">';
	        		$html.='<label for="">Status Type</label>';
	        	$html.='</div>';
	
	        	$html.='<div class="mb-input">';
	        		$html.=$this->posts_dropdown('statustypes', 'statustypes', 'Status Type', get_post_meta($post->ID, '_statustype', true));
	        	$html.='</div>';
	        $html.='</div>';
        
        $html.='</div>';
        
        echo $html;
    }
    
    /**
     * posts_dropdown function.
     * 
     * @access protected
     * @param string $post_type (default: '')
     * @param string $name (default: '')
     * @param string $label (default: 'Item')
     * @param string $selected (default: '')
     * @return void
     */
    protected function posts_dropdown($post_type='', $name='', $label='Item', $selected='') {
	    $html='';
	    $posts_ids=get_posts(array(
		    'posts_per_page' => -1,
		    'post_type' => $post_type,
		    'fields' => 'ids',
	    ));
	    
	    $html.='<select name="cloud_statuses['.sanitize_key($name).']" id="'.$name.'">';
	    	$html.='<option value="0">Select a '.$label.'</option>';
	    	
	    	foreach ($posts_ids as $post_id) :
	    		$html.='<option value="'.$post_id.'" '.selected($selected, $post_id, false).'>'.get_the_title($post_id).'</option>';
	    	endforeach;
	    	
	    $html.='</select>';
	    
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
        $nonce_name   = isset( $_POST['cloud_status_metabox'] ) ? $_POST['cloud_status_metabox'] : '';
        $nonce_action = 'update_cloud_status_meta';
 
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
        
		update_post_meta($post_id, '_service', $_POST['cloud_statuses']['services']);
		update_post_meta($post_id, '_statustype', $_POST['cloud_statuses']['statustypes']);		
    }
    
    /**
     * place_metabox function.
     * 
     * @access public
     * @return void
     */
    public function place_metabox() {
    	global $post, $wp_meta_boxes;

		do_meta_boxes( get_current_screen(), 'after_title', $post );	    
    }
    
}
 
new Boomi_Trust_Cloud_Statuses_Meta_Box();
?>