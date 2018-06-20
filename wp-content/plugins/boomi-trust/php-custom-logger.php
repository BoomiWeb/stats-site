<?php

if ( ! class_exists( 'PHP_Custom_Logger' ) ) :

    final class PHP_Custom_Logger {

        protected $args = '';

        public function __construct() {
            $upload_dir = wp_upload_dir();
            
            $this->args = array(
                'path' => $upload_dir['path'],
                'file' => 'php-custom-log.txt',
                'url' => $upload_dir['url'],
            );
            
            add_action('admin_menu', array($this, 'add_log_page'));
        }

        public function log( $message = '' ) {
            $this->write_to_log( $message );
        }

        protected function write_to_log( $message = '' ) {
            $file = $this->args['path'] . '/' . $this->args['file'];

            $time = date( 'm-d-y H:i' );

            $log_message = "\n#$time\n";

            if ( is_array( $message ) ) :
                $log_message .= print_r( $message, true );
            else :
                $log_message .= $message;
            endif;

            $open = fopen( $file, 'a' );
            $write = fputs( $open, $log_message );

            fclose( $open );
        }
        
        public function add_log_page() {
            add_management_page('PHP Custom Logger', 'PHP Custom Logger', 'manage_options', 'php-custom-logger', array($this, 'admin_page'));
        }
        
        public function admin_page() {
            $html = '';
            $file = $this->args['path'] . '/' . $this->args['file'];
            $url = $this->args['url'] . '/' . $this->args['file'];
            $contents = file_get_contents( $file ); // get the contents.
            
            $html .= '<a href="'.$url.'" target="_blank">'.$url.'</a>';
            
            $html .= '<div class="php-logger-output">';
                $html .= nl2br($contents);
            $html .= '</div>';
            
            echo $html;
        }

    }

endif;

/**
 * Main function.
 *
 * @access public
 * @return class
 */
if ( ! function_exists( 'php_custom_logger' ) ) :

    function php_custom_logger() {
        return new PHP_Custom_Logger();
    }

    // Global for backwards compatibility.
    $GLOBALS['php_custom_logger'] = php_custom_logger( $args = '' );

endif;
