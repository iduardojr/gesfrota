$(function() {
	$('body').on('errorform', 'form', function( e, ui ){
		$('.control-group', ui.form).removeClass('error');
		$('.control-group', ui.form).find('.validate').remove(); 
		$('.tab-error', ui.form).removeClass('tab-error');
		$.each(ui.invalidList, function (i, error) {
			var group = $(error.element).closest('.control-group'),
				tabpane = group.closest('.tab-pane'),
				tab = $('[data-toggle=tab][data-target="#' + tabpane.attr('id') + '"]');
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
			$('label[for="' + $(item).attr('name').replace('[]', '') + '"]').addClass('label-required');
		});
		
		$('form[name="service-card-form"]').form({ajax: true, format: 'json'});
		$('form[name="passangers-form"]').form({ajax: true, format: 'json'});
		$('form[name="fleet-owner-form"]').form({ajax: true, format: 'json'});
	})
	$('body').trigger('ajaxComplete');
		
	// SUBFORM FLEET OWNER
	$('body').on('sentform', 'form[name="fleet-owner-form"]', function( e, form ) {
		$('#owner-new .modal-header h3 .icon-loading').remove();
		if (! form.response['alert-message']) {
			$('#owner-new').modal('hide');
		}
	});
	$('body').on('sendform', 'form[name="fleet-owner-form"]', function( e ) {
		$('#owner-new .modal-header h3 .icon-loading').remove();
		$('#owner-new .modal-header h3').append('<i class="icon-loading"></i>');
	});
	
	// SUBFORM SERVICE CARD
	$('body').on('sentform', 'form[name="service-card-form"]', function( e ) {
		$('#service-card-form legend .icon-loading').remove();
	});
	$('body').on('sendform', 'form[name="service-card-form"]', function( e ) {
		$('#service-card-form legend .icon-loading').remove();
		$('#service-card-form legend').append('<i class="icon-loading"></i>');
	});
	
	$('body').on('sendaction', '#service-card-form [data-column-action="service-card-remove"]', function( e ) {
		$('#service-card-form legend .icon-loading').remove();
		$('#service-card-form legend').append('<i class="icon-loading"></i>');
	});
	
	// SUBFORM PASSANGERS
	$('body').on('sentform', 'form[name="passangers-form"]', function( e ) {
		$('#passangers-form legend .icon-loading').remove();
	});
	$('body').on('sendform', 'form[name="passangers-form"]', function( e ) {
		$('#passangers-form legend .icon-loading').remove();
		$('#passangers-form legend').append('<i class="icon-loading"></i>');
	});
	
	$('body').on('sendaction', '#passangers-form [data-column-action="passanger-remove"]', function( e ) {
		$('#passangers-form legend .icon-loading').remove();
		$('#passangers-form legend').append('<i class="icon-loading"></i>');
	});
	
	
	$('body').on('keypress', 'input[name="nif"]:not([data-mask])', function( e ) {
		$(this).unmask();
		var val = $(this).val();
		var length  = val.replace(/\D/g,'').length;
		var opt = {
			placeholder: '',
			autoclear: false
			};
		var mask = length <= 11 ? "999.999.999-99?9" : "99.999.999/999?9-99";
	    
		$(this).mask(mask, opt);
	});
	
	$('body').on('change', '#request-trip-form #duration', function (e) {
		if($('#duration').field('value') == 'custom') {
			$('#duration-group').show();
		} else {
			$('#duration-group').hide();
		}
	});
	
	$('body').on('change', '#fleet-vehicle-form #fleet', function (e) {
		if($(this).field('value') == 2) {
			$('#asset-code').field('disabled', true);
		} else {
			$('#asset-code').field('disabled', false);
		}
	});
	
	$('#request-trip-form #duration').trigger('change');
	$('#fleet-vehicle-form #fleet').trigger('change');
	
	$.fn.datepicker.defaults.language = 'pt-BR';
	
	$('body').on('click', '#request_types #type_all', function(e) {
		$('#request_types #type').field('value', null);
	});
	
	$('body').on('click', '#request_types #type_trip', function(e) {
		$('#request_types #type').field('value', 'T');
	});
	
	$('body').on('click', '#request_types #type_freight', function(e) {
		$('#request_types #type').field('value', 'F');
	});
	switch ($('#request_types #type').field('value')) {
		case 'T':
			$('#request_types #type_trip').trigger('click');
			break;
			
		case 'F':
			$('#request_types #type_freight').trigger('click');
			break;
			
		default:
			$('#request_types #type_all').trigger('click');
	}
	
	$('body').on('click', '#fleet_types #type_all', function(e) {
		$('#fleet_types #type').field('value', null);
	});
	
	$('body').on('click', '#fleet_types #type_vehicle', function(e) {
		$('#fleet_types #type').field('value', 'V');
	});
	
	$('body').on('click', '#fleet_types #type_equipment', function(e) {
		$('#fleet_types #type').field('value', 'E');
	});
	switch ($('#fleet_types #type').field('value')) {
		case 'V':
			$('#fleet_types #type_vehicle').trigger('click');
			break;
			
		case 'E':
			$('#fleet_types #type_equipment').trigger('click');
			break;
			
		default:
			$('#fleet_types #type_all').trigger('click');
	}
	
	$('body').on('click', '#agency-search #agency-table [data-storage]', function(e) {
		$('#agency-id').seek('lookup');
	});
	
	$('body').on('click', '#administrative-unit-search #administrative-unit-table [data-storage]', function(e) {
		$('#administrative-unit-id').seek('lookup');
	});
	
	$('body').on('click', '#transfer-from-modal [data-storage]', function(e) {
		$.each( $(this).data('storage'), function( key, value ) {
			$('#from-' + key ).trigger($.Event('update'), value);
		});
		$('#from-agency-id').seek('lookup');
	});
	
	$('body').on('click', '#transfer-to-modal [data-storage]', function(e) {
		$.each( $(this).data('storage'), function( key, value ) {
			$('#to-' + key ).trigger($.Event('update'), value);
		});
		$('#to-agency-id').seek('lookup');
	});
	
	$('body').on('click', '#transfer-unit-from-modal [data-storage]', function(e) {
		$.each( $(this).data('storage'), function( key, value ) {
			$('#from-' + key ).trigger($.Event('update'), value);
		});
		$('#from-administrative-unit-id').seek('lookup');
	});
	
	$('body').on('click', '#transfer-unit-to-modal [data-storage]', function(e) {
		$.each( $(this).data('storage'), function( key, value ) {
			$('#to-' + key ).trigger($.Event('update'), value);
		});
		$('#to-administrative-unit-id').seek('lookup');
	});
	
	var width = $('.container').width()+50;
	var height = $(window).width()/1.77-400;
	$('#disposal-survey-view').css({
		'width': width,
		'margin-left': -width/2
	});
	$('#disposal-survey-view .modal-body').css({
		'height': height,
		'max-height': 'none'
	});
	
	
	$('body').on('update', '#results-center, #result-center-id', function (e, values) {
		var that = $(this);
		$('option', that).remove();
	    if ( values != undefined && values.constructor === Object ) {
			$.each( values, function( value, label ) {
				that.append('<option value="' + value + '">' + label + '</option>');
			});
		} 
		that.trigger("chosen:updated");
		return false;
	});
	
	$('body').on('update', '#result-center-required', function (e, value) { 
		if (value) {
			$('#result-center-required').field('value', 1);
			$('#results-center-group').show();
		} else {
			$('#result-center-required').field('value', 0);
			$('#results-center-group').hide();
		}
		return false;
	});
	
	$('body').on('update', '#driver-form #email', function (e, value) { 
		$(this).field('disabled', !!value);
	});
	
	$('#result-center-required').trigger('update', $('#result-center-required').field('value') > 0 ? true : false);
	
	$('body').on('change', '#form-filter #agency', function (e) {
		$('#results_center_chosen .chosen-choices').addClass('loading');
		var key = $(this).val();
		$.getJSON('/agency/result-center/' + (key == '' ? 0 : key), function (data) {
			$.each(data, function(key, value) {
				$('#' + key).trigger('update', value);
			});
		}).always(function() {
			$('#results_center_chosen .chosen-choices').removeClass('loading');
			if ( $('option', '#form-filter #results-center').size() ) {
				$('#results-center').field('disabled', false);
			} else {
				$('#results-center').field('disabled', true);
			}
		});
	});
	
	
});