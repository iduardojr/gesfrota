(function($) {
	
	/* TOOLTIP DATA-API
	* ============== */
	$('body').on('mouseenter.tooltip.data-api, mouseleave.tooltip.data-api', '[rel=tooltip],[rel=popover]', function( e ) {
		var $this = $(e.currentTarget), 
			type = $this.attr('rel'),
			widget = $this.data(type);
		
		if (! widget ) {
			$this[type]({trigger: 'manual'});
			widget = $this.data(type);
		} 
		widget[e.type == 'mouseenter' ? 'enter' : 'leave'](e);
		return false;
	});
	
}(jQuery));

(function($) {
	
	$.fn.typeahead.Constructor.prototype.select = function () {
        var val = this.$menu.find('.active').data('value');
        this.$element.val(this.updater(val))
        			 .change();
        return this.hide();
    };
    
    $.fn.typeahead.Constructor.prototype.render = function ( items ) {
		var that = this;

		items = $(items).map(function (i, item) {
			i = $(that.options.item).data('value', item);
			i.find('a').html(that.highlighter(item));
        	return i[0];
		});

		items.first().addClass('active');
		this.$menu.html(items);
		return this;
    };

    $.fn.typeahead.Constructor.prototype.lookup = function ( event ) {
        var items;
        this.query = this.$element.val();

        if ( this.query.length < this.options.minLength ) {
        	return this.shown ? this.hide() : this;
        }
        items = $.isFunction(this.source) ? this.source(this.query, $.proxy(this.process, this)) : this.source;
        return items ? this.process(items) : this;
   };
   
}(jQuery));

(function($) {
	
	$.fn.datepicker.Constructor.prototype.setValue = function() {
		var formatted = this.getFormattedDate();
		if (!this.isInput) {
			if (this.component){
				this.element.find('input').val(formatted);
			}
			if ( this.element.data('target') ) {
				$(this.element.data('target')).field('value', formatted);
			} else {
				this.element.data('date', formatted);
			}
		} else {
			this.element.val(formatted);
		}
	};
	
	$.fn.datepicker.Constructor.prototype.update = function(){
		var date, fromArgs = false;
		if(arguments && arguments.length && (typeof arguments[0] === 'string' || arguments[0] instanceof Date)) {
			date = arguments[0];
			fromArgs = true;
		} else {
			if ( this.isInput ) {
				date = this.element.val();
			} else {
				if ( this.element.data('target') ) {
					date = $(this.element.data('target')).field('value');
				} else {
					date = this.element.data('date') || this.element.find('input').val();
				}
			}
		}

		this.date = $.fn.datepicker.DPGlobal.parseDate(date, this.format, this.language);

		if(fromArgs) this.setValue();

		if (this.date < this.startDate) {
			this.viewDate = new Date(this.startDate);
		} else if (this.date > this.endDate) {
			this.viewDate = new Date(this.endDate);
		} else {
			this.viewDate = new Date(this.date);
		}
		this.fill();
	},
	
	$.fn.timepicker.Constructor.prototype._init = function() {
	      var self = this;
	      
	      if ( this.$element.is(':text') ){
	          this.$element.on({
	            'focus.timepicker': $.proxy(this.highlightUnit, this),
	            'click.timepicker': $.proxy(this.showWidget, this),
	            'keydown.timepicker': $.proxy(this.elementKeydown, this),
	          });
	      } else {
	    	  this.$element.on({
	            'click.timepicker': $.proxy(this.showWidget, this),
	          });
	      }

	      if (this.template !== false) {
	        this.$widget = $(this.getTemplate()).appendTo('body').on('click', $.proxy(this.widgetClick, this));
	      } else {
	        this.$widget = false;
	      }

	      if (this.showInputs && this.$widget !== false) {
	          this.$widget.find('input').each(function() {
	            $(this).on({
	              'click.timepicker': function() { $(this).select(); },
	              'keydown.timepicker': $.proxy(self.widgetKeydown, self)
	            });
	          });
	      }

	      this.setDefaultTime(this.defaultTime);
	};
	
	$.fn.timepicker.Constructor.prototype.showWidget = function() {
		if (this.isOpen) {
			return;
        }

        var self = this;
        $(document).on('mousedown.timepicker', function (e) {
          // Clicked outside the timepicker, hide it
          if ($(e.target).closest('.bootstrap-timepicker-widget').length === 0) {
            self.hideWidget();
          }
        });

        this.$element.trigger({
          'type': 'show.timepicker',
          'time': {
              'value': this.getTime(),
              'hours': this.hour,
              'minutes': this.minute,
              'seconds': this.second,
              'meridian': this.meridian
           }
        });

        this.updateFromElementVal();

        if (this.template === 'modal') {
        	this.$widget.modal('show').on('hidden', $.proxy(this.hideWidget, this));
        } else {
        	  if (this.isOpen === false) {
        		  var zIndex = parseInt(this.$element.parents().filter(function() {
    								return $(this).css('z-index') != 'auto';
    							}).first().css('z-index'))+10;
        		  var offset = this.$element.offset();
        		  var height = this.$element.outerHeight(true);
        		  this.$widget.css({
        			  top: offset.top + height,
        			  left: offset.left,
        			  zIndex: zIndex,
        			  display: 'block'
        		  });
        	  }
        }

        this.isOpen = true;
	};
	
	$.fn.timepicker.Constructor.prototype.hideWidget = function() {
		if (this.isOpen === false) {
			return;
	    }

		if (this.showInputs) {
			this.updateFromWidgetInputs();
		}

        this.$element.trigger({
	          'type': 'hide.timepicker',
	          'time': {
	              'value': this.getTime(),
	              'hours': this.hour,
	              'minutes': this.minute,
	              'seconds': this.second,
	              'meridian': this.meridian
	           }
        });

        if (this.template === 'modal') {
	         this.$widget.modal('hide');
        } else {
	         this.$widget.css('display', 'none');
        }

	    $(document).off('mousedown.timepicker');

	    this.isOpen = false;
	};
	
	$.fn.timepicker.Constructor.prototype.updateElement = function() {
		if ( this.$element.is(':text') ) {
			this.$element.val(this.getTime()).change();
		} else {
			if ( this.$element.data('target') ) {
				$(this.$element.data('target')).field('value', this.getTime());
			}
		}
	};
	
	$.fn.timepicker.Constructor.prototype.updateFromElementVal = function() {
		var val = null;
		
		if ( this.$element.is(':text') ) {
			val = this.$element.val();
		} else {
			if ( this.$element.data('target') ) {
				val = $(this.$element.data('target')).field('value');
			}
		}

		if ( val ) {
			this.setTime(val);
		}
	};
	
	$.fn.timepicker.Constructor.prototype.setDefaultTime = function( defaultTime ) {
	      if (!this.$element.val() && !this.$element.data('target') ) {
	        if (defaultTime === 'current') {
	          var dTime = new Date(),
	            hours = dTime.getHours(),
	            minutes = Math.floor(dTime.getMinutes() / this.minuteStep) * this.minuteStep,
	            seconds = Math.floor(dTime.getSeconds() / this.secondStep) * this.secondStep,
	            meridian = 'AM';

	          if (this.showMeridian) {
	            if (hours === 0) {
	              hours = 12;
	            } else if (hours >= 12) {
	              if (hours > 12) {
	                hours = hours - 12;
	              }
	              meridian = 'PM';
	            } else {
	              meridian = 'AM';
	            }
	          }

	          this.hour = hours;
	          this.minute = minutes;
	          this.second = seconds;
	          this.meridian = meridian;

	          this.update();

	        } else if (defaultTime === false) {
	          this.hour = 0;
	          this.minute = 0;
	          this.second = 0;
	          this.meridian = 'AM';
	        } else {
	          this.setTime(defaultTime);
	        }
	      } else {
	        this.updateFromElementVal();
	      }
	};
	
	$.fn.colorpicker.Constructor.prototype.update = function() {
		var val = null;
		if ( this.element.is(':text') ) {
			val = this.element.val();
		} else if ( this.element.data('target') ) {
			val = $(this.element.data('target')).field('value');
		} else {
			val = this.element.data('color');
		}
		this.setValue(val + '');
	};
	
	$.fn.colorpicker.Constructor.prototype.hide = function(){
		this.picker.hide();
		$(window).off('resize', this.place);
		if (!this.isInput) {
			$(document).off({
				'mousedown': this.hide
			});
			if ( this.element.data('target') ) {
				$(this.element.data('target')).field('value', this.format.call(this));
			}
			this.element.data('color', this.format.call(this));
		} else {
			this.element.prop('value', this.format.call(this));
		}
		this.element.trigger({
			type: 'hide',
			color: this.color
		});
	};
	
	$.fn.colorpicker.Constructor.prototype.show = function(e) {
		this.picker.show();
		this.height = this.component ? this.component.outerHeight() : this.element.outerHeight();
		this.place();
		$(window).on('resize', $.proxy(this.place, this));
		if (!this.isInput) {
			if (e) {
				e.stopPropagation();
				e.preventDefault();
			}
		}
		$(document).on({
			'mousedown': $.proxy(this.hide, this)
		});
		this.element.trigger({
			type: 'show',
			color: this.color
		});
		this.update();
	};
	
	/* DATA-API
	 * ============== */
	$(function () {
	
		$('.datepicker').each(function(i, item) {
			$(item).datepicker($(item).data('date-options'));
		});
		
		var event = function( e ) {
			var $this = $(e.currentTarget);
			if ( ! $this.data('datepicker') ) {
				$this.datepicker($this.data('date-options'));
				$this.datepicker('show');
			}
			return false;
		};
		$('body').on('click.datepicker.data-api', '[data-toggle=datepicker]', event);
		$('body').on('focus.datepicker.data-api', '[data-provide=datepicker]', event);
		
		event = function( e ) {
			var $this = $(e.currentTarget);
			if ( ! $this.data('timepicker') ) {
				$this.timepicker($this.data('time-options'));
				$this.timepicker('showWidget');
			}
			return false;
		};
		$('body').on('click.timepicker.data-api', '[data-toggle=timepicker]', event);
		$('body').on('focus.timepicker.data-api', '[data-provide=timepicker]', event);
		
		event = function( e ) {
			var $this = $(e.currentTarget);
			if ( ! $this.data('colorpicker') ) {
				$this.colorpicker();
				$this.colorpicker('show');
			}
			return false;
		};
		$('body').on('click.colorpicker.data-api', '[data-toggle=colorpicker]', event);
		$('body').on('focus.colorpicker.data-api', '[data-provide=colorpicker]', event);
	});
	
}(jQuery));

