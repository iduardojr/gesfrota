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
		options.execute = function( event, widget, data ) {
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
			if ( this.options.remote) {
				window.location = this.options.remote;
			}
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
			if ( $.isFunction(this.options.send) ) {
				if ( this.options.send.call(this) === false ) {
					
				}
			}
			this.request = $.ajax( {
				url: this.options.remote, 
				success: $.proxy( function( data, textStatus, jqXHR) {
					if ( $.isFunction(this.options.execute) ) {
						this.options.execute.apply(this, arguments);
					}
					if ( $.isFunction(this.options.sent) ) {
						this.options.sent.call(this);
					}
				}, this ),
				dataType: this.options.response
			});
		},
	
		abort: function() {
			if ( this.request ) {
				this.request.abort();
				if ( $.isFunction(this.options.sent) ) {
					this.options.sent.call(this);
				}
			}
		}
		
	};
	
	/* STORAGE CLASS DEFINITION
	 * ======================= */
	var Storage = function( options ){
		this.options = $.extend({}, $.fn.action.defaults, options);
	};
	
	Storage.prototype = {
		options: null,
		
		toggle: function() {
			if ( $.isFunction(this.options.send) ) {
				this.options.send.call(this);
			}
			if ( $.isFunction(this.options.execute) ) {
				this.options.execute.call(this, this.options.storage);
			}
			if ( $.isFunction(this.options.sent) ) {
				this.options.sent.call(this);
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
				case 'execute':
				case 'before':
				case 'after':
					break;
				case 'ajax':
				case 'target':
				case 'storage': 
					var options = $.extend({}, this.options, {
						execute: $.proxy( function( data, textStatus, jqXHR ) {
							this._trigger('execute', this, data, textStatus, jqXHR );
						}, this),
						send: $.proxy( function() {
							this._trigger('send', this);
						}, this),
						sent: $.proxy(function() {
							this._trigger('sent', this);
						}, this)
					});
					if ( this.options.ajax ) {
						this.strategy = new Ajax(options);
					} else if ( this.options.target ){
						this.strategy = new Windows(options);
					} else if ( this.options.storage ) {
						this.strategy = new Storage(options);
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
		send: null,
		execute: function( e, ui, data, textStatus, jqXHR ){
			if ( ui.options.response == ui.Json) {
				$.each( data, function( key, value ) {
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
			} else {
				if ( ui.options.target ) {
					var event = $.Event('update');
					$(ui.options.target).trigger(event, data);
					if ( ! event.isDefaultPrevented() ) {
						if ( ui.options.response == ui.Text ){
							$(ui.options.target).html(data);
						} else if ( ui.options.response == ui.Html ){
							$(ui.options.target).replaceWith(data);
						}
					}
				}
			}
		},
		sent: null
	});
	
	/* ACTION DATA-API
	* ============== */
	var event = function ( e ) {
		var $this = $(e.currentTarget), 
		options = $.extend({response: 'json'}, $this.data());
	
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
		} else {
			delete options.target;
		}
		$this.action('option', options);
		
		$this.action('toggle');
		return false;
	};
	
	$('body').on('click.action.data-api', 'a:not([href^=#],[data-toggle]),a[data-storage]', event);
	$('body').on('toggle.action.data-api', 'a:not([href^=#]),a[data-storage]', event);
	
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
	
	/* SEARCH CLASS DEFINITION
	 * ========================= */
	Search = {
			
		request: null,
		query: null,
		output: null,
		response: null,
		
		_create: function() {
			this.output = this.options.output ? $('.modal-body', this.options.output) : null;
		},
		
		abort: function(){
			if ( this.request ) {
				this.request.abort();
				this.request = null;
				this.response = null;
				this._trigger('loaded', this);
			}
		},
		
		lookup: function() {
			var query = this.options.query ? $(this.options.query).filter(':not([readonly])').val() : '';
			//if ( this.query != query || this.response == null ) {
				this.abort();
				this.query = query;
				if ( this.options.remote ) {
					this._trigger('loading', this);
					this.request = $.get( this.options.remote, { 'query': this.query }, $.proxy( function( result ) {
						this.request = null;
						this.response = result;
						this._trigger('process', this);
						this._trigger('loaded', this);
					}, this));
				}
			//} else {
			//	this._trigger('process', this);
			//}
		}
	};
	
	/* SEARCH PLUGINS DEFINITION
	 * ======================= */
	$.plugin('search', Search, {
		loading: function ( e, ui ) {
			$(ui.options.query).addClass('loading');
		},
		loaded: function( e, ui ) {
			$(ui.options.query).removeClass('loading');
		}, 
		process: function ( e, ui ) {
			if ( ui.output ) {
				ui.output.empty();
				ui.output.append(ui.response);
				ui.output.closest('.modal')
						 	  .modal('show')
       	   		  		 	  .one('hide', $.proxy( function () { ui.element.focus(); }, this ));
			} else {
				$.each( ui.response, function( key, value ) {
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
		}
	});
	
   /* DATA-API
	* ============== */
	$(function () {
		
		$('body').on('click.search.data-api', '[data-toggle=search]', function( e ) {
			var $this = $(e.currentTarget);
			if ( ! $this.data('search') ) {
				$this.search($this.data());
			};
			$this.search('lookup');
			return false;
		});
		
	});
	
}(jQuery));

(function($) {
	
	/* TYPEAHEAD CLASS DEFINITION
	 * ===================== */
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
				if (data.error) {
					alert(data.error);
				} else {
					this.process(data);
				}
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
    	
    	matcher: function (item) {
			var callback = this.options.matcher;
			if($.isFunction(callback)) {
				return callback.call(this, item);
			}
			return true;
    	},
    	
    	sorter: function (items) {
			if($.isFunction(this.options.sorter)) {
				var callback = this.options.sorter;
				return callback.call(this, items);
			}
			return items;
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
	
	/* SEEK CLASS DEFINITION
	 * ===================== */
	Seek = {
			
		request: null,
		query: null,
		result: null,
		
		_create: function() {
			this.element.on('blur', $.proxy(this.lookup, this));
			this.element.on('focus', $.proxy(this.abort, this));
		},
		
		abort: function(){
			if ( this.request ) {
				this.request.abort();
				this.request = null;
				this.result = null;
				this._trigger('loaded', this);
			}
		},
		
		lookup: function() {
			if ( this.query != this.element.val() ) {
				this.query = this.element.val();
				this.abort();
				if ( this.options.remote && this.query.length > 0 ) {
					this._trigger('loading', this);
					this.request = $.getJSON( this.options.remote, {'query': this.query }, $.proxy( function( result ) {
						this.request = null;
						this.result = result;
						this._trigger('process', this);
						this._trigger('loaded', this);
					}, this));
				}
			}
		}
		
	};
	
	
	/* SUGGEST, SEEK PLUGINS DEFINITION
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
		select: function( e, data ) {
			if ( $.isPlainObject(data) && $.isPlainObject(data.value) ) {
				$.each( data.value, function( key, value ) {
					var el = $('#' + key );
					var event = $.Event('update');
					el.trigger(event, value);
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
	
	
	$.plugin('seek', Seek, {
		loading: function ( e, ui ) {
			ui.element.addClass('loading');
		},
	    loaded: function( e, ui ) {
			ui.element.removeClass('loading');
		},
		process: function ( e, ui ) {
			$.each( ui.result, function( key, value ) {
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
	
   /* SUGGEST, SEEK DATA-API
	* ============== */
	$(function () {
		
		$('body').on('focus.seek.data-api', '[data-provide=seek]', function( e ) {
			var $this = $(e.currentTarget);
			if ($this.data('seek')) return;
		    e.preventDefault();
		    $this.seek($this.data());
		});
		
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
	
	/* CHOSENBOX CLASS DEFINITION
	 * ====================== */
	var ChosenBox = $.extend({}, Default, {
		
		create: function ( element ) {
			element.chosen(element.data('options'));
			element.next().removeAttr('style');
		},
		
		set: function ( element, value ) {
			Default.set(element, value);
			element.trigger("chosen:updated");
		},
		
		disable: function ( element ) {
			Default.disable(element);
			element.trigger("chosen:updated");
		},
		
		enable: function( element ) {
			Default.enable(element);
			element.trigger("chosen:updated");
		},
		
		get: function ( element ) {
			var value = Default.get(element);
			return element.is('[multiple]') && ! $.isArray(value) ? [value] : value;
		}, 
		
		defaultValue: function( element ) {
			var value = []; 
			element.children().each( function(i, item) {
				if ( item.defaultSelected ) {
					value.push(item.value);
				}
			});
			return element.is('[multiple]') ? value : value.shift();
		}
	});
	
	/* LISTBOX CLASS DEFINITION
	 * ====================== */
	var ListBox = $.extend({}, Default, {
		
		get: function ( element ) {
			var value = Default.get(element);
			return ! $.isArray(value) ? [value] : value;
		}, 
		
		defaultValue: function( element ) {
			var value = []; 
			element.children().each( function(i, item) {
				if ( item.defaultSelected ) {
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
			return ListBox.defaultValue(element).shift();
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
		
		get: function() {
			return this.control;
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
		'ListBox': ListBox,
		'XComboBox': XComboBox,
		'ChosenBox': ChosenBox,
		'XFileBox': XFileBox
	};
	
   /* FIELD DATA-API
	* ============== */
	$(function () {
		$.valHooks.select.set = function(elem, value ) {
			var values = jQuery.makeArray( value );
			if ( !values.length ) {
				elem.selectedIndex = -1;
				return null;
			}
			$(elem).find("option").each(function( i, item ) {
				$(item)[$.inArray( $(item).val(), values ) >= 0 ? 'attr' : 'removeAttr']('selected', 'selected');
			});
		};
		
		$('[data-control=RichText], [data-control=ChosenBox]').field();
		
		$('body').on('focus.field.data-api', '[data-control]', function( e ) {
			var $this = $(e.currentTarget);
			if ( ! $this.data('field') ) {
				$this.field();
			}
		});
		
		$('body').on('update.field.data-api', '[data-control]', function( e, data ) {
			var $this = $(e.currentTarget);
			$this.field('value', data);
			return false;
		});
		
		$('body').on('focus.field.data-api', '[data-mask]', function( e ) {
			var $this = $(e.currentTarget);
				$this.mask($this.data('mask'));
		});
		
		$('body').on('focus.field.data-api', '[data-mask-money]', function( e ) {
			var $this = $(e.currentTarget);
			if ( ! $this.data('MaskMoney') ) {
				$this.maskMoney($this.data('mask-money'));
				$this.data('MaskMoney', true);
			}
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
		
		Text: 'text',
		Html: 'html',
		Json: 'json',
			
		request: null,
		response: null,
		invalidList: null,
		validList: null,
		
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
				this.response = data;
				this._trigger('refresh', this);
				this._trigger('loaded', this);
			}, this));
		},
		
		abort: function(){
			if ( this.request ) {
				this.request.abort();
				this.request = null;
				this.response = null;
				this._trigger('loaded', this);
			}
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
			
			this.elements('select').each( function(i, item) {
				$(item).val($(item).val());
			});
			
			form.append(this.elements(':not(:button, :disabled)')
							.clone(true)
							.removeAttr('form')
							.removeAttr('id'));
			
			$.each( this.elements('textarea'), function( i, item ){
				$('textarea[name=' + $(item).attr('id') + ']', form).val($(item).val());
			});
			
			if ( this.isAjax() ) {
				this.abort();
				form.ajaxSubmit({
					dataType: this.options.format,
					success: $.proxy( function( data ) {
						this.request = null;
						this.response = data;
						this._trigger('success', this);
						this._trigger('sent', this);
						this.response = null;
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
		
		error: function ( invalidList, validList ) {
			this.invalidList = invalidList;
			this.validList = validList;
			if ( this.invalidList.length ) {
				this._trigger('error', this);
			}
			if ( this.validList.length ){
				this._trigger('valid', this);
			}
		}
	};
	
	/* FORM PLUGIN DEFINITION
	 * ======================= */
	$.plugin('form', Form, { 
		ajax: false,
		format: 'html',
		refresh: function( e, ui ) {
			$.each(ui.response, function( key, value ) {
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
		success: function( e, ui ){
			if ( ui.options.format == ui.Json ) {
				$.each( ui.response, function( key, value ) {
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
			} else {
				var event = $.Event('update');
				ui.element.trigger(event, ui.response);
				if ( ! event.isDefaultPrevented() ) {
					if ( ui.options.format == ui.Text ){
						ui.element.html(ui.response);
					} else if ( ui.options.format == ui.Html ){
						ui.element.replaceWith(ui.response);
					}
				}
			}
		}
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

(function($) {
	
	/* TREE CLASS DEFINITION
	 * ====================== */
	var Tree = {
		history: {},
		_create: function() {
			if ( this.options.persist ) {
				var history = $.cookie(this.options.persist);
				if ( history !== undefined ) {
					if ( ! $.cookie.json ) {
						history = JSON.parse(history);
					}
					this._reset(history);
				}
			}
		},
		
		_reset: function( history ) {
			var that = this;
			this.history = {};
			$.each(history, function( identify, value ) {
				var node = $('li[id="' + identify + '"]', that.element);
				if ( node.is('li.expandable,li.collapsable') ) {
					value ? that.expand(node) : that.collapse(node);
				} else {
					that.history[identify] = value;
				}
			});
		}, 

		toggle: function( node, isAll ) {
			if ( node.is('.expandable') ) {
				this.expand(node, isAll);
			} else if ( node.is('.collapsable') ) {
				this.collapse(node);
			}
		},
		
		expand: function ( node, isAll ) {
			if ( node.is('.expandable') ) {
				var hitarea = $('> .hitarea', node);
				var identify = node.attr('id');
				if ( $('ul > li', node).size() > 0 || hitarea.is(':not(a[href])') ) {
					node.removeClass('expandable');
					node.addClass('collapsable');
					this._trigger('expand', {'node': node, 'ui': this});
					this.persist();
				} else {
					var that = this;
					node.addClass('loading');
					this._trigger('loading', {'node': node, 'ui': this});
					$.get(hitarea.attr('href'), function ( data ) {
						node.removeClass('loading');
						node.replaceWith(data);
						node = $('li[id="' + identify + '"]', that.element);
						that._trigger('loaded', {'node': node, 'ui': that});
						if ( isAll ) {
							$('ul > li', node).each( function ( i, node1 ) {
								that.expand($(node1), isAll);
							});
						}
						that.persist();
						that._reset(that.history);
					}, 'html');
				}
			}
		},

		collapse: function( node ) {
			if ( node.is('.collapsable') ) {
				node.removeClass('collapsable');
				node.addClass('expandable');
				this._trigger('collapse', {'node': node, 'ui': this});
				this.persist();
			}
		},

		persist: function() {
			if ( this.options.persist ) {
				var history = {};
				this.element.find('.expandable,.collapsable')
							.each(function( i , node ) {
								node = $(node);
								history[node.attr('id')] = node.is('.collapsable');
							});
				if ( ! $.cookie.json) {
					history = JSON.stringify(history);
				} 
				$.cookie(this.options.persist, history, this.options.cookie);
			}
		}

	};
	
	/* TREE PLUGIN DEFINITION
	 * ======================= */
	$.plugin('tree', Tree, {
		persist: false,
		cookie: {}
	});
	
   /* TREE DATA-API
	* ============== */
	$(function () {
		$('body').on('click.tree.data-api', '[data-toggle=tree]', function ( e ) {
			var $this = $(e.currentTarget),
				node = $this.data('target') ? $($this.data('target')) : $this.parents('li:first');
				tree = node.closest('.tree');
				tree.tree('toggle', node);
				return false;
	    });
		
		$('body').on('click.tree.data-api', '[data-tree]', function ( e ) {
			var $this = $(e.currentTarget),
				tree = $($this.data('target'));
				$('li', tree).each(function (i, node) {
					tree.tree($this.data('tree'), $(node), true);
				});
	    });
	});
	
}(jQuery));

(function ($) {
	
	jQuery.validator.addMethod("cpf", function(value, element) {
		value = value.replace('.','');
		value = value.replace('.','');
		cpf = value.replace('-','');
		if(this.optional(element))
		{
			return this.optional(element);	
		}
		while(cpf.length < 11) cpf = "0"+ cpf;
		var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
		var a = [];
		var b = new Number;
		var c = 11;
		for (i=0; i<11; i++){
			a[i] = cpf.charAt(i);
			if (i < 9) b += (a[i] * --c);
		}
		if ((x = b % 11) < 2) { a[9] = 0; } else { a[9] = 11-x; }
		b = 0;
		c = 11;
		for (y=0; y<10; y++) b += (a[y] * c--);
		if ((x = b % 11) < 2) { a[10] = 0; } else { a[10] = 11-x; }
		if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg)) return false;
		return true;
	}, "Por favor, forne&ccedil;a um CPF v&aacute;lido."); 
	
	
	jQuery.validator.addMethod("cnpj", function(cnpj, element) {
	   // DEIXA APENAS OS NUMEROS
	   cnpj = cnpj.replace('/','');
	   cnpj = cnpj.replace('.','');
	   cnpj = cnpj.replace('.','');
	   cnpj = cnpj.replace('-','');
	   if(this.optional(element))
	   {
			return this.optional(element);   
	   }
	   var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;
	   digitos_iguais = 1;
	
	   if (cnpj.length < 14 && cnpj.length < 15){
	      return false;
	   }
	   for (i = 0; i < cnpj.length - 1; i++){
	      if (cnpj.charAt(i) != cnpj.charAt(i + 1)){
	         digitos_iguais = 0;
	         break;
	      }
	   }
	
	   if (!digitos_iguais){
	      tamanho = cnpj.length - 2;
	      numeros = cnpj.substring(0,tamanho);
	      digitos = cnpj.substring(tamanho);
	      soma = 0;
	      pos = tamanho - 7;
	
	      for (i = tamanho; i >= 1; i--){
	         soma += numeros.charAt(tamanho - i) * pos--;
	         if (pos < 2){
	            pos = 9;
	         }
	      }
	      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
	      if (resultado != digitos.charAt(0)){
	         return false;
	      }
	      tamanho = tamanho + 1;
	      numeros = cnpj.substring(0,tamanho);
	      soma = 0;
	      pos = tamanho - 7;
	      for (i = tamanho; i >= 1; i--){
	         soma += numeros.charAt(tamanho - i) * pos--;
	         if (pos < 2){
	            pos = 9;
	         }
	      }
	      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
	      if (resultado != digitos.charAt(1)){
	         return false;
	      }
	      return true;
	   }else{
	      return false;
	   }
	}, "Por favor, forne&ccedil;a um CNPJ v&aacute;lido."); // Mensagem padr&atilde;o 
	
	jQuery.validator.addMethod("phone", function(phone_number, element) { 
		return this.optional(element) || phone_number.match(/^\([1-9]\d\)\s[1-9]\d{3}-\d{4}$/);
	}, "Por favor, forne&ccedil;a um telefone v&aacute;lido.");
	jQuery.validator.addMethod("zipcode", function(cep, element){ 
		return this.optional(element) || cep.match(/^\d{2}\.?\d{3}-?\d{3}$/);
	}, "Por favor, forne&ccedil;a um CEP v&aacute;lido.");
	
	
	$.extend($.validator.messages, { dateITA: "Por favor, forne&ccedil;a uma data v&aacute;lida." });
}(jQuery));

