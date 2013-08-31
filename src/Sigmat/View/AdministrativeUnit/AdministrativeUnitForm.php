<?php
namespace Sigmat\View\AdministrativeUnit;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\Decorator\Mask;
use PHPBootstrap\Validate\Pattern\Pattern;
use PHPBootstrap\Validate\Pattern\Email;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\AbstractForm;
use Sigmat\Model\AdministrativeUnit\AdministrativeUnit;
use PHPBootstrap\Widget\Form\Controls\Uneditable;

/**
 * Formulario
 */
class AdministrativeUnitForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 */
	public function __construct( Action $submit, Action $cancel ) {
		$this->buildPanel('Administração', 'Gerenciar Unidades Administrativas');
		$form = $this->buildForm('administrative-unit-form');
		
		$input = new Uneditable('parent');
		$input->setSpan(9);
		$form->buildField('Unidade Superior', $input);
		
		$input = new TextBox('acronym');
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->addFilter('strtoupper');
		$form->buildField('Sigla', $input);
		
		$input = new TextBox('name');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome', $input);
		
		$input = new TextBox('contact');
		$input->setSpan(7);
		$form->buildField('Responsável', $input);
		
		$input = new TextBox('email');
		$input->setSpan(7);
		$input->setPattern(new Email('Por favor, informe um e-mail'));
		$form->buildField('E-mail', $input);
		
		$input = new TextBox('phone');
		$input->setSpan(2);
		$input->setMask(Mask::PhoneBR);
		$input->setPattern(new Pattern(Pattern::PhoneBR, 'Por favor, informe um telefone'));
		$form->buildField('Telefone', $input);
		
		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( AdministrativeUnit $object ) {
		$data['name'] = $object->getName();
		$data['acronym'] = $object->getAcronym();
		$data['contact'] = $object->getContact();
		$data['email'] = $object->getEmail();
		$data['phone'] = $object->getPhone();
		$data['parent'] = implode(' / ', $object->getAncestors());
		$this->component->setData($data);
	}
	
	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( AdministrativeUnit $object ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setAcronym($data['acronym']);
		$object->setContact($data['contact']);
		$object->setEmail($data['email']);
		$object->setPhone($data['phone']);
	}

}
?>