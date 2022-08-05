$(function() {
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
	
	$.fn.field.controls = $.extend($.fn.field.controls, { 'Place': PlaceInput } );
	
	$('[data-control=Place]').field();
});