<?php
class Boomi_Trust_Update_Statistics {
    
    protected $file = '';
	
	public function __construct() {
		$this->get_file();
		$this->process_file();
	}
	
	private function get_file() {
    	// this will be a curl or something.
        $this->file = BOOMI_TRUST_PATH . 'performance-history.json';
	}
	
	private function process_file() {
    	// get json and turn it into an array
    	$file_contents = file_get_contents($this->file);
        $json_arr = json_decode($file_contents, true);
        
        // json construction: array['trust'] => date, data => dataArray.
        $trust_arr = $json_arr['trust'];
        $date = str_replace(' ', '', $trust_arr['date']);
        $data = $trust_arr['data']['dataArray'];
         
        // check date against option '_trust_statistic_updated'
        $existing_date = get_option('_trust_statistic_updated', '');
      
        if ($date > $existing_date) :
            update_option('_trust_statistic_updated', $date);
            
            $this->update_data($data);
        endif;

        return;
	}

    private function update_data($data = array()) {
        if (empty($data))
            return;
            
        foreach ($data as $arr) :
            $slug = strtolower( str_replace(' ', '-', $arr['name']) );
            update_option('_trust_statistic_' . $slug, $arr['value']);
        endforeach;
        
        return;
    }
	
}