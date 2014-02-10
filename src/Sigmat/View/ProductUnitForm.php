<?php
namespace Sigmat\View;

use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\GUI\AbstractForm;
use Sigmat\Model\Domain\ProductUnit;

/**
 * Formulario
 */
class ProductUnitForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 * @param VariationOptionsForm $subform
	 */
	public function __construct( Action $submit, Action $cancel) {
		$panel = $this->buildPanel('Banco de Especificações', 'Gerenciar Unidades de Medida');
		$form = $this->buildForm('product-unit-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$input = new TextBox('description');
		$input->setSpan(4);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Descrição', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$tab = new Tabbable('product-unit-tabs');
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
	public function extract( ProductUnit $object ) {
		$data['description'] = $object->getDescription();
		$data['active'] = $object->getActive();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( ProductUnit $object ) {
		$data = $this->component->getData();
		$object->setDescription($data['description']);
		$object->setActive($data['active']);
	}

}
?>