(function($) {
	
	$(document).on('click.modal.data-api', '[data-toggle="modal-open"]', function (e) {
		var $this = $(e.currentTarget),
	      	$target = $($this.attr('data-target'));
		
	    $target.data('subject', $this)
	    	   .modal('toggle')
		       .one('hide', function () { $this.focus(); });
	    
	    return false;
	});
	
	$(document).on('click.modal.data-api', '[data-toggle="modal-load"]', function (e) {
		var $this = $(e.currentTarget),
			options = {};
		
		options.remote = $this.attr('href');
		options.disabled = $this.closest('.disabled,:disabled').size() > 0;
		options.ajax = true;
		options.response = $this.data('response');
		options.target = '#' + $this.attr('target');
		options.success = function( event, widget, data ) {
			var event = $.Event('update'),
				$target = $(widget.options.target);
			$target.trigger(event, data, $this);
			if ( ! event.isDefaultPrevented() ) {
				if ( widget.options.response == 'html' ){
					$('.modal-body', $target).html(data);
				} else {
					$.each( data, function( key, value ) {
						var el = $('#' + key );
						var event = $.Event('update');
						el.trigger(event, value, $this);
						if ( ! event.isDefaultPrevented() ) {
							if ( el.is(':input') ) {
								el.val(value);
							} else {
								el.html(value);
							}
						}
					});
				}
			}
			
			$target.modal('show')
    	       	   .one('hide', function () { $this.focus(); });
		};
		$this.action('option', options);
		$this.action('toggle');
	    return false;
	});
	
	$(document).on('click.modal.data-api', '[data-dismiss="confirm"]', function () {
		var $this = $(this),
	      	$target = $($this.closest('.modal'));
	  
		if ( $target.data('subject') ) {
			$target.data('subject').trigger('toggle');
			$target.removeData('subject');
		}
	    $target.modal('hide');
	    
	    return false;
	});
	
}(jQuery));

(function($) {
	
	/* CORE CLASS DEFINITION
	 * ====================== */
	Core = {
		name: '',
		options: {},
		element: null,
		
		_constructor: function( element, options ) {
			this.element = $(element);
			this.options = $.extend( true, {}, this.options, this.element.data(), options );
			this._create();
			this._trigger("create");
		},
		
		_create: function() {
			
		},
		
		_trigger: function( type, data ) {
			var callback = this.options[type],
				event = $.Event(type + this.name),
				args = $.merge([event], Array.prototype.slice.call( arguments, 1 ));
			
			this.element.trigger( event, data );
			
			return !( $.isFunction(callback) &&
				callback.apply(this.element[0], args) === false ||
				event.isDefaultPrevented() );
		},
		
		_setOption: function(key, value) {
			this.options[key] = value;
		},

		widget: function() {
			return this.element;
		},
		
		_fn: function( fn, args ) {
			return this[fn].apply( this, args );
		},
		
		option: function( key, value ) {
			if ( arguments.length === 0 ) {
				return this.options;
			}
			if  (typeof key === "string" ) {
				if ( value === undefined ) {
					return this.options[ key ];
				}
				this._setOption(key, value);
			} else {
				$.each( key, $.proxy(function (i, v) {
					this._setOption(i, v);
				}, this));
			}
			return this;
		}
	};
	
	
	// CREATE PLUGINS
	$.plugin = function( name, object, subclass, defaults ) {
		
		if ( arguments.length <= 3 ) {
			defaults = subclass;
			subclass = {};
		} 
		
		var plugin = function( element, options ) {
			this._constructor(element, options);
		};
		plugin.prototype = $.extend(true, {}, Core, subclass, object);
		
		$.fn[name] = function ( option ) {
			
			var isMethodCall = typeof option === 'string',
				returnValue = this,
				args = Array.prototype.slice.call( arguments, 1 ),
				options = $.extend({}, $.fn[name].defaults, typeof option == 'object' && option);
			
			if ( !isMethodCall || ( isMethodCall && option.charAt( 0 ) != "_") ) {
				
				this.each(function () {
					var widget = $(this).data(name),
						methodValue = widget;
					
				    if ( !widget ) {
				    	widget = new plugin(this, options);
				    	widget.name = name.toLowerCase();
				    	$(this).data(name, widget);
				    }
				    
				    if ( isMethodCall ) {
				    	methodValue = widget._fn(option, args);
				    } 
				    
				    if ( methodValue !== widget && methodValue !== undefined ) {
				    	returnValue = methodValue;
				    	return false;
				    }
				    
				});
			}
			
			return returnValue;
		};
		
		$.fn[name].constructor = plugin;
		$.fn[name].defaults = defaults || {};
	};
	
}(jQuery));

(function($) {
	
	/* DEFAULT CLASS DEFINITION
	 * ====================== */
	var Default = function( options ){
		$.extend(this.options, options);
	};
	
	Default.prototype = {
		options: {},
		
		toggle: function() {
			window.location = this.options.remote;
		}
	};
	
	/* WINDOWS CLASS DEFINITION
	 * ====================== */
	var WINDOWS_LAST_ID = 0;
	var Windows = function( options ){
		this.options = $.extend({}, $.fn.action.defaults, options);
		WINDOWS_LAST_ID++;
		this.guid = WINDOWS_LAST_ID;
	};
	
	Windows.prototype = {
		guid: 0,
		options: null, 	
		target: null,
		
		toggle: function() {
			this.open();
		},
		
		open: function() {
			if ( this.options.features ) {
				this.target = window.open(this.options.remote, 'WINDOWS_' + this.guid, this.options.features);
			} else {
				this.target = window.open(this.options.remote, '_blank');
			}
		},
		
		close: function() {
			if ( this.target ) {
				this.target.close();
				this.target = null;
			}
		}
		
	};
	
	/* AJAX CLASS DEFINITION
	 * ======================= */
	var Ajax = function( options ){
		this.options = $.extend({}, $.fn.action.defaults, options);
	};
	
	Ajax.prototype = {
		options: null,
		request: null,
		
		toggle: function() {
			this.abort();
			this.loading();
			this.request = $.ajax( {
				url: this.options.remote, 
				success: $.proxy( function( data, textStatus, jqXHR) {
					this.process(data, textStatus, jqXHR);
					this.loaded();
				}, this ),
				dataType: this.options.response
			});
		},
	
		abort: function() {
			if ( this.request ) {
				this.request.abort();
				this.loaded();
			}
		},
		
		loading: function() {
			if ( $.isFunction(this.options.loading) ) {
				this.options.loading.call(this);
			}
		},
		
		loaded: function() {
			if ( $.isFunction(this.options.loaded) ) {
				this.options.loaded.call(this);
			}
		},
		
		process: function() {
			if ( $.isFunction(this.options.process) ) {
				this.options.process.apply(this, arguments);
			}
		}
		
	};
	
	/* ACTION PLUGIN DEFINITION
	 * ======================= */
	var Action = {
			
		Html: 'html',
		Json: 'json',
		Text: 'text',
		
		strategy: null,
		
		_fn: function( fn, args ) {
			switch ( fn ) {
				case 'toggle':
				case 'option':
				case 'widget':
					return this[fn].apply( this, args );
					break;
				default:
					return this.strategy[fn].apply( this.strategy, args );
					break;
					
			}
		},
		
		toggle: function() {
			if ( ! this.options.disabled ) {
				this.strategy.toggle();
			}
		},
		
		_setOption: function( key, value ) {
			this.options[key] = value;
			switch ( key ) {
				case 'success':
				case 'loading':
				case 'loaded':
					break;
				case 'ajax':
				case 'target': 
					var options = $.extend({}, this.options, {
						process: $.proxy( function( data, textStatus, jqXHR ) {
							this._trigger('success', this, data, textStatus, jqXHR );
						}, this),
						loading: $.proxy( function() {
							this._trigger('loading', this);
						}, this),
						loaded: $.proxy(function() {
							this._trigger('loaded', this);
						}, this)
					});
					if ( this.options.ajax ) {
						this.strategy = new Ajax(options);
					} else if ( this.options.target ){
						this.strategy = new Windows(options);
					} else {
						this.strategy = new Default(options);
					}
					break;
				default:
					if ( this.strategy ) {
						this.strategy.options[key] = value;
					}
					break;
			}
		}
		
	};
	
	/* ACTION PLUGIN DEFINITION
	 * ======================= */
	$.plugin('action', Action, {
		remote: '',
		target: '',
		features: '',
		disabled: false,
		ajax: false, 
		response: 'html',
		success: function( e, $this, data, textStatus, jqXHR ){
			var event = $.Event('update');
			$($this.options.target).trigger(event, data, $this);
			if ( ! event.isDefaultPrevented() ) {
				if ( $this.options.response != $this.Json ){
					$($this.options.target).html(data);
				} else {
					$.each( data, function( key, value ) {
						var el = $('#' + key );
						var event = $.Event('update');
						el.trigger(event, value, $this);
						if ( ! event.isDefaultPrevented() ) {
							if ( el.is(':input') ) {
								el.val(value);
							} else {
								el.html(value);
							};
						};
					});
					
				};
			};
		},
		loading: null,
		loaded: null
	});
	
	/* ACTION DATA-API
	* ============== */
	var event = function ( e ) {
		var $this = $(e.currentTarget), 
		options = {};
	
		options.remote = $this.attr('href');
		options.disabled = $this.closest('.disabled,:disabled').size() > 0;
		options.ajax = false;
		
		if ( $this.attr('target') ) {
			if ( $this.attr('target') == '_blank' ) {
				options.target = '_blank';
				options.features = $this.data('features');
			} else {
				options.ajax = true;
				options.response = $this.data('response');
				options.target = '#' + $this.attr('target');
			}
		}
		$this.action('option', options);
		
		$this.action('toggle');
		return false;
	};
	
	$('body').on('click.action.data-api', 'a:not([href^=#],[data-toggle])', event);
	$('body').on('toggle.action.data-api', 'a:not([href^=#])', event);
	
}(jQuery));

(function($) {
	
	/* PAGER CLASS DEFINITION
	 * ====================== */
	var Pager = {
		
		page: function( page ) {
			var $throw = $('.current-change a', this.element).clone(true),
				href = $throw.attr('href'),
				total = $('.total', this.element).val();
			
			page = page == undefined || page == '' ? 0 : parseInt(page);
			if ( page < 1 ) {
				page = 1;
			}
			if ( page > total ){
				page = total;
			}
			
			$throw.attr('href', href.replace(/#CURRENT#/, page));
			this.element.append($throw);
			$throw.trigger('throw');
			$throw.remove();
			$('.current', this.element).val(page);
			$('.page-first,.page-prev', this.element)[page == 1 ? 'addClass' : 'removeClass']('disabled');
			$('.page-next,.page-last', this.element)[page == total ? 'addClass' : 'removeClass']('disabled');
		} 
	};
	
	/* PAGER PLUGIN DEFINITION
	 * ======================= */
	$.plugin('pager', Pager);
	
	/* PAGER DATA-API
	* ============== */
	$('body').on('change.pagination.data-api', '.pager-bar .current', function( e ) {
		var $this = $(e.currentTarget);
		$this.parents('.pager-bar').pager('page', $this.val());
	});
	
}(jQuery));

(function($) {
	
	/* SEEK CLASS DEFINITION
	 * ===================== */
	Seek = {
			
		request: null,
		
		abort: function(){
			if ( this.request ) {
				this.request.abort();
				this.request = null;
				this._trigger('loaded', this);
			}
		},
		
		search: function() {
			this.abort();
			if ( this.options.query.length > 0 && this.options.remote ) {
				this._trigger('loading', this);
				this.request = $.getJSON( this.options.remote, {'query': this.options.query }, $.proxy( function( result ) {
					this.request = null;
					this._trigger('process', { ui: this, data: result });
					this._trigger('loaded', { ui: this, data: result });
				}, this));
			}
		}
		
	};
	
	/* RESEARCH CLASS DEFINITION
	 * ========================= */
	Research = {
		
		search: function() {
			this.abort();
			if ( this.options.remote ) {
				this._trigger('loading', this);
				this.request = $.get( this.options.remote, {'query': this.options.query }, $.proxy( function( result ) {
					this.request = null;
					$('.modal-body', this.options.target).empty();
					$('.modal-body', this.options.target).append(result);
					this.options.target.modal('show')
	       	   		  		  		   .one('hide', $.proxy(function () { this.element.focus(); }, this));
					this._trigger('loaded', { ui: this, data: result });
				}, this));
			}
		}
		
	};
	
	/* OUTPUT, INPUT PLUGINS DEFINITION
	 * ======================= */
	$.plugin('xseek', Seek, {
		process: function ( e, response ) {
			$.each( response.data, function( key, value ) {
				var el = $('#' + key );
				var event = $.Event('update');
				el.trigger(event, value);
				if ( ! event.isDefaultPrevented() ) {
					if ( el.is(':input') ) {
						el.val(value);
					} else {
						el.html(value);
					};
				};
			});
		}
	});
	$.plugin('xresearch', Research, Seek, {});
	
   /* DATA-API
	* ============== */
	$(function () {
		
		$('body').on('click.search.data-api', '[data-toggle=seek]', function( e ) {
			var $this = $(e.currentTarget),
				input = $($this.data('input-query'));
			
			if ( ! $this.data('xseek') ) {
				$this.xseek({ remote: $this.data('remote'),
							  loading: function (e, ui ) {
								  input.addClass('loading');
							  },
							  loaded: function(e, ui) {
								  input.removeClass('loading');
							  }});
			}
			$this.xseek('option', 'query', input.field('value'));
			$this.xseek('search');
			return false;
		});
		
		$('body').on('click.search.data-api', '[data-toggle=research]', function( e ) {
			var $this = $(e.currentTarget),
				input = $($this.data('input-query'));
			
			if ( ! $this.data('xresearch') ) {
				$this.xresearch({ 
					remote: $this.data('remote'),
					target: $($this.data('target')),
					loading: function (e, ui ) {
						input.addClass('loading');
					},
					loaded: function(e, ui) {
						input.removeClass('loading');
					}
				});
			}
			$this.xresearch('option', 'query', input.is('[readonly]') ? '' : input.field('value'));
			$this.xresearch('search');
			return false;
		});
		
	});
	
}(jQuery));

(function($) {
	
	/* SUGGEST CLASS DEFINITION
	 * ====================== */
	var Suggest = function (element, options) {
	    this.$element = $(element);
	    this.options = $.extend({}, $.fn.suggest.defaults, options);
	    this.$menu = $(this.options.menu);
	    this.shown = false;
	    this.listen();
	};
	
	Suggest.prototype = $.extend({}, $.fn.typeahead.Constructor.prototype, {
		name: 'suggest',
		timer: null,
		request: null,
	    
	    lookup: function ( event) {
	    	this.abort();
	    	this.query = this.$element.val();
	        if (!this.query || this.query.length < this.options.minLength) {
	        	return this.shown ? this.hide() : this;
	        }
	        this.timer = setTimeout( $.proxy(function() {
	        	this.request = this.connect();
	        }, this), this.options.delay );
	    },
	    
	    abort: function() {
	    	clearTimeout( this.timer );
	    	if ( this.request ) {
    			this.request.abort();
    			this.request = null;
    		}
	    },
	    
	    connect: function() {
    		this.$element.addClass('loading');
			return $.getJSON(this.options.source, { 'query': this.query, 'items': this.options.items }, $.proxy(function( data ) {
				this.request = null;
				this.process(data);
				this.$element.removeClass('loading');
			}, this));
    		
    	},
    	
    	updater: function( item ) {
    		this.trigger('select', item);
    		return typeof item == 'string' ? item : item.label;
    	},
    	
    	highlighter: function ( item ) {
    		var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&'),
    			re = new RegExp('(' + query + ')', 'ig'),
    			label = typeof item == 'string' ? item : item.label;

    	    return label.replace(re, function ( $1, match ) {
    	    	return '<strong>' + match + '</strong>';
    	    });
    	},
    	
    	matcher: function ( item ) {
    		var label = typeof item == 'string' ? item : item.label;
    	   	return ~label.toLowerCase().indexOf(this.query.toLowerCase());
    	},
    	
    	sorter: function ( items ) {
    		var beginswith = [],
	        	caseSensitive = [],
	        	caseInsensitive = [],
	        	item, label;

    		 while ( item = items.shift() ) {
    			
    			label = typeof item == 'string' ? item : item.label;

    			if ( ! label.toLowerCase().indexOf(this.query.toLowerCase()) ) {
    				beginswith.push(item);
    			} else if (~label.indexOf(this.query)) {
    				caseSensitive.push(item);
    			} else {
    				caseInsensitive.push(item);
    			};
    			
    		}

    		return beginswith.concat(caseSensitive, caseInsensitive);
	    },
    	
    	trigger: function( type, data ) {
			var callback = this.options[type],
				event = $.Event(type + this.name),
				args = $.merge([event], Array.prototype.slice.call( arguments, 1 ));
			
			this.$element.trigger( event, data );
			
			return !( $.isFunction(callback) &&
				callback.apply(this.$element[0], args) === false ||
				event.isDefaultPrevented() );
		}
	});
	
	/* SUGGEST PLUGINS DEFINITION
	 * ======================= */
	
	$.fn.suggest = function ( option ) {
		return this.each(function () {
			var $this = $(this),
				data = $this.data('suggest'),
		        options = typeof option == 'object' && option;
			
		      if ( ! data )  {
		    	  $this.data('suggest', (data = new Suggest(this, options)));
		      }
		      if ( typeof option == 'string' ) {
		    	  data[option]();
		      };
		 });
	};

	$.fn.suggest.defaults = $.extend({}, $.fn.typeahead.defaults, { 
		source: '', 
		delay: 300, 
		select: function( e, data, $this ) {
			if ( $.isPlainObject(data) && $.isPlainObject(data.value) ) {
				$.each( data.value, function( key, value ) {
					var el = $('#' + key );
					var event = $.Event('update');
					el.trigger(event, value, $this);
					if ( ! event.isDefaultPrevented() ) {
						if ( el.is(':input,label,fieldset') ) {
							el.val(value);
						} else {
							el.html(value);
						}
					}
				});
			}
		}
	});
	$.fn.suggest.Constructor = Suggest;
	
   /* SUGGEST DATA-API
	* ============== */
	$(function () {
		
		$('body').on('focus.suggest.data-api', '[data-provide=suggest]', function ( e ) {
			var $this = $(e.currentTarget);
			if ($this.data('suggest')) return;
		    e.preventDefault();
		    $this.suggest($this.data());
		});
		
	});
}(jQuery));

(function($) {
	
	/* TABLE CLASS DEFINITION
	 * ====================== */
	var Table = {
		abstract: false,
		selected: false,
		
		select: function (){
			this.selected = true;
			this._trigger('select');
			$(':checkbox:enabled', this.element).attr('checked', 'checked');
		},
		
		unselect: function(){
			this.selected = false;
			this._trigger('unselect');
			$(':checkbox:enabled', this.element).removeAttr('checked');
		},
		
		toggle: function() {
			this.refresh();
			if ( ! this.selected ) {
				this.select();
			} else {
				this.unselect();
			}
		},
		
		refresh: function () {
			var enabled = $('tbody :checkbox:enabled', this.element).size(),
				checked = $('tbody :checkbox:checked', this.element).size();
			if ( enabled == checked ) {
				$('thead :checkbox', this.element).attr('checked', 'checked');
				this.selected = true;
			} else {
				$('thead :checkbox', this.element).removeAttr('checked');
				this.selected = false;
			}
		}
		
	};
	
	/* TABLE PLUGIN DEFINITION
	 * ======================= */
	$.plugin('table', Table);
	
   /* TABLE DATA-API
	* ============== */
	$(function () {
		$(document).on('click.table.data-api', '.table thead :checkbox', function( e ){
			var $this = $(e.currentTarget).parents('.table');
			 	$this.table('toggle');
		});
		$(document).on('click.table.data-api', '.table tbody :checkbox:enabled', function( e ){
			var $this = $(e.currentTarget).parents('.table');
				$this.table('refresh');
		});
	});
	
}(jQuery));

(function($) {
	
	/* DEFAULT CLASS DEFINITION
	 * ====================== */
	var Default = {
		
		create: function( element ) {
			
		},
	
		set: function ( element, value ) {
			element[element.is(':input') ? 'val' : 'html'](value);
		},
		
		get: function( element ) {
			return element[element.is(':input') ? 'val' : 'html']();
		},
		
		disable: function ( element ) {
			element[element.is(':input') ? 'attr' : 'addClass']('disabled', 'disabled');
		},
		
		enable: function( element ) {
			element[element.is(':input') ? 'removeAttr' : 'removeClass']('disabled');
		},
		
		defaultValue: function( element ) {
			return element[0].defaultValue;
		}
	};
	
	/* ENTRY CLASS DEFINITION
	 * ====================== */
	var Entry = $.extend({}, Default, {
		
		disable: function ( element ) {
			if ( element.closest('.input-prepend,.input-append').size() ) {
				element.siblings('.btn').each(function(i, item){
					$(item)[$(item).is('a') ? 'addClass' : 'attr']('disabled', 'disabled');
				});
			}
			element.attr('disabled', 'disabled');
		},
		
		enable: function( element ) {
			if ( element.closest('.input-prepend,.input-append').size() ) {
				element.siblings('.btn').each(function(i, item){
					$(item)[$(item).is('a') ? 'removeClass' : 'removeAttr']('disabled');
				});
			}
			element.removeAttr('disabled');
		}
	});
	
	/* RICH TEXT CLASS DEFINITION
	 * ====================== */
	var RichText = $.extend({}, Default, {
		
		create: function( element ) {
			element.fck({
				width: element.innerWidth(),
				height: element.innerHeight(),
				toolbar: element.attr('data-richtext-type')
			});
			var loaded = false;
			var interval = setInterval( $.proxy( function() {
				var instance = this.instance(element);
				if ( loaded ) {
					element.data('stateButton', instance.EditorWindow.parent.FCKToolbarButton.prototype.RefreshState);
					element.data('stateSpecialCombo', instance.EditorWindow.parent.FCKToolbarSpecialCombo.prototype.RefreshState);
					if ( element.is(':disabled,[readonly]') ) {
						this.disable(element);
					}
					clearInterval(interval);
				}
				if ( instance ) {
					loaded = true;
				}
			}, this), 500);
		},
		
		instance: function( element ){
			try {
				return FCKeditorAPI.GetInstance(element.attr('id'));	
			} catch ( e ){
				return null;
			}
			
		},
		
		set: function( element, value ){
			element.val(value);
			this.instance(element).SetData(value);
		},
		
		get: function( element ){
			return this.instance(element).GetXHTML(true);
		},
		
		disable: function ( element ) {
			element.attr('disabled', 'disabled');
			
			// Disabled Area
			this.instance(element).EditorDocument.body.disabled = true;
			this.instance(element).EditorDocument.designMode = "off";
			
			// Disabled Toolbar
			this.instance(element).EditorWindow.parent.FCK.ToolbarSet.Disable();
			this.instance(element).EditorWindow.parent.FCKToolbarButton.prototype.RefreshState = function(){return false;};
			this.instance(element).EditorWindow.parent.FCKToolbarSpecialCombo.prototype.RefreshState = function(){return false;};
			
			// Update
			this.instance(element).EditorWindow.parent.FCK.ToolbarSet.RefreshModeState();
		},
		
		enable: function ( element ) {
			element.removeAttr('disabled');
			
			// Enabled Area
			this.instance(element).EditorDocument.body.disabled = false;
			this.instance(element).EditorDocument.designMode = "on";
			
			// Enabled Toolbar
			this.instance(element).EditorWindow.parent.FCK.ToolbarSet.Enable();
			this.instance(element).EditorWindow.parent.FCKToolbarButton.prototype.RefreshState = element.data('stateButton');
			this.instance(element).EditorWindow.parent.FCKToolbarSpecialCombo.prototype.RefreshState = element.data('stateSpecialCombo');
			
			// Update
			this.instance(element).EditorWindow.parent.FCK.ToolbarSet.RefreshModeState();
		}
	});
	
	/* CHECKABLE CLASS DEFINITION
	 * ====================== */
	var Checkable = $.extend({}, Default, {
		
		set: function ( element, value ) {
			if ( typeof value != 'boolean' ){
				value = parseInt(value) > 0 ? true : false;
			}
			element.prop('checked', value);
		},
		
		get: function ( element ) {
			return element.is(':checked');
		},
		
		disable: function ( element ) {
			element.attr('disabled', 'disabled')
				   .parent()
				   .addClass('disabled');
		},
		
		enable: function ( element ) {
			element.removeAttr('disabled')
			   	   .parent()
			   	   .removeClass('disabled');
		},
		
		defaultValue: function( element ) {
			return element[0].defaultChecked;
		}
	});
	
	/* CHECKABLE LIST CLASS DEFINITION
	 * ====================== */
	var CheckableList = $.extend({}, Default, {
			
		set: function( element, value ){
			if ( typeof value != 'object' ) {
				value = [value];
			}
			element.find(':input')
				   .each( function (i, el) {
					  if ( $.inArray($(el).val(), value + '' ) >= 0 ) {
						  $(el).attr('checked', 'checked');
					  } else {
						  $(el).removeAttr('checked');
					  }
				   });
		},
		
		get: function( element ) { 
			var value = $(':checked', element).map(function(){
				return $(this).val();
			}).get();
			return element.find('.checkbox').size() > 0 ? value : value.length > 0 ? value[0] : null;
		},
		
		enable: function( element ) {
			element.find(':input').removeAttr('disabled');
		},
		
		disable: function( element ) {
			element.find(':input').attr('disabled', 'disabled');
		},
		
		defaultValue: function( element ) {
			var value = []; 
			element.find(':input').each( function(i, item) {
				if ( item.defaultChecked ) {
					value.push(item.value);
				}
			});
			return value;
		}
		
	});
	
	/* COMBOBOX CLASS DEFINITION
	 * ====================== */
	var ComboBox = $.extend({}, Default, {
		
		defaultValue: function( element ) {
			var value = ''; 
			element.children()
				   .each( function(i, item){
						if ( item.defaultSelected ) {
							value = item.value;
							return false;
						}
				   });
			return value;
		}
	});
	
	/* XCOMBOBOX CLASS DEFINITION
	 * ====================== */
	var XComboBox = $.extend({}, Default, {
		
		create: function ( element ) {
			var hidden = element.find(':hidden'),
				text = element.find(':text'),
				button = element.find(':button'),
				that = this;
			
			text.typeahead($.extend({}, text.data(), {
				minLength: 0,
				matcher: function ( item ) {
					return ~item.label.toLowerCase().indexOf(this.query.toLowerCase());
				},
				sorter: function ( items ) {
					var beginswith = [],
				        caseSensitive = [],
				        caseInsensitive = [],
				        item;

					while ( item = items.shift() ) {
				    	if ( !item.label.toLowerCase().indexOf(this.query.toLowerCase()) ) {
				    		beginswith.push(item);
				    	} else if ( ~item.label.indexOf(this.query)) {
				    		caseSensitive.push(item);
				    	} else {
				    		caseInsensitive.push(item);
				    	}
					}

					return beginswith.concat(caseSensitive, caseInsensitive);
				},
				updater: function ( item ){
					that.set(element, item.value);
					return item.label;
				},
				highlighter: function ( item ) {
					var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
					return item.label.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
						return '<strong>' + match + '</strong>';
					});
				}
			}));
			
			button.on('click', function() {
				var icon = $('i', this);
				if ( icon.is('.icon-remove') ) {
					that.set(element, '');
				} else {
					text.focus();
					text.data('typeahead').lookup();
				}
				return false;
			});
		}, 
		
		set: function ( element, value ) {
			element.find(':hidden, :text').val('');
			element.find(':button i').removeClass('icon-remove').addClass('icon-chevron-down');
			value = value + '';
			if ( value ) {
				$.each(element.find(':text').data('source'), function ( i, item ) {
					if ( item.value == value ) {
						element.find(':hidden').val(item.value);
						element.find(':text').val(item.label);
						element.find(':button i').removeClass('icon-chevron-down').addClass('icon-remove');
						return false;
					}
				});
			}
		},
		
		get: function ( element ){
			return element.find(':hidden').val();
		},
		
		disable: function ( element ) {
			element.addClass('disabled');
			element.find(':input').attr('disabled', 'disabled');
		},
		
		enable: function ( element ) {
			element.removeClass('disabled');
			element.find(':input').removeAttr('disabled');
		},
		
		defaultValue: function( element ) {
			return element.find(':hidden')[0].defaultValue;
		}
	});
	
	/* FILE CLASS DEFINITION
	 * ====================== */
	var File = $.extend({}, Default, {
		
		set: function ( element, value ) {
			if ( ! value ) {
				this.replace(element);
			}
		},
		
		replace: function ( element ) {
			var clone = $('<input>'),
				file = element[0],
				attr, i;
			for ( i = 0; i < file.attributes.length; i++) {
				attr = file.attributes[i];
				if ( attr.nodeName != 'value' ) {
					clone.attr(attr.nodeName, attr.nodeValue);
				}
			}
			element.replaceWith(clone);
		}
	});
	
	/* XFILEBOX CLASS DEFINITION
	 * ====================== */
	var XFileBox = $.extend({}, File, {
		
		create: function( element ) {
			var button = element.find(':button'),
				text = element.find(':text'),
				that = this;
			
			button.on('click', function() {
				if ( element.is('.remove') ) {
					that.set(element);
				} else {
					element.find(':file').click();
				}
			});
			text.on('click', function( e ) {
				element.find(':file').click();
			});
			text.on('keydown', function( e ) {
				// space or enter
				if ( e.keyCode == 32 || e.keyCode == 13 ) {
					element.find(':file').click();
				}
				// tab
				return e.keyCode == 9;
			});
			element.on('change', ':file', function( e ) {
				element.find(':text').val( element.find(':file').val() );
				element.addClass('remove');
				element.find(':button').html(element.data('labelClear'));
			});
		},
		
		get: function ( element ) {
			return element.find(':file').val();
		},
		
		set: function ( element, value ) {
			if ( ! value ) {
				this.replace(element.find(':file'));
				element.find(':text').val('');
				element.find(':button').html(element.data('labelAdd'));
				element.removeClass('remove');
			}
		},
		
		disable: function ( element ) {
			element.addClass('disabled');
			element.find(':input').attr('disabled', 'disabled');
		},
		
		enable: function ( element ) {
			element.removeClass('disabled');
			element.find(':input').removeAttr('disabled');
		},
		
		defaultValue: function ( element ) {
			return element.find(':file')[0].defaultValue;
		}
	});
	
	/* FIELD CLASS DEFINITION
	 * ====================== */
	var Field = {
		
		control: null,
		
		_create: function () {
			this.element.on('change.field.data-api', $.proxy( function ( e ) {
				this._trigger('change', this );
			}, this));
			this.control = $.fn.field.controls[this.options.control || this.element.data('control') || 'Default'];
			this.control.create(this.element);
		},
		
		value: function ( value ) {
			if ( arguments.length ){
				var changed = ( value != this.control.get(this.element) );
				if ( changed ) {
					this.control.set( this.element, value);
					this.element.trigger('change');
				}
				return;
			} 
			return this.control.get(this.element);
		},
	
		disabled: function ( value ) {
			if ( arguments.length ) {
				if ( value ) {
					this.control.disable(this.element);
				} else {
					this.control.enable(this.element);
				}
				return;
			}
			return this.element.is('.disabled,:disabled');
		},
		
		reset: function() {
			this.value(this.control.defaultValue(this.element));
		}
	
	};
	
	/* FIELD PLUGIN DEFINITION
	 * ======================= */
	$.plugin('field', Field);
	
	$.fn.field.controls = {
		'Default': Default,
		'Hidden': Default,
		'TextBox': Entry,
		'PasswordBox': Entry,
		'TextArea': Default,
		'RichText': RichText,
		'FileBox': File,
		'CheckBox': Checkable,
		'RadioButton': Checkable,
		'CheckBoxList': CheckableList,
		'RadioButtonList': CheckableList,
		'ComboBox': ComboBox,
		'XComboBox': XComboBox,
		'XFileBox': XFileBox
	};
	
   /* FIELD DATA-API
	* ============== */
	$(function () {
		
		$('[data-control=RichText]').field();
		
		$('body').on('focus.field.data-api', '[data-control]', function( e ) {
			var $this = $(e.currentTarget);
			if ( ! $this.data('field') ) {
				$this.field();
			}
		});
		
		$('body').on('update.field.data-api', '[data-control]', function( e, data ) {
			var $this = $(e.currentTarget);
			$this.field('value', data);
		});
		
		$('body').on('focus.field.data-api', '[data-mask]', function( e ) {
			var $this = $(e.currentTarget);
				$this.mask($this.data('mask'));
		});
		
		$('body').on('focus.field.data-api', '[data-mask-money]', function( e ) {
			var $this = $(e.currentTarget);
				$this.maskMoney($this.data('mask-money'));
		});
		
	});
	
}(jQuery));

(function($) {
	
	/* FILE UPLOAD CLASS DEFINITION
	 * ====================== */
	FileUpload = {
		form: null,
		
		_create: function () {
			this.form = $('<form id="' + this.options.identify + '" method="post" class="hide" action="' + this.options.remote + '">'
				     	+ '<input type="file" name="' + this.options.identify + '" autocomplete="off">'
				     	+ '</form>');
			this.element.after(this.form);
			this.form.on('change', ':file', $.proxy( function ( e ) {
				this.select();
			}, this));
		},
		
		open: function() {
			this.form.find(':file').click();
		},
		
		select: function() {
			this.form.submit();
		}
	
	};
	
	/* FILE UPLOAD PLUGIN DEFINITION
	 * ======================= */
	$.plugin('upload', FileUpload);
	
	/* FILE UPLOAD DATA-API
	* ============== */
	$(function () {
		$('body').on('click.upload.data-api', '[data-toggle=fileupload]', function( e ) {
			var $this = $(e.currentTarget);
			$this.upload('open');
			return false;
		});
	});
	
}(jQuery));

(function($) {
	
	/* FORM CLASS DEFINITION
	 * ====================== */
	var Form = {
		
		request: null,
		
		_create: function () {
			this.element.validate({
				showErrors: $.proxy( function() { 
					var validator = this.element.validate(); 
					this.error(validator.errorList, validator.successList); 
				}, this)
			});
		},
		
		load: function( url ) {
			this.abort();
			this._trigger('loading', this);
			this.request = $.getJSON(url, $.proxy(function( data ) {
				this.request = null;
				this.refresh(data);
				this._trigger('loaded', {'ui': this, 'response': data});
			}, this));
		},
		
		abort: function(){
			if ( this.request ) {
				this.request.abort();
				this.request = null;
				this._trigger('loaded', { 'ui': this });
			}
		},
		
		refresh: function( data ) {
			$.each(data, function(key, value) {
				var el = $('#' + key );
				var event = $.Event('update');
				el.trigger(event, value);
				if ( ! event.isDefaultPrevented() ) {
					if ( el.is(':input') ) {
						el.val(value);
					} else {
						el.html(value);
					};
				};
			});
		},
		
		elements: function ( selector ) {
			var elements = $('[form=' + this.element.attr('name') + ']');
			if ( arguments.length < 0 ) {
				selector = '[data-control]';
			}
			if ( selector ) {
				elements = elements.closest(selector);
			}
			return elements;
		},
		
		clear: function( selector ) {
			this.elements( selector ).each( function ( i, el ) {
				$(el).field('value', '');
			});
		},
		
		reset: function( selector ) {
			this.elements( selector ).each(function ( i, el ) {
				$(el).field('reset');
			});
		},
		
		isAjax: function() {
			return this.options.ajax;
		},
		
		submit: function( isValid ) {
			isValid = isValid == undefined ? true : isValid;
			if ( isValid && ! this.valid() ) {
				return false;
			}
			if ( ! this._trigger('send', this) ) {
				return false;
			}
			var form = $('<form class="hide">').attr('action', this.element.prop('action'))
											   .attr('method', this.element.prop('method'))
											   .attr('enctype', this.element.prop('enctype'))
											   .appendTo('body');
			
			form.append(this.elements(':not(:button, :disabled)')
							.clone()
							.removeAttr('form')
							.removeAttr('id'));
			
			if ( this.isAjax() ) {
				this.abort();
				form.ajaxSubmit({
					dataType: this.options.format,
					success: $.proxy( function( response ) {
						this._trigger('success', response);
					}, this)
				});
				this.request = form.data('jqxhr');
			} else {
				form.submit();
			}
			form.remove();
			return true;
		},
		
		data: function () {
			var data = {};
			this.elements().each(function ( i, el ) {
				data[ $(el).attr('id') ] = $(el).field('value');
			});
			return data;
		}, 
		
		valid: function( element ) {
			if ( element == undefined ) {
				return this.element.valid();
			}
			return element.valid();
		},
		
		error: function ( errors, valids ) {
			if ( errors.length ) {
				this._trigger('error', {'list': errors, 'ui': this});
			}
			if ( valids.length ){
				this._trigger('valid', {'list': valids, 'ui': this});
			}
		}
	};
	
	/* FORM PLUGIN DEFINITION
	 * ======================= */
	$.plugin('form', Form, { 
		ajax: false,
		format: 'html',
	});
	
   /* FORM DATA-API
	* ============== */
	$(function () {
		$.validator.prototype.elements = function() {
			var validator = this,
				rulesCache = {},
				elements;
				elements = $(this.currentForm).form('elements', ':not(:button, :disabled)')
											  .filter(function() {
													if ( this.name in rulesCache || !validator.objectLength($(this).rules()) ) {
														return false;
													}
													rulesCache[this.name] = true;
													return true;
												});
				return elements;
		};
		
		$.validator.prototype.customMetaMessage = function(element, method) {
			var data = $.validator.metadataRules(element);
			if ( data && data.messages && data.messages[method] ) {
				return data.messages[method];
			}
			return undefined;
		},
		
		$.validator.prototype.findByName = function( name ) {
			return $(this.currentForm).form('elements', '[name="' + name + '"]');
		};

		$.validator.metadataRules = function( element ) {
			return $(element).data( 'validate' ); 
		};
		
		$.validator.staticRules = function( element ) {
			return {};
		};
		
		$.fn.valid = function() {
			if ( $(this[0]).is('form')) {
				return this.validate().form();
			} else {
				var valid = true;
				var validator = $('form[name=' + $(this[0]).attr('form') + ']').form().validate();
				this.each(function() {
					valid &= validator.element(this);
				});
				return valid;
			}
		};
		
		$('body').on('click.form.data-api', ':submit[form]', function ( e ) {
			var $this = $(e.currentTarget);

			$('form[name=' + $this.attr('form') + ']').attr('action', $this.attr('formaction'))
									   				  .form('submit', !$this.is('[formnovalidate]'));
			return false;
        });
		
		$('body').on('click.form.data-api', ':reset[form]', function ( e ) {
			$('form[name=' + $(e.currentTarget).attr('form') + ']').form('reset');
			return false;
        });
		
		$('body').on('click.form.data-api', '[data-toggle=clear][form]', function ( e ) {
			$('form[name=' + $(e.currentTarget).attr('form') + ']').form('clear');
			return false;
        });
		
	});
	
}(jQuery));