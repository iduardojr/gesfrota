<?php
namespace Gesfrota\View\Render\Html5;

use Gesfrota\View\Widget\DynInputRemove;
use PHPBootstrap\Render\Html5\HtmlNode;

class RendererDynInputRemove extends RendererDynInputAdd {
	
	
	const TOGGLENAME ='dyninput-remove';
	
	protected function _render( DynInputRemove $ui, HtmlNode $node ) {
		parent::_render($ui, $node);
		$node->setAttribute('data-index', $ui->getIndex());
	}
	
}