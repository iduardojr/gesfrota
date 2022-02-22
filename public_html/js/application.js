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
			$('label[for="' + $(item).attr('name') + '"]').addClass('label-required');
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
	
	$('body').on('change', '#duration', function (e) {
		if($('#duration').field('value') == 'custom') {
			$('#duration-group').show();
		} else {
			$('#duration-group').hide();
		}
	});
	
	$('#duration').trigger('change');
	
	$.fn.datepicker.defaults.language = 'pt-BR';
	
	
	/* DIRECTIONS CLASS DEFINITION
	 * ===================== */
	var Directions = function(el, options, optMaps) {
		this.service = new google.maps.DirectionsService();
		this.renderer = new google.maps.DirectionsRenderer();
		this.element = $(el);
		this.options = $.extend({}, $.fn.directions.defaults, options);
		this.renderer.setMap(new google.maps.Map(el, optMaps));
		var selector = [];
		var $this = this;
		
		if(this.data('place-way')) {
			selector.push('#' + this.data('place-way') + ' [data-attribute=description]');
			$('body').on('refresh', '#' + this.data('place-way'), function(e) {
				var index = 1;
				if ($this.data('place-from')) {
					$('.add-on', '#' + $this.data('place-from')).html($this.marker($this.data('marker'), index));
					index++;
				}
				
				$('.add-on', '#' + $this.data('place-way')).each(function(){
					$(this).html($this.marker($this.data('marker'), index));
					index++;
				});
				
				if ($this.data('place-to')) {
					$('.add-on', '#' + $this.data('place-to')).html($this.marker($this.data('marker'), index));
				}
				
			});
		}
		if (this.data('place-from')) {
			selector.push('#' + this.data('place-from') + ' [data-attribute=description]');
		}
		if ($this.data('place-to')) {
			selector.push('#' + this.data('place-to') + ' [data-attribute=description]');
		}
		
		if (selector.length > 0) {
			$('body').on('change', selector.join(','), function() {
		    	$this.route();
			});
		}
		
		this.route();
	};
	
	Directions.prototype = {
		
		route: function (){
			this._route(this.waypoints(), this.from(), this.to());
		},
		
		waypoints: function() {
			var points = [];
			if (this.data('place-way')) {
				$('[data-attribute=place]', '#' + this.data('place-way')).each(function () {
					var place = $(this).val();
					if ( place ) {
						points.push({ location: {placeId:  place},  stopover: true });
					}
				});
			}
			return points;
		},
		
		from: function () {
			if (this.data('place-from')) {
				var place = $('[data-attribute=place]', '#' + this.data('place-from')).val();
				if ( place ) {
					return { placeId: place }
				}
			}
			return null;
		},
		
		to: function () {
			if (this.data('place-to')) {
				var place = $('[data-attribute=place]', '#' + this.data('place-to')).val();
				if ( place ) {
					return { placeId: place }
				}
			}
			return null;
		},
		
		data: function(attr) {
			return this.element.data(attr);
		},
		
		_route: function(waypoints, from, to) {
			var request = this.options;
			var point;
			
			if ( from ) {
				request['origin'] = from;
			} else {
				point = waypoints.shift();
				if ( point ) {
					request['origin'] = point.location;
				}
			}
			
			if ( to ) {
				request['destination'] = to;
			} else {
				point = waypoints.pop();
				if ( point ) {
					request['destination'] = point.location;
				}
			}
			
			if (waypoints.length > 0) {
				request['waypoints'] = waypoints;
			}
			
			if (request.origin && request.destination) {
				var $this = this;
			  	this.service.route(request).then((response) => {
			      $this.renderer.setDirections(response);
			    });
			}
		},
		
		marker: function (template, i) {
			var index = function (i) {
				if ( i > 25) {
					return this((i/26)-1) + this(i%26);
				}
				return 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.substr(i, 1);
			};
			return template.replace(new RegExp( "\\{0\\}", "g" ), i)
						   .replace(new RegExp( "\\{A\\}", "g" ), index(i-1))
						   .replace(new RegExp( "\\{a\\}", "g" ), index(i-1).toLowerCase());
		}
		
	};
	
	/* DIRECTIONS PLUGINS DEFINITION
	 * ============================= */
	$.fn.directions = function ( option, optMaps ) {
		return this.each(function () {
			var $this = $(this),
				data = $this.data('directions'),
		        options = typeof option == 'object' && option;
			
		      if ( ! data )  {
		    	  $this.data('directions', (data = new Directions(this, options, optMaps)));
		      }
		      if ( typeof option == 'string' ) {
		    	  data[option]();
		      };
		 });
	};

	$.fn.directions.defaults = { 
		provideRouteAlternatives: true,
		unitSystem: google.maps.UnitSystem.METRIC, 
		travelMode: google.maps.TravelMode.DRIVING
	}
	$.fn.directions.Constructor = Directions;
	
	
	/* DIRECTIONS DATA-API
	 * ===================== */
	$('[data-renderer=directions]').each(function() {
    	$(this).directions($(this).data('options-request'), $(this).data('options-map'));
	});
	
	
	/* PLACEINPUT CLASS DEFINITION
	 * ====================== */
	var PlaceInput = $.extend({}, $.fn.field.controls.Default, {
		
		create: function(element) {
			var suggest = element.data('suggest');
			var select = function( e, data ) {
				if ( $.isPlainObject(data) && $.isPlainObject(data.value) ) {
					$.each($(':input', element.parent()), function () {
						var attr = $(this).data('attribute');
						$(this).val(data.value[attr]);
					});
				} else {
					$(':input[data-attribute=place]', element.parent()).val(data.value);
				}
			};
			if (suggest) {
				suggest.options.select = select; 
			} else {
				element.suggest($.extend({}, element.data(), {select: select}));
			}
		},
		
		set: function ( element, value ) {
			var $this = this;
			$.each($(':input', element.parent()), function () {
				var attr = $(this).data('attribute');
				$(this).val(value[attr]);
			});
		},
		
		get: function( element ) {
			var value = {};
			var $this = this;
			$.each($(':input', element.parent()), function(){
				var attr = $(this).data('attribute');
				value[attr] = $(this).val();
			});
			return value;
		},
		
		disable: function ( element ) {
			$(':input', element.parent()).attr('disabled', 'disabled');
		},
		
		enable: function( element ) {
			$(':input', element.parent()).removeAttr('disabled');
		},
		
		defaultValue: function( element ) {
			var value = {};
			return $.each($(':input', element.parent()), function(){
					var attr = $(this).data('attribute');
					value[attr] = this.defaltValue;
			});
		}
	}); 
	
	/* DYNINPUT CLASS DEFINITION
	 * ====================== */
	var DynInput = $.extend({}, $.fn.field.controls.Default, {
		
		_set: function ( element, values ) {
			var $this = this;
			element.empty();
			$.each(values, function (i, value) {
				$this._add(element);
				if ( $.isPlainObject(value) ) {
					$.each($this.input(element, i), function () {
						var attr = $(this).data('attribute');
						$(this).field('value', value[attr]);
					});
				} else {
					$this.input(element, i).field('value', value);
				}
			});
			element.field('disabled', element.is('.disabled'));
		},
		
		set: function ( element, values ) {
			this._set(element, values);
			element.trigger("refresh");
		},
		
		get: function( element ) {
			var values = [];
			var $this = this;
			this.items(element).each(function (i) {
				if ($this.input(element, i).length > 0) {
					var value = {}; 
					$.each($this.input(element, i), function() {
						var attr = $(this).data('attribute');
						value[attr] = $(this).field('value');
					});
					values.push(value);
				} else {
					values.push(this.input(element, i).field('value'));
				}
			});
			return values;
		},
		
		disable: function ( element ) {
			element.addClass('disabled');
			this.toggle(element, true);
			$('[data-toggle=dyninput-remove]', element).attr('disabled', 'disabled');
			$('[data-control]', this.items(element)).field('disabled', true);
		},
		
		enable: function( element ) {
			element.removeClass('disabled');
			if (this.length(element) < element.data('max')) {
				this.toggle(element, false);
			}
			$('[data-toggle=dyninput-remove]', element).removeAttr('disabled');
			$('[data-control]', this.items(element)).field('disabled', false);
		},
		
		defaultValue: function( element ) {
			return element.data('value');
		}, 
		
		items: function (element) {
			return $('.control-group', element);
		},
		
		input: function (element, index) {
			return $('.control-group:eq('+ index +') [data-control]', element);
		},
		
		_add: function (element) {
			if ( element.is('.disabled') )  return false;
			if (element.data('max') == undefined || this.length(element) < element.data('max')) {
				var i = this.length(element) + 1;
				var template = this.format(element.data('template'), i);
				
				element.append(template);
				if (i <= element.data('min')) {
					$('[data-toggle=dyninput-remove]', template).remove();
				}
				if (i == element.data('max')) {
					this.toggle(element, true);
				}
				return true;
			}
			return false;
		},
		
		add: function (element) {
			if (this._add(element)) {
				element.trigger("refresh");
				return true;
			}
			return false;
		},
		
		_remove(element, index) {
			if ( element.is('.disabled') )  return false;
			var input = $('.control-group:eq('+ (index - 1) +')', element);
			if (input.length > 0) {
				input.remove();
				this.toggle(element, false);
				return true;
			}
			return false;
		},
		
		remove(element, index) {
			if (this._remove(element, index)) {
				this._set(element, this.get(element));
				element.trigger("refresh");
				return true;
			}
			return false;
		},
		
		toggle: function (element, disabled) {
			var buttons = $('[data-toggle="dyninput-add"][data-target="#' + element.attr('id') + '"],[data-toggle="dyninput-add"][href="#' + element.attr('id') + '"]');
			if ( disabled ) {
				buttons.attr('disabled', 'disabled');
			} else {
				buttons.removeAttr('disabled');
			}
		},
		
		format: function (template, i) {
			var index = function (i) {
				if ( i > 25) {
					return this((i/26)-1) + this(i%26);
				}
				return 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.substr(i, 1);
			};
			return $(template.replace(new RegExp( "\\{0\\}", "g" ), i)
						     .replace(new RegExp( "\\{A\\}", "g" ), index(i-1))
						     .replace(new RegExp( "\\{a\\}", "g" ), index(i-1).toLowerCase()));
		},
		
		length: function (element) {
			return this.items(element).length;
		}
	});
	
	$.fn.field.controls = $.extend($.fn.field.controls, { 'DynInput': DynInput, 'Place': PlaceInput } );
	
	$('[data-control=DynInput],[data-control=Place]').field();
	
	$('body').on('click.field.data-api', '[data-toggle=dyninput-add]', function(e) {
		var target = $($(this).is('a') ? $(this).attr('href') : $(this).data('target'));
		
		target.field('get').add(target);
		return false;
	});
	
	$('body').on('click.field.data-api', '[data-toggle=dyninput-remove]', function(e) {
		var target = $($(this).is('a') ? $(this).attr('href') : $(this).data('target'));
		
		target.field('get').remove(target, $(this).data('index'));
		return false;
	});
	
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
	
	$('body').on('click', '#user-form #agency-table [data-storage]', function(e) {
		$('#user-form #agency-id').seek('lookup');
	});
	
});
  

