jQuery(document).ready(function($) {

	$('body').on('click', '.simcoe-calendar .cal-nav', function(e) {
		e.preventDefault();

		var data={
			'action' : 'bscal_nav',
			'month' : $(this).data('month'),
			'year' : $(this).data('year')
		};
	
		$.post(simcoeCalOpts.ajax_url, data, function(response) {
			var $parent=$('.simcoe-calendar').parent();
			
			$parent.html(''); // clear html
			$parent.append(response); // add calendar
			
			$(document).trigger('simcoe_calendar_ajax_load', response);
		});
	});
	
});

jQuery(window).load(function() {
	bscalEqualHeight('.simcoe-calendar .calendar-day');
});


jQuery(window).resize(function(){
	bscalEqualHeight('.simcoe-calendar .calendar-day');
});

/*
 * our equal heights function for calendar days
 */
(function($) {
	
bscalEqualHeight = function(container) {
	var currentTallest = 0,
	currentRowStart = 0,
	rowDivs = new Array(),
	$el,
	topPosition = 0;
	
	$(container).each(function() {
	
		$el = $(this);
		$($el).height('auto')
		topPostion = $el.position().top;
		
		if (currentRowStart != topPostion) {
			for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
				rowDivs[currentDiv].height(currentTallest);
			}
			
			rowDivs.length = 0; // empty the array
			currentRowStart = topPostion;
			currentTallest = $el.height();
			rowDivs.push($el);
		} else {
			rowDivs.push($el);
			currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
		}
		
		for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
			rowDivs[currentDiv].height(currentTallest);
		}

	});
}	
	
})(jQuery);