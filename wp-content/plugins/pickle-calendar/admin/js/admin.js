jQuery(document).ready(function($) {
	
	if ($('input#include_details').is(':checked')) {
		$('.pickle-calendar-settings-form tr.details-box').show();	
	}
	
	// taxonomy display options onload.
    if ($("#tax-display input[type=radio]:checked").val() == 1) {
        $('#tax-display-type').show(); // show display options.
        
        if ($("#tax-display-type input[type=radio]:checked").val() == 'tabs') {
            $('#tax-tabs-dsiplay-all-tab').show(); // show tab options.
        } 
    }
    
	// taxonomy display options onchange.
    $('#tax-display input.tax-display').change(function() {       
        if ($(this).val() == 1) {
            $('#tax-display-type').slideDown('fast'); // show display options                   	   
        } else {
            $('#tax-display-type').slideUp('fast'); // hide display options 
        }
    });

	// taxonomy display options type onchange.
    $('#tax-display-type input.tax-display-type').change(function() {      
        if ($(this).val() == 'tabs') {
            $('#tax-tabs-dsiplay-all-tab').slideDown('fast'); // show tab options                   	   
        } else {
            $('#tax-tabs-dsiplay-all-tab').slideUp('fast'); // hide tab options 
        }
    });
	
});