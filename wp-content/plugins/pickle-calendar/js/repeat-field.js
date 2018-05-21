(function($) {
	
	repeatField = function(options) {
		var default_settings = {
			container: '.repeatable-field',
			datePicker: false
		};

		var settings = $.extend({}, default_settings, options);
		
		initalize(settings.container);

		function initalize(elem) {		
			var $container=$(elem).parent();
			var $clone=$(elem + ':last', $container).clone();
			var oldRowId=parseInt($clone.data('rowId'));
			var newRowId=oldRowId+1;
			
			$clone.find('input').val(''); // clear values
			$clone.attr('data-row-id', newRowId);

			// update name //
			$clone.find('input').each(function() {
				$(this).attr('name', $(this).attr('name').replace(/\d+/, newRowId));
			});
			
			// datepick //
			if (settings.datePicker) {
				$clone.find('.pcdetail-pickr').removeClass('hasDatepicker').attr('id', '');
				$clone.find('.pcdetail-pickr').datepicker(settings.datePicker);
			}							
					
			$clone.insertBefore('a.pc-repeater'); // insert			
		}
	};
	
	resetIDs = function(elem) {
		var counter=0;
		
		$(elem).each(function() {
			$(this).attr('data-row-id', counter);
			
			$(this).find('input').each(function() {
				$(this).attr('name', $(this).attr('name').replace(/\d+/, counter));
			});
			
			counter++;
		});
	};
	
})(jQuery);