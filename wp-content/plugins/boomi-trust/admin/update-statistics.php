<?php
class Boomi_Trust_Update_Statistics {
	
	public function __construct() {
		$this->process_file();
	}
	
	private function process_file() {
    	// get json and turn it into an array
    	$file_contents = file_get_contents('http://erikmitchell.net/_b00mI/trust-statistics.json');
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