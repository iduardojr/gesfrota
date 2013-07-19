$(function() {
	$('form').on('errorform', function( e, data ){
		$.each(data.list, function (i, error) {
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
	
	$('form').on('validform', function( e, data ){
		$.each(data.list, function (i, valid) {
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
	
	$(':input').on('focusin', function( e ){
		var group = $(this).closest('.control-group'); 
		group.removeClass('error')
			 .find('.validate')
			 .remove();
	});
	
	$('[data-validate*="required"]').each( function( i, item ) {
		$('label[for=' + $(item).attr('name') + ']').addClass('label-required');
	});
	
	$('#administrative-unit-list').tree({persist: 'administrative-unit'});
	$('#administrative-unit-list > li > ul li').draggable({ 
		revert: 'invalid',
		cursor: "move", 
		cursorAt: { top: 17, left: 20 },
		helper: function ( e ) {
			var helper = $(e.currentTarget).children('.tree-node').clone(); 
			$('.btn', helper).remove();
			return helper.addClass('tree-helper');
		}, 
		start: function ( e, ui ) {
			$(e.currentTarget).addClass('tree-selected');
		},
		stop: function ( e, ui ) {
			$('.tree-selected').removeClass('tree-selected');
		}
	});
	$('li, .tree-node', '#administrative-unit-list').droppable({
		tolerance: 'pointer',
		distance: 20,
		accept: "#administrative-unit-list li",
		greedy: true,
		over: function ( e, ui ) {
			var target = $(this).closest('li');
			$('.tree-selected').removeClass('tree-placeholder');
			$('.tree-placeholder').remove();
			$('.tree-hover').removeClass('tree-hover');
			target.addClass('tree-hover')
			if ( $('.tree-selected').is(':hidden') )  {
				target.children('ul')
					  .append($('<li class="tree-placeholder"></li>'));
			}
		},
		out: function ( e, ui ) {
			$('.tree-selected').removeClass('tree-placeholder');
			$('.tree-placeholder').remove();
			$('.tree-hover').removeClass('tree-hover');
		},
		drop: function( e, ui ) {
			var target = $(this).closest('li'),
				item = ui.draggable,
				helper = item.clone().removeAttr('style');
				sender = item.parents('li:first'),
				tree = item.closest('.tree');
			$('.tree-selected').removeClass('tree-placeholder');
			if ( $('> ul > .tree-selected', target).size() == 0 ) {
				item.hide();
				$('.tree-node:first', helper).addClass('loading');
				target.addClass('expandable');
				target.children('ul').append(helper);
				tree.tree('expand', target);
				$('.tree-hover').removeClass('tree-hover');
				$.getJSON('/administrative-unit/update', { parent: target.attr('id'), key: item.attr('id') }, function ( data ) {
					$('.tree-node', helper).removeClass('loading');
					item.show();
					if ( data.success ) {
						helper.replaceWith(item);
						if ( $('> ul > li', sender).size() == 0 ) {
							sender.removeClass('collapsable expandable');
						}
					} else {
						helper.remove();
						if ( $('> ul > li', target).size() == 0 ) {
							target.removeClass('collapsable expandable');
						}
						if ( data.message ) {
							$('#flash-message').html(data.message);
						}
					}
				});
			}
			$('.tree-placeholder').remove();
		}
	});
	
	$('body').on('loadedseek', '[data-provide=seek]', function ( e, ui ) {
		if ( ui.result.message ) {
			var group = ui.element.closest('.control-group');
				group.addClass('error')
					 .find('.controls')
					 .append('<span class="validate help-inline">' + ui.result.message + '</span>');
		}
	});
	
	$('body').on('afteraction', '.modal a[data-storage]', function ( e ) {
		$(this).closest('.modal').modal('hide');
	});
});