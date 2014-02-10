<?php
namespace Sigmat\View;

use Doctrine\ORM\EntityManager;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use Sigmat\View\GUI\AbstractForm;
use Sigmat\Model\Domain\Stockroom;

/**
 * Formulario
 */
class StockroomForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 */
	public function __construct( Action $submit, Action $cancel ) {
		$this->buildPanel('Gestão de Estoques', 'Gerenciar Almoxarifados');
		$form = $this->buildForm('stockroom-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$input = new TextBox('name');
		$input->setSpan(6);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$form->buildField(null, $input, null, $general);

		$tab = new Tabbable('stockroom-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$form->append($tab);
		$form->remove($general);

		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Stockroom $object ) {
		$data['name'] = $object->getName();
		$data['active'] = $object->getActive();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Stockroom $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setActive($data['active']);
	}

}
?>