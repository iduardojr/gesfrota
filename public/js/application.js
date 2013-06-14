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
	
	$('[data-validate*="required"]').each( function( i, item ) {
		$('label[for=' + $(item).attr('name') + ']').append(' <span class="text-error">*</span>');
	});
});