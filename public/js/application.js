$(function() {
	$('body').on('errorform', 'form', function( e, ui ){
		$.each(ui.invalidList, function (i, error) {
			var group = $(error.element).closest('.control-group'),
				tabpane = group.closest('.tab-pane'),
				tab = $('[data-toggle=tab][data-target="#' + tabpane.attr('id') + '"]');
			group.find('.validate')
				 .remove(); 
			group.addClass('error')
				 .find('.controls')
				 .append('<span class="validate help-inline">' + error.message + '</span>');
			tab.addClass('tab-error');
		});
	});
	
	$('body').on('validform', 'form', function( e, ui ){
		$.each(ui.validList, function (i, valid) {
			var group = $(valid).closest('.control-group'),
			 	tabpane = group.closest('.tab-pane'),
				tab = $('[data-toggle=tab][data-target="#' + tabpane.attr('id') + '"]');
			group.removeClass('error')
				 .find('.validate')
				 .remove();
			if ( $('.control-group.error', tabpane).size() == 0 ) {
				tab.removeClass('tab-error');
			}
		});
	});
	
	$('body').on('focusin', ':input', function( e ){
		var group = $(this).closest('.control-group'); 
		group.removeClass('error')
			 .find('.validate')
			 .remove();
	});
	
	$('[class^="ui-icon-"], [class*=" ui-icon-"], [class^="icon-"], [class*=" icon-"]', '.btn').each(function( i, item ) {
		var button = $(item).closest('.btn');
		if ( !$.trim(button.text()) )  {
			button.addClass('btn-icon-only');
		} else {
			button.removeClass('btn-icon-only');
		}
	});
	
	$('body').on('sentaction', '.modal a[data-storage]', function ( e ) {
		$(this).closest('.modal').modal('hide');
	});
	
	$('body').on('ajaxComplete', function( e ) {
		$('[data-validate*="required"]').each( function( i, item ) {
			$('label[for=' + $(item).attr('name') + ']').addClass('label-required');
		});
		$('form[name="requesters-units-form"]').form({ajax: true, format: 'json'});
		$('form[name="product-units-form"]').form({ajax: true, format: 'json'});
	})
	$('body').trigger('ajaxComplete');
	
	$('body').on('sentform', 'form[name="requesters-units-form"]', function( e ) {
		$('#requesters-units-form legend .icon-loading').remove();
	});
	$('body').on('sendform', 'form[name="requesters-units-form"]', function( e ) {
		$('#requesters-units-form legend .icon-loading').remove();
		$('#requesters-units-form legend').append('<i class="icon-loading"></i>');
	});
	$('body').on('sendaction', '#requesters-units-form [data-column-action="administrative-unit-remove"]', function( e ) {
		$('#requesters-units-form legend .icon-loading').remove();
		$('#requesters-units-form legend').append('<i class="icon-loading"></i>');
	});
	
	$('body').on('sentform', 'form[name="product-units-form"]', function( e ) {
		$('#product-units-form legend .icon-loading').remove();
	});
	$('body').on('sendform', 'form[name="product-units-form"]', function( e ) {
		$('#product-units-form legend .icon-loading').remove();
		$('#product-units-form legend').append('<i class="icon-loading"></i>');
	});
	$('body').on('sendaction', '#product-units-form [data-column-action="product-unit-remove"]', function( e ) {
		$('#product-units-form legend .icon-loading').remove();
		$('#product-units-form legend').append('<i class="icon-loading"></i>');
	});
});