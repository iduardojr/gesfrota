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
	
	$('#administrative-unit-list').tree({persist: 'administrative-unit'});
	$('#product-category-list').tree({persist: 'product-category'});
	$('> li > ul li', '#administrative-unit-list, #product-category-list').draggable({ 
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
	$('#administrative-unit-list').data('remote', '/administrative-unit/update');
	$('#product-category-list').data('remote', '/product-category/update');
	$('li, .tree-node', '#administrative-unit-list, #product-category-list').droppable({
		tolerance: 'pointer',
		distance: 20,
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
				$.getJSON(tree.data('remote'), { parent: target.attr('id'), key: item.attr('id') }, function ( data ) {
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
	$('li, .tree-node', '#administrative-unit-list').droppable('option', { 
		accept: '#administrative-unit-list li'
	});
	$('li, .tree-node', '#product-category-list').droppable('option', { 
		accept: '#product-category-list li'
	});
	
	$('body').on('sentaction', '.modal a[data-storage]', function ( e ) {
		$(this).closest('.modal').modal('hide');
	});
	
	$('body').on('ajaxComplete', function( e ) {
		$('[data-validate*="required"]').each( function( i, item ) {
			$('label[for=' + $(item).attr('name') + ']').addClass('label-required');
		});
		$('form[name="requesters-units-form"]').form({ajax: true, format: 'json'});
		$('form[name="product-attributes-form"]').form({ajax: true, format: 'json'});
		$('form[name="product-attribute-options-form"]').form({ajax: true, format: 'json'});
	})
	$('body').trigger('ajaxComplete');
	
	$('body').on('sentform', 'form[name="requesters-units-form"]', function( e ) {
		$('#requesters-units-form legend .icon-loading').remove();
	});
	$('body').on('sendform', 'form[name="requesters-units-form"]', function( e ) {
		$('#requesters-units-form legend .icon-loading').remove();
		$('#requesters-units-form legend').append('<i class="icon-loading"></i>');
	});
	$('body').on('sendaction', '#requesters-units-form [data-column-action="unit-remove"]', function( e ) {
		$('#requesters-units-form legend .icon-loading').remove();
		$('#requesters-units-form legend').append('<i class="icon-loading"></i>');
	});
	
	$('body').on('sentform', 'form[name="product-attribute-options-form"]', function( e ) {
		$('#product-attribute-options-form legend .icon-loading').remove();
	});
	$('body').on('sendform', 'form[name="product-attribute-options-form"]', function( e ) {
		$('#product-attribute-options-form legend .icon-loading').remove();
		$('#product-attribute-options-form legend').append('<i class="icon-loading"></i>');
	});
	$('body').on('sendaction', '#product-attribute-options-form [data-column-action="unit-remove"]', function( e ) {
		$('#product-attribute-options-form legend .icon-loading').remove();
		$('#product-attribute-options-form legend').append('<i class="icon-loading"></i>');
	});
});