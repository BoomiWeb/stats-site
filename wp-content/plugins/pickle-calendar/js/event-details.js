jQuery(document).ready(function($) {
	
	var fpConf={
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd',
	};

	$('.pcdetail-pickr').datepicker(fpConf);
	
	// check second date //
	$('body').on('change', '.pcdetail-pickr.start-date', function(e) {
    	var startDate=$(this).val();
    	var endDate=$(this).parent().find('.pcdetail-pickr.end-date').val();
    	
    	if (endDate < startDate) {
        	$(this).parent().find('.pcdetail-pickr.end-date').val(startDate);
    	}
	});
	
	// add new date row //
	$('.pc-repeater').on('click', function(e) {
		e.preventDefault();
		
		var el=$(this).data('field');
		
		repeatField({
			container: el,
			datePicker: fpConf
		});
	});
	
	// remove date row //
	$('body').on('click', '.pc-remove-row', function(e) {
		e.preventDefault();
		
		var rowID=$(this).parent().data('rowId');
		
		$(this).parent().remove();
		
		resetIDs('.event-date-wrap');
	});	
	
});