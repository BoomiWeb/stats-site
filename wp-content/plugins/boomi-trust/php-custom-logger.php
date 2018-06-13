<?php
    
if (!class_exists('PHP_Custom_Logger')) :
    
    final class PHP_Custom_Logger {
    
        protected $filename = 'php-custom-log.txt';
    
        public function __construct() {
            // do nothing?
        }
    
        public function log( $message = '' ) {
            $this->write_to_log( $message );
        }
    
        protected function write_to_log( $message = '' ) {
            $time = date( 'm-d-y H:i' );
            $file = BSLI_PATH . $this->filename;
    
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

    function php_custom_logger() {
        return new PHP_Custom_Logger();
    }

    // Global for backwards compatibility.
    $GLOBALS['php_custom_logger'] = php_custom_logger();

endif;