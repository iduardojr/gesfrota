<?php
namespace Sigmat\View\Widget;

use PHPBootstrap\Render\Html5\HtmlNode;
use PHPBootstrap\Render\Html5\RendererDependsResponse;

/**
 * Renderiza um alternador de edição de arvore
 */
class RendererTgTreeEditable extends RendererDependsResponse  {
	
	
	/**
	 * @see RendererDependsResponse::_render()
	 */
	protected function _render( TgTreeEditable $ui, HtmlNode $node ) {
		$node->setTagName('a');
		$node->setAttribute('href', $ui->getAction()->toURI()); 
		$node->setAttribute('data-tree-node', $ui->getOperation());
		$node->setAttribute('data-target', '#' . $ui->getTarget()->getIdentify());
	}
}
?>