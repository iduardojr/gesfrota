<?php
namespace Sigmat\View\Agency;

use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Form\Controls\RadioButtonList;

/**
 * Formulario de Filtro
 */
class FormFilter extends Form {
	
	/**
	 * Construtor
	 */
	public function __construct() {
		parent::__construct('agency-filter');
		$this->setStyle(Form::Horizontal);
		
		$input = new TextBox('acronym');
		$input->setSpan(2);
		$this->append(new ControlGroup(new Label('Sigla', $input), $input));
		$this->register($input);
		
		$input = new TextBox('name');
		$input->setSpan(4);
		$this->append(new ControlGroup(new Label('Nome', $input), $input));
		$this->register($input);
		
		$input = new RadioButtonList('status', true);
		$input->addOption(0, 'Ambos');
		$input->addOption(2, 'Ativo');
		$input->addOption(1, 'Inativo');
		$input->setValue(0);
		$this->append(new ControlGroup(null, $input));
		$this->register($input);
	}
}
?>