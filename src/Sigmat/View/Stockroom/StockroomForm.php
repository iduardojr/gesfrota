<?php
namespace Sigmat\View\Stockroom;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use Sigmat\View\AbstractForm;
use Sigmat\Model\Stockroom\Stockroom;
use Sigmat\Model\AdministrativeUnit\AdministrativeUnit;
use Doctrine\ORM\EntityManager;

/**
 * Formulario
 */
class StockroomForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 * @param RequestersUnitsForm $subform
	 */
	public function __construct( Action $submit, Action $cancel, RequestersUnitsForm $subform ) {
		$this->buildPanel('Administração', 'Gerenciar Almoxarifados');
		$form = $this->buildForm('stockroom-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$input = new TextBox('name');
		$input->setSpan(6);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome', $input, null, $general);
		
		$input = new CheckBox('status', 'Ativo');
		$form->buildField(null, $input, null, $general);

		$tab = new Tabbable('stockroom-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Unidades Requisitantes'), null, new TabPane($subform));
		$form->append($tab);
		$form->remove($general);
		
		$form->register($subform->getByName('units'));
		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Stockroom $object ) {
		$data['name'] = $object->getName();
		$data['status'] = $object->getStatus();
		$data['units'] = $object->getUnits();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Stockroom $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setStatus($data['status']);
		$object->removeAllUnit();
		foreach( $data['units'] as $unit ) {
			$unit = $em->find(AdministrativeUnit::getClass(), $unit->getId());
			$object->addUnit($unit);
		}
	}

}
?>