<?php
namespace Gesfrota\View\Render\Html5;

use PHPBootstrap\Render\Html5\RendererComponent;
use Gesfrota\View\Widget\PlaceInput;
use PHPBootstrap\Render\Context;

class RendererPlaceInput extends RendererComponent {
	
	public function render( PlaceInput $ui, Context $context ){
		$newContext = new Context();
		if ($ui->getDirection()) {
			$ui->setMarker($ui->getDirection()->getMarkerInstance());
		}
		$this->toRender($ui->getComponent(), $newContext);
		$node = $newContext->getResponse();

		foreach ($node->getAllNodes() as $el) {
			if ($el->getTagName() == 'input') {
				$el->setAttribute('id', str_replace(['[',']'], ['-',''], $el->getAttribute('id')));
				if ($el->getAttribute('type') == 'text') {
					$el->setAttribute('data-attribute', 'description');
					$el->setAttribute('data-control', 'Place');
				} else {
					$el->setAttribute('data-attribute', 'place');
					$el->setAttribute('data-control', null);
				}
			}
		}
		if ( $context->getResponse() ) {
			$context->getResponse()->appendNode($node);
		} else {
			$context->setResponse($node);
		}
	}
}
?>
