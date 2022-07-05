<?php
namespace Gesfrota\View\Render\Html5;

use PHPBootstrap\Widget\Pagination\Pagination;
use PHPBootstrap\Render\Html5\HtmlNode;
use PHPBootstrap\Render\Html5\Pagination\RendererPaginator;

/**
 * Renderiza paginador
 */
class RendererPagination extends RendererPaginator {

	/**
	 * Nome da tag
	 *
	 * @var string
	 */
	const TAGNODE = 'div';
	
	/**
	 * 
	 * @see RendererPaginator::_render()
	 */
	protected function _render( Pagination $ui, HtmlNode $node ) {
		$node->addClass('pagination');
		
		if ( $ui->getAlign() ) {
			$node->addClass( $ui->getAlign());
		}
		
		if ( $ui->getSize() ) {
			$node->addClass($ui->getSize());
		}
		
		if ($ui->getPaginator()->getTotal() > 0) {
    		$total = new HtmlNode('small');
    		$total->addClass('pagination-label');
    		$total->appendNode($ui->getPaginator()->getTotal() . ' registro' . ($ui->getPaginator()->getTotal() > 1 ? 's' : ''));
    		$node->appendNode($total);
	
		    $ul = new HtmlNode('ul');
		    parent::_render($ui, $ul);
		    $node->appendNode($ul);
		}
	}

}
?>