<?php
namespace Sigmat\View\Product;

use Doctrine\ORM\EntityManager;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use Sigmat\View\AbstractForm;
use Sigmat\Model\Product\Attribute;

/**
 * Formulario
 */
class AttributeForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 * @param AttributeOptionsForm $subform
	 */
	public function __construct( Action $submit, Action $cancel, AttributeOptionsForm $subform ) {
		$this->buildPanel('Administração', 'Gerenciar Atributos');
		$form = $this->buildForm('product-attribute-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$input = new TextBox('name');
		$input->setSpan(3);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome', $input, null, $general);
		
		$input = new TextBox('description');
		$input->setSpan(6);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Descrição', $input, null, $general);
		
		$tab = new Tabbable('product-attribute-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Opções do Atributo'), null, new TabPane($subform));
		$form->append($tab);
		$form->remove($general);
		
		$form->register($subform->getByName('options'));
		
		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Attribute $object ) {
		$data['name'] = $object->getName();
		$data['description'] = $object->getDescription();
		$data['options'] = $object->getOptions();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Attribute $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setDescription($data['description']);
		$object->removeAllOptions();
		foreach( $data['options'] as $option ) {
			if ( $option->getId() > 0 ) {
				$option->setAttribute($object);
				$em->merge($option);
			} else {
				$object->addOption($option);
			}
		}
	}

}
?>