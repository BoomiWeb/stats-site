<?php
class Boomi_Trust_Update_Daily_Integrations {

    public function __construct() {}

    public function run() {
        return $this->process_file();
    }

    private function process_file() {
        $message = '';

        // get json and turn it into an array
        $file_contents = file_get_contents( 'http://erikmitchell.net/_b00mI/trust-performance.json' );
        $json_arr = json_decode( $file_contents, true );

        // json construction: array['Process Count'].
        $process_count = array_map( 'boomi_trust_clean_json', $json_arr['Process Count'] );

        // check date against option '_trust_process_count'
        $existing_count = get_option( '_trust_process_count', '' );

        if ( serialize( $process_count ) != serialize( $existing_count ) ) :
            update_option( '_trust_process_count', $process_count );

            $message = 'Daily integrations updated.';
        elseif ( serialize( $process_count ) == serialize( $existing_count ) ) :
            $message = 'Daily integrations already updated.';
        else :
            $message = 'Daily integrations failed to update.';
        endif;

        return $message;
    }

}

/**
 * Main function.
 *
 * @access public
 * @return class
 */
function boomi_trust_update_daily_integrations() {
    return new Boomi_Trust_Update_Daily_Integrations();
}

function boomi_trust_clean_json( $arr ) {
    $arr_clean = array();

    foreach ( $arr as $key => $value ) :
        $clean_key = trim( preg_replace( '/\s+/', ' ', $key ) );
        $clean_value = trim( preg_replace( '/\s+/', ' ', $value ) );
        $arr_clean[ $clean_key ] = $clean_value;
    endforeach;

    return $arr_clean;
}
