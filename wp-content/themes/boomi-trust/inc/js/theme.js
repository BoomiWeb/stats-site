/**
 * supporter functions for our theme
 */

/**
 * when a panel item (mobile nav) with children is clicked, this changes the +/- icon
 */
jQuery(document).on('click','.koksijde-mobile-menu .panel-heading.menu-item-has-children a, .koksijde-mobile-menu .panel-collapse .panel-heading a', function(e) {
	var $this=jQuery(this);

	if ($this.hasClass('collapsed')) {
		$this.find('i').removeClass('glyphicon-minus').addClass('glyphicon-plus');
	} else {
		$this.find('i').removeClass('glyphicon-plus').addClass('glyphicon-minus');
	}
});

/**
 * back to top button function
 */
jQuery(document).ready(function($) {
	// browser window scroll (in pixels) after which the "back to top" link is shown
	var offset = 300;
	//browser window scroll (in pixels) after which the "back to top" link opacity is reduced
	var offset_opacity = 1200;
	//duration of the top scrolling animation (in ms)
	var scroll_top_duration = 700;
	//grab the "back to top" link
	$back_to_top = $('.koksijde-back-to-top');

	//hide or show the "back to top" link
	$(window).scroll(function(){
		( $(this).scrollTop() > offset ) ? $back_to_top.addClass('btt-is-visible') : $back_to_top.removeClass('btt-is-visible btt-fade-out');
		if( $(this).scrollTop() > offset_opacity ) {
			$back_to_top.addClass('btt-fade-out');
		}
	});

	//smooth scroll to top
	$back_to_top.on('click', function(event){
		event.preventDefault();
		$('body,html').animate({
			scrollTop: 0 ,
		 	}, scroll_top_duration
		);
	});

});