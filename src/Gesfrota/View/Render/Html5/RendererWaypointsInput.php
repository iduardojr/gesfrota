<?php
namespace Gesfrota\View\Render\Html5;

use PHPBootstrap\Render\Context;
use Gesfrota\View\Widget\WaypointsInput;
use PHPBootstrap\Render\Html5\HtmlNode;

class RendererWaypointsInput extends RendererDynInput {
	
	protected function template(WaypointsInput $ui, $i, $value = null) {
		$group = new HtmlNode('div');
		$group->addClass('control-group');
		
		if ($ui->getLabel()) {
			$label = new HtmlNode('label');
			$label->setAttribute('for', $ui->getName(). '-' . $i . '-description');
			$label->addClass('control-label');
			$text = is_string($i) ? $ui->getLabel() : $this->format($ui->getLabel(), $i);
			$label->appendNode($text);
			$group->appendNode($label);
		}
		$control = new HtmlNode('div');
		$control->addClass('controls');
		$control->addClass('controls-row');
		$group->appendNode($control);
		
		$context = new Context();
		$place = clone $ui->getComponent();
		$place->setName($ui->getName().'[' . $i . ']');
		if ($ui->getDirection() && !is_string($i)) {
			$place->setMarker($ui->getDirection()->getMarkerInstance());
		} else {
			$place->setMarker(is_string($i) ? $ui->getComponent()->getMarker() : $this->format($ui->getComponent()->getMarker(), $i));
		}
		if (!is_string($i) && isset($value[$i-1])){
			$place->setValue($value[$i-1]);
		}
		
		$this->toRender($place, $context);
		$embed = $context->getResponse();
		$embed->setAttribute('id', $ui->getName(). '-' .$i);
		
		foreach ($embed->getAllNodes() as $el) {
			if ($el->getTagName() == 'input') {
				$el->setAttribute('id', str_replace(['[',']'], ['-',''], $el->getAttribute('id')));
			}
		}
		
		$control->appendNode($embed);
		
		if (is_string($i) || $i > $ui->getInputMin()) {
			$button = new HtmlNode('a');
			$button->setAttribute('href', '#' . $ui->getName());
			$button->addClass('btn');
			$button->addClass('btn-link');
			if ($ui->getDisabled()) {
				$button->addClass('disabled');
			}
			$button->setAttribute('data-toggle', 'dyninput-remove');
			$button->setAttribute('data-index', $i);
			$button->appendNode('<i class="icon-remove"></i>');
			$control->appendNode($button);
		}
		
		return $group;
	}
	
}
?>
