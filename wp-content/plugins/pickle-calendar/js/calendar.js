jQuery(document).ready(function($) {

    // AJAX calendar navigation //
	$('body').on('click', '.pickle-calendar .cal-nav', function(e) {
		e.preventDefault();

		var data={
			'action' : 'bscal_nav',
			'month' : $(this).data('month'),
			'year' : $(this).data('year')
		};
	
		$.post(pickleCalOpts.ajax_url, data, function(response) {
			var $parent=$('.pickle-calendar').parent();
			
			$parent.html(''); // clear html
			$parent.append(response); // add calendar
			
			$(document).trigger('pickle_calendar_ajax_load', response);
			
			PickleCalendarRowSetup();
			
			pcalEqualHeight('.pickle-calendar .calendar-day');
		});
	});
	
	// calendar filters (checkbox) //
	$('body').on('change', '.pickle-calendar-filters .filter-term', function(e) {
    	var $PCFilter = $(this).parents('.pickle-calendar-filters');
    	var activeFilters = $PCFilter.data('filters'); // get filters
    	var activeFiltersArr=activeFilters.split(','); // filters to arr

        // add or remove based on check //
    	if (this.checked) {
        	activeFiltersArr.push($(this).val());     	
    	} else {
            var index=activeFiltersArr.indexOf($(this).val());
            
            if (index > -1) {
                activeFiltersArr.splice(index, 1);
            }        	
    	}
    	
    	// remove empty arr elements //
    	activeFiltersArr=activeFiltersArr.filter(function(x) {
            return (x !== (undefined || null || ''));
        });

        // update filters //
    	$PCFilter.data('filters', activeFiltersArr.join());
    	
    	// apply fliters here //
    	$('.pickle-calendar .pickle-calendar-event').each(function(e) {
        	var classes=$(this).attr('class').split(' ');
        	var match=0;

        	if (activeFiltersArr.length === 0) {
            	$(this).removeClass('filter-hide');
        	} else {
                for (var i = 0; i < activeFiltersArr.length; i++) {
                    for (var j = 0; j < classes.length; j++) {
                        if (activeFiltersArr[i] == classes[j]) {
                            match++;
                        }
                    }
                }
                
                if (match === 0) {
                    $(this).addClass('filter-hide');
                } else {
                    $(this).removeClass('filter-hide');                
                }            	
        	}
    	});
	});
	
	// tab filter
	$('body').on('click', '.pickle-calendar-filters .filter-tab', function(e) {
        e.preventDefault();

        var filter = $(this).data('tabSlug');
        
        $('.pickle-calendar-filters .filter-tab').each(function() {
            $(this).removeClass('active');            
        });

        $(this).addClass('active');

        if (filter == 'all')
            filter = '';

    	// apply fliters here //
    	$('.pickle-calendar .pickle-calendar-event').each(function(e) {
        	var classes=$(this).attr('class').split(' ');
        	var match=0;

        	if (filter.length === 0) {
            	$(this).removeClass('filter-hide');
        	} else {

                for (var j = 0; j < classes.length; j++) {
                    if (filter == classes[j]) {
                        match++;
                    }
                }                
                
                if (match === 0) {
                    $(this).addClass('filter-hide');
                } else {
                    $(this).removeClass('filter-hide');                
                }            	
        	}
    	}); 
    });
});

jQuery(window).load(function() {
	PickleCalendarRowSetup();
	pcalEqualHeight('.pickle-calendar .calendar-day');	
});


jQuery(window).resize(function() {
	PickleCalendarRowSetup();
	pcalEqualHeight('.pickle-calendar .calendar-day');
});

/*
 * our equal heights function for calendar days
 */
(function($) {
	
	pcalEqualHeight = function(container) {
		var currentTallest = 0,
		currentRowStart = 0,
		rowDivs = [],
		$el,
		topPosition = 0;
		
		$(container).each(function() {
		
			$el = $(this);
			$($el).height('auto');
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
	};
	
	$.fn.pcEventOffset=function() {
		var thisOffset=jQuery(this).offset();		
		var date=new Date(jQuery(this).data('eventDate') + 'T00:00:00');
		var newDate=new Date(jQuery(this).data('eventDate') + 'T00:00:00');
		
		newDate.setDate(newDate.getDate() - 1);
		
		var nd=new Date(newDate);
		var prevDay=('0' + nd.getDate()).slice(-2);
		var prevMonth=('0' + (nd.getMonth() + 1)).slice(-2);
		var prevDate=nd.getFullYear() + '-' + prevMonth + '-' + prevDay;
		var prevEvent=jQuery('.pickle-calendar').find(".pickle-calendar-event[data-event-date='" + prevDate + "'][data-event-id='" + jQuery(this).data('eventId') + "']");
		
		if (typeof prevEvent.offset() != 'undefined' && prevEvent.offset().top != thisOffset.top) {
			jQuery(this).css({
				'margin-top' : prevEvent.offset().top - thisOffset.top
			});
			
			// double check //
			if (prevEvent.offset().top != jQuery(this).offset().top) {
				jQuery(this).css({
					'margin-top' : parseInt(jQuery(this).css('margin-top')) + (prevEvent.offset().top - jQuery(this).offset().top)
				});			
			}
		}
				
	};	
})(jQuery);

function pcTextWidth(text, font) { 
    $fakeEl=jQuery('<span>').hide().appendTo(document.body);
    	
    $fakeEl.text(text).css('font', font);
    
    var width = $fakeEl.width();
    
    $fakeEl.remove();
    
    return width;
}

function PickleCalendarRowSetup() {
	jQuery('.pickle-calendar-event.multiday').each(function() {
		var eventTotalDays=jQuery(this).data('eventTotalDays');
	
		jQuery(this).css('width', eventTotalDays * 98 + '%'); // set width //
			
		jQuery(this).pcEventOffset(); // tweak margin	
	});
}