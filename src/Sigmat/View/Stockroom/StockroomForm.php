<?php
namespace Sigmat\View\Stockroom;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\AbstractForm;
use Sigmat\Model\Stockroom\Stockroom;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Form;

/**
 * Formulario
 */
class StockroomForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Form $subform
	 */
	public function __construct( Action $submit, Action $cancel, Form $subform ) {
		$this->buildPanel('Administração', 'Gerenciar Almoxarifados');
		$form = $this->buildForm('stockroom-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$input = new TextBox('name');
		$input->setSpan(6);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$control = $this->buildField('Nome', $input);
		$form->remove($control);
		$general->append($control);
		
		$input = new CheckBox('status', 'Ativo');
		$input->setValue(true);
		$control = $this->buildField(null, $input);
		$form->remove($control);
		$general->append($control);
		
		$tab = new Tabbable('stockroom-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Unidades Requisitantes'), null, new TabPane($subform));
		$form->append($tab);
		
		$this->buildButton('submit', 'Incluir', $submit);
		$this->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Stockroom $object ) {
		$data['name'] = $object->getName();
		$data['status'] = $object->getStatus();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Stockroom $object ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setStatus($data['status']);
	}

}
?>