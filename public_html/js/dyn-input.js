$(function() {
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
	
	$.fn.field.controls = $.extend($.fn.field.controls, { 'DynInput': DynInput } );
	
	$('[data-control=DynInput]').field();
	
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
});