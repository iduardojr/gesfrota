<?php
namespace Sigmat\View;

use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Validate\Pattern\Pattern;
use PHPBootstrap\Validate\Pattern\Email;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\Decorator\Mask;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use Sigmat\View\GUI\AbstractForm;
use Sigmat\Model\Domain\Agency;

class AgencyForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 */
	public function __construct( Action $submit, Action $cancel ) {
		$this->buildPanel('Estrutura Organizacional', 'Gerenciar Orgãos');
		$form = $this->buildForm('agency-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$input = new TextBox('name');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome', $input, null, $general);
		
		$input = new TextBox('acronym');
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->addFilter('strtoupper');
		$form->buildField('Sigla', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$accountable = new Fieldset('Responsável');
		
		$input = new TextBox('contact');
		$input->setSpan(7);
		$form->buildField('Responsável', $input, null, $accountable);
		
		$input = new TextBox('email');
		$input->setSpan(7);
		$input->setPattern(new Email('Por favor, informe um e-mail'));
		$form->buildField('E-mail', $input, null, $accountable);
		
		$input = new TextBox('phone');
		$input->setSpan(2);
		$input->setMask(Mask::PhoneBR);
		$input->setPattern(new Pattern(Pattern::PhoneBR, 'Por favor, informe um telefone'));
		$form->buildField('Telefone', $input, null, $accountable);
		
		$tab = new Tabbable('agency-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Responsável'), null, new TabPane($accountable));
		
		$form->append($tab);
		$form->remove($general);
		$form->remove($accountable);

		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Agency $object ) {
		$data['name'] = $object->getName();
		$data['acronym'] = $object->getAcronym();
		$data['contact'] = $object->getContact();
		$data['email'] = $object->getEmail();
		$data['phone'] = $object->getPhone();
		$data['active'] = $object->getActive();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Agency $object ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setAcronym($data['acronym']);
		$object->setContact($data['contact']);
		$object->setEmail($data['email']);
		$object->setPhone($data['phone']);
		$object->setActive($data['active']);
	}

}
?>