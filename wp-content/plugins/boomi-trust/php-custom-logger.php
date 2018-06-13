<?php
    
if (!class_exists('PHP_Custom_Logger')) :
    
    final class PHP_Custom_Logger {
            
        protected $args = '';
    
        public function __construct($args = '') {
            $default_args = array(
                'path' => plugin_dir_path(__FILE__), 
                'filename' => 'php-custom-log',
                'file_extension' => '.txt';
            );
            $this->args = wp_parse_args($args, $default_args);            
        }
    
        public function log( $message = '' ) {
            $this->write_to_log( $message );
        }
    
        protected function write_to_log( $message = '' ) {
            $time = date( 'm-d-y H:i' );
            $file = $this->args['path'] . $this->args['filename'] . $args['file_extension'];
    
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
    
    }
    
endif;

/**
 * Main function.
 *
 * @access public
 * @return class
 */
if (!function_exists('php_custom_logger')) :

    function php_custom_logger($args = '') {
        return new PHP_Custom_Logger($args);
    }

    // Global for backwards compatibility.
    $GLOBALS['php_custom_logger'] = php_custom_logger( $args = '' );

endif;