<?php
namespace Gesfrota\View\Render\Html5;

use Gesfrota\View\Widget\DynInput;
use PHPBootstrap\Render\Context;
use PHPBootstrap\Render\Html5\HtmlNode;
use PHPBootstrap\Render\Html5\RendererComponent;

class RendererDynInput extends RendererComponent {
	
	
	public function render(DynInput $ui, Context $context) {
		$value = $ui->getValue();
		
		$node = new HtmlNode('div');
		$node->setAttribute('id', $ui->getName());
		$node->addClass('dyn-input');
		if ($ui->getDisabled()) {
			$node->addClass('disabled');
		}
		$node->setAttribute('data-control', 'DynInput');
		if ($value) {
			$node->setAttribute('data-value', htmlentities(json_encode($value)));
		}
		
		$max = count($value) > $ui->getInputMin() ? count($value) : $ui->getInputMin();
		
		if ($ui->getInputMin() > 0) {
			$node->setAttribute('data-min', $ui->getInputMin());
		}
		
		if ($ui->getInputMax() > 0) {
			$node->setAttribute('data-max', $ui->getInputMax());
		} 
		
		$node->setAttribute('data-template', htmlentities($this->template($ui, '{0}')));
		
		$i = 1;
		while ($i <= $max) {
			$node->appendNode($this->template($ui, $i, $value));
			$i++;
		}
		
		if ( $context->getResponse() ) {
			$context->getResponse()->appendNode($node);
		} else {
			$context->setResponse($node);
		}
	}
	
	protected function template(DynInput $ui, $i, $value = null) {
		$group = new HtmlNode('div');
		$group->addClass('control-group');
		
		if ($ui->getLabel()) {
			$label = new HtmlNode('label');
			$label->setAttribute('for', $ui->getName(). '-' .$i);
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
		$this->toRender($ui->getComponent(), $context);
		$input = $context->getResponse();
		
		$input->setAttribute('name', $ui->getName() . '[]');
		$input->setAttribute('id', $ui->getName(). '-' .$i);
		if (! is_string($i) && isset($value[$i-1])) {
			$input->setAttribute('value', $value[$i-1]);
		}
		$control->appendNode($input);
		
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
	
	protected function format($label, $index) {
	    $seq = null;
		$seq = function ($i) use (&$seq){
			if ($i > 25) {
				return $seq(($i/26)-1). $seq($i%26);
			}
			return chr(65+$i);
		};
		$search = ['{0}', '{A}', '{a}'];
		$replace = [$index, $seq($index-1), strtolower($seq($index-1))];
		return str_replace($search, $replace, $label);
	}
	
}