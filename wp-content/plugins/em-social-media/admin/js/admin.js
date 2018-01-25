jQuery(document).ready(function($) {

	// add field (row) //
	$('#emsm-add-field').on('click', function(e) {  
		e.preventDefault();

		addNewRow();
	});
	
	// delete row //
	$('#emsm-social-media-table').on('click', 'a.emsm-delete', function(e) {
		e.preventDefault();
		
		var rowID=$(this).data('rowid');
		
		$('#' + rowID).remove();
	});

});

/**
 * addNewRow function.
 * 
 * @access public
 * @return void
 */
function addNewRow() {
	var $clone=jQuery('#emsm-social-media-table tr:last').clone();
	var oldSlug=$clone.attr('id').replace('emsm-', '');	
	var newSlug=generateID();

	clearFormElements($clone);
	clearOldSlug($clone, oldSlug, newSlug);
	clearIcon($clone, newSlug);
	
	jQuery('#emsm-social-media-table').append($clone);
}

/**
 * clearFormElements function.
 * 
 * @access public
 * @param mixed $div
 * @return void
 */
function clearFormElements($div) {
  $div.find(':input').each(function() {  
    switch(this.type) {
        case 'password':
        case 'text':
        case 'textarea':
        case 'file':
        case 'select-one':
        case 'select-multiple':
        case 'date':
        case 'number':
        case 'tel':
        case 'url':
        case 'hidden':
        case 'email':
            jQuery(this).val('');
            break;
        case 'checkbox':
        case 'radio':
            this.checked = false;
            break;
    }
  });
}

/**
 * clearOldSlug function.
 * 
 * @access public
 * @param mixed $div
 * @param mixed slug
 * @param mixed newSlug
 * @return void
 */
function clearOldSlug($div, slug, newSlug) {
	// clear ids and names //
	
	$div.find(':input').each(function() {
		var newID=this.id.replace(slug, newSlug);
		var newName=this.name.replace(slug, newSlug);

		jQuery(this).attr('id', newID);
		jQuery(this).attr('name', newName);
	});
	
	// adjust data for select icon link //
	$div.find('a.emsm-select-icon').attr('data-input-id', newSlug);
}

/**
 * generateID function.
 * 
 * @access public
 * @return void
 */
function generateID() {
	return '_' + Math.random().toString(36).substr(2, 9);
}

/**
 * clearIcon function.
 * 
 * @access public
 * @param mixed $div
 * @param mixed newSlug
 * @return void
 */
function clearIcon($div, newSlug) {
	$div.find('span.icon-img').attr('class', newSlug + '-icon icon-img');
	$div.find('.icon-img-fa').html('');
}

// make list sortable //
jQuery(function($) {
	$("#emsm-sortable").sortable({
		update: function(event, ui) {
			reorderSort();			
		}
	});
	$("#emsm-sortable").disableSelection();
});

/**
 * reorderSort function.
 * 
 * @access public
 * @return void
 */
function reorderSort() {
	jQuery('#emsm-sortable tr').each(function(i) {	
		jQuery(this).find('input.order').val(i);
	});
}

// select icon dialog //
(function ($) {
	var slug='';
	
	// initalise the dialog
	$('#emsm-icons-overlay').dialog({
		title: 'Select a Social Media Icon',
		dialogClass: 'wp-dialog',
		autoOpen: false,
		draggable: false,
		width: 'auto',
		modal: true,
		resizable: false,
		closeOnEscape: true,
		position: {
		  my: "center",
		  at: "center",
		  of: window
		},
		open: function () {
		  // close dialog by clicking the overlay behind it
		  $('.ui-widget-overlay').bind('click', function(){
		    $('#emsm-icons-overlay').dialog('close');
		  })
		},
		create: function () {
		  // style fix for WordPress admin
		  $('.ui-dialog-titlebar-close').addClass('ui-button');
		},
	});
	
	// bind a button or a link to open the dialog
	$('#emsm-social-media-table').on('click', 'a.emsm-select-icon', function(e) { 		 
		e.preventDefault();

		slug=$(this).data('inputId');
	
		$('#emsm-icons-overlay').dialog('open');
	});

	// an icon is selected //
	$('.fa-icons-list li a').on('click', function(e) {
		e.preventDefault();
		
		var icon=$(this).data('icon');
	
		// set hidden input //
		$('#' + slug + '-icon').val(icon);
	
		// show new icon //
		$('.' + slug + '-icon .icon-img-fa').html('<i class="fa ' + icon + '"></i>');
		
		// close dialog //
		$('#emsm-icons-overlay').dialog('close');
	});
  
})(jQuery);
