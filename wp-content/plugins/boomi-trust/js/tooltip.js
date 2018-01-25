jQuery(document).ready(function($) {
    $('.simcoe-calendar .trust-cal-icon').tooltip();
});

jQuery(document).bind('simcoe_calendar_ajax_load', function(event, response) {
	jQuery('.simcoe-calendar .trust-cal-icon').tooltip();
});