<?php
final class Boomi_Trust_Logger {
	
	public $version='1.0.0';
	
	protected static $_instance = null;
	
	protected $filename='log.txt';

	public static function instance() {
		if (is_null(self::$_instance)) :
			self::$_instance =new self();
		endif;
		
		return self::$_instance;
	}

	public function __construct() {		

	}
	
	public function log($message='') {
		$this->write_to_log($message);
	}
	
	protected function write_to_log($message='') {
	    $time=date("m-d-y H:i");
	    $file=BOOMI_TRUST_PATH.$this->filename;
	    
	    if (is_array($message) || is_object($message)) :
	    	$message=print_r($message, true);
	    endif;
	    
	    $message="\n#$time\n".$message;
	    
	    $open=fopen($file, "a"); 
	    $write=fputs($open, $message); 
	    fclose($open);		
	}

}

function boomi_trust_logger() {
    return Boomi_Trust_Logger::instance();
}

// Global for backwards compatibility.
$GLOBALS['boomitrustlogger'] = boomi_trust_logger();