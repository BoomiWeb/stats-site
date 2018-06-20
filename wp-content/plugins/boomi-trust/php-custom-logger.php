<?php

if ( ! class_exists( 'PHP_Custom_Logger' ) ) :

    final class PHP_Custom_Logger {

        protected $args = '';

        public function __construct( $args = '' ) {
            $upload_dir = wp_upload_dir();
            
            $default_args = array(
                'path' => $upload_dir['path'],
                'file' => 'php-custom-log.txt',
                'url' => $upload_dir['url'],
            );
            $this->args = wp_parse_args( $args, $default_args );
            
            add_action('admin_menu', array($this, 'add_log_page'));
        }

        public function log( $message = '' ) {
            $this->write_to_log( $message );
        }

        protected function write_to_log( $message = '' ) {
            $file = $args['path'] . '/' . $args['file'];
            //$file_url = $args['url'] . '/' . $args['file'];
            
            // in case uploads folder changes.
            //update_option('_php_logger_file', $file_url);
            
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
            
            $html .= get_option('_php_logger_file', $args['url']);
            
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

    function php_custom_logger( $args = '' ) {
        return new PHP_Custom_Logger( $args );
    }

    // Global for backwards compatibility.
    $GLOBALS['php_custom_logger'] = php_custom_logger( $args = '' );

endif;
