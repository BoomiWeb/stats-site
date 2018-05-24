<?php
/*
Plugin Name: Boomi Trust Importer
Plugin URI: 
Description: Import statuses from the old trust site.
Author: Erik Mitchell
Author URI: 
Version: 0.1.0
Text Domain: boomi-trust-importer
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

class Boomi_Trust_Importer {
	
	protected $id=0;
	
	protected $file='';
	
	public function init() {
		$this->header();

		$step=empty($_GET['step']) ? 0 : (int) $_GET['step'];
		
		switch ( $step ) {
			case 0:
				$this->intro();
				break;
			case 1:
				check_admin_referer('import-upload');
				set_time_limit(0);
				$this->upload();
				break;
		}

		$this->footer();
	}
	
	protected function header() {
		echo '<div class="wrap">';
		
		echo '<h2>' . __( 'Import Boomi Trust', 'boomi-trust-importer' ) . '</h2>';	
	}	
	
	protected function footer() {
		echo '</div>';
	}
	
	protected function intro() {
		wp_import_upload_form(add_query_arg('step', 1));
	}
	
	protected function upload() {
		$file = wp_import_handle_upload();
		
		if ( isset( $file['error'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'boomi-trust-importer' ) . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
		} else if ( ! file_exists( $file['file'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'boomi-trust-importer' ) . '</strong><br />';
			printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'boomi-trust-importer' ), esc_html( $file['file'] ) );
			echo '</p>';
			return false;
		}
		
		$this->id=(int) $file['id'];
		$this->file=get_attached_file($this->id);
		
		$result=$this->process_csv();
		//if ( is_wp_error( $result ) )
			//return $result;	
	}
	
	public function process_csv() {
		$row_separator="\n";
		$col_separator=',';
		$data=array();
		$row_counter=1;
		$headers=array();
	
		ini_set('auto_detect_line_endings', TRUE); // added for issues with MAC
	
		$handle=fopen($this->file, 'r');
		
		if ($handle == false) :
			echo '<p><strong>'.__('Failed to open file.', 'boomi-trust-importer').'</strong></p>';
			wp_import_cleanup($this->id);
			return false;
		endif;		
		
		while (($row = fgetcsv($handle, 0, $col_separator)) !== FALSE) :
			if ($row_counter==1) :			
				$headers=array_map('sanitize_key', $row);				
			else :;
				$data[]=array_combine($headers, $row);
			endif;

			$row_counter++;	
		endwhile;
		
		// remove empty columns
		
		foreach ($data as $row) :		
			$this->import_row($row);
		endforeach;
		
		wp_import_cleanup($this->id);
		
		echo '<h3>'.__('All Done.', 'boomi-trust-importer').'</h3>';
	}
	
	protected function import_row($row=array()) {	
        $service=get_page_by_title($row['servicename'], 'object', 'services');
		$statustype=get_page_by_title($row['statustype'], 'object', 'statustypes');
		
		$post_id=wp_insert_post(array(
			'post_title' => $service->post_title.' - '.$row['date'],
			'post_name' => sanitize_title($service->post_title.' '.$row['date']),
			'post_content' => $row['details'],
			'post_type' => 'cloudstatuses',
			'post_status' => 'publish',
		));
		
		if (is_wp_error($post_id))
			return false;
		
		update_post_meta($post_id, '_service', $service->ID);
		update_post_meta($post_id, '_statustype', $statustype->ID);
		update_post_meta($post_id, '_outageminutes', $row['outageminutes']);
		update_post_meta($post_id, '_date_and_time_of_occurance', $row['date']);

		return true;
	}
	
}

function boomi_trust_importer_init() {
	$GLOBALS['boomi_trust_import'] = new Boomi_Trust_Importer();
	
	register_importer(
		'boomi-trust-importer', 
		'Boomi Trust Importer', 
		__('Import statuses from the old trust site.', 'boomi-trust-importer'), 
		array($GLOBALS['boomi_trust_import'], 'init') 
	);
}
add_action('admin_init', 'boomi_trust_importer_init');
