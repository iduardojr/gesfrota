<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Form\Controls\Decorator\Mask;
use PHPBootstrap\Validate\Pattern\Pattern;
use PHPBootstrap\Validate\Pattern\Email;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Misc\Title;

/**
 * Formulario
 */
class AgencyForm extends Form {
	
	/**
	 * @var Button
	 */
	protected $submit;
	
	/**
	 * Construtor
	 * 
	 * @param string $name
	 * @param string $method
	 */
	public function __construct( $name, $method = null ) {
		parent::__construct($name, $method);
		$this->setStyle(Form::Horizontal);
	
		$header = new Title('Orgãos');
		$header->setSubtext(' administração');
		$header->setPageHeader(true);
		$this->append($header);
		
		$input = new Hidden('id');
		$this->append($input);
		$this->register($input);
		
		$input = new TextBox('acronym');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->addFilter('strip_tags');
		$this->append(new ControlGroup(new Label('Sigla', $input), $input));
		$this->register($input);
		
		$input = new TextBox('name');
		$input->setSpan(5);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->addFilter('strip_tags');
		$this->append(new ControlGroup(new Label('Nome', $input), $input));
		$this->register($input);
		
		$input = new TextBox('contact');
		$input->setSpan(5);
		$input->addFilter('strip_tags');
		$this->append(new ControlGroup(new Label('Responsável', $input), $input));
		$this->register($input);
		
		$input = new TextBox('email');
		$input->setSpan(5);
		$input->setPattern(new Email('Por favor, informe um e-mail'));
		$input->addFilter('strip_tags');
		$this->append(new ControlGroup(new Label('E-mail', $input), $input));
		$this->register($input);
		
		$input = new TextBox('phone');
		$input->setMask(Mask::PhoneBR);
		$input->setPattern(new Pattern(Pattern::PhoneBR, 'Por favor, informe um telefone'));
		$input->addFilter('strip_tags');
		$this->append(new ControlGroup(new Label('Telefone', $input), $input));
		$this->register($input);
		
		$input = new CheckBox('status', 'Ativo');
		$this->append(new ControlGroup(null, $input));
		$this->register($input);
		
		$button = new Button('Salvar', null, Button::Primary);
		$button->setName('submit');
		$this->addButton($button);
		
		$button = new Button('Cancelar');
		$button->setName('cancel');
		$this->addButton($button);
	}
	
}
?>