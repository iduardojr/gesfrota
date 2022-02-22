<?php
namespace Gesfrota\View\Render\Html5;

use PHPBootstrap\Render\Html5\HtmlNode;
use PHPBootstrap\Render\Html5\RendererToggle;
use Gesfrota\View\Widget\DynInputAdd;

class RendererDynInputAdd extends RendererToggle {
	
	const TOGGLENAME ='dyninput-add';
	
	protected function _render( DynInputAdd $ui, HtmlNode $node ) {
		parent::_render($ui, $node);
		$attr = $node->getTagName() == 'a' ?  'href' : 'data-target';
		
		$node->setAttribute($attr, '#' . $ui->getTarget()->getName());
	}
	
}