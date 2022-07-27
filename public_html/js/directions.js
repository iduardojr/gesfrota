$(function() {
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
});