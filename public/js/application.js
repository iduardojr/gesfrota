$(function() {
	$('form').on('errorform', function( e, data ){
		$.each(data.list, function (i, error) {
			var group = $(error.element).closest('.control-group');
			group.find('.validate')
				 .remove(); 
			group.addClass('error')
				 .find('.controls')
				 .append('<span class="validate help-inline">' + error.message + '</span>');
		});
	});
	
	$('form').on('validform', function( e, data ){
		$.each(data.list, function (i, valid) {
			var group = $(valid).closest('.control-group'); 
			group.removeClass('error')
				 .find('.validate')
				 .remove();
		});
	});
	
	$(':input').on('focusin', function( e ){
		var group = $(this).closest('.control-group'); 
		group.removeClass('error')
			 .find('.validate')
			 .remove();
	});
	
	$(':input').on('focusout', function( e ) {
		if ($(this).closest('[data-control]')) {
			$(this).valid();
		}
		return true;
	});
	
	$('[data-validate*="required"]').each( function( i, item ) {
		$('label[for=' + $(item).attr('name') + ']').addClass('label-required');
	});
});