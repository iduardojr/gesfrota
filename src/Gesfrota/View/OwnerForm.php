<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Owner;
use Gesfrota\Model\Domain\OwnerPerson;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Pattern\CNPJ;
use PHPBootstrap\Validate\Pattern\CPF;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;

class OwnerForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param string|Owner $owner
	 * @param Action $submit
	 * @param Action $cancel
	 */
    public function __construct( $owner, Action $submit, Action $cancel ) {
	    $this->buildPanel('Entidades Externas', 'Gerenciar Proprietários');
		$form = $this->buildForm('owner-form');
		
		$owner = is_object($owner) ? get_class($owner) : $owner;
		
		$general = new Fieldset('Dados Gerais');
		
		$input = new TextBox('name');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField($owner == OwnerPerson::getClass() ? 'Nome' : 'Razão Social', $input, null, $general);
		
		$input = new TextBox('nif');
		$input->setSpan(2);
		if ($owner == OwnerPerson::getClass()) {
		    $input->setMask('999.999.999-99');
		    $input->setPattern(new CPF('Por favor, informe um CPF válido'));
		    $label = 'CPF';
		} else {
		    $input->setMask('99.999.999/9999-99');
		    $input->setPattern(new CNPJ('Por favor, informe um CNPJ válido'));
		    $label = 'CNPJ';
		}
		
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField($label, $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$tab = new Tabbable('owner-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		
		$form->append($tab);

		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Owner $object ) {
		$data['name'] = $object->getName();
		$data['nif'] = $object->getNif();
		$data['active'] = $object->getActive();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Owner $object ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setNif($data['nif']);
		$object->setActive($data['active']);
	}

}
?>