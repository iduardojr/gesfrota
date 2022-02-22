<?php
namespace Gesfrota\View\Render\Html5;

use PHPBootstrap\Render\Html5\RendererComponent;
use Gesfrota\View\Widget\Direction;
use PHPBootstrap\Render\Context;
use PHPBootstrap\Render\Html5\HtmlNode;

class RendererDirection extends RendererComponent {
	
	public function render( Direction $ui, Context $context ){
		$newContext = new Context();
		$this->toRender($ui->getComponent(), $newContext);
		$node = $newContext->getResponse();
		$node instanceof HtmlNode;
		
		$node->addClass('map');
		$node->setAttribute('data-renderer', 'directions');
		$node->setAttribute('data-marker', $ui->getMarker());
		if ($ui->getOptionsMap()) {
			$node->setAttribute('data-options-map', htmlentities(json_encode($ui->getOptionsMap())));
		}
		if ($ui->getOptionsRequest()) {
			$node->setAttribute('data-options-request', htmlentities(json_encode($ui->getOptionsRequest())));
		}
		if ($ui->getFrom()) {
			$node->setAttribute('data-place-from', $ui->getFrom()->getName());
		}
		
		if ($ui->getWay()) {
			$node->setAttribute('data-place-way', $ui->getWay()->getName());
		}
		
		if ($ui->getTo()) {
			$node->setAttribute('data-place-to', $ui->getTo()->getName());
		}
		
		if ( $context->getResponse() ) {
			$context->getResponse()->appendNode($node);
		} else {
			$context->setResponse($node);
		}
	}
}
?>