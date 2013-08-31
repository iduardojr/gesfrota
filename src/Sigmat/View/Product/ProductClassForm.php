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
use Sigmat\Model\Product\ProductClass;

/**
 * Formulario
 */
class ProductClassForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 * @param AttributesForm $subform
	 */
	public function __construct( Action $submit, Action $cancel, AttributesForm $subform ) {
		$this->buildPanel('Administração', 'Gerenciar Classes de Produto');
		$form = $this->buildForm('product-class-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$input = new TextBox('description');
		$input->setSpan(6);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Descrição', $input, null, $general);
		
		$tab = new Tabbable('product-class-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Atributos'), null, new TabPane($subform));
		$form->append($tab);
		$form->remove($general);
		
		$form->register($subform->getByName('attributes'));
		
		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( ProductClass $object ) {
		$data['description'] = $object->getDescription();
		$data['attributes'] = $object->getAttributes();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( ProductClass $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setDescription($data['description']);
		$object->removeAllAttributes();
		foreach( $data['attributes'] as $attr ) {
			$attr = $em->find(Attribute::getClass(), $attr->getId());
			$object->addAttribute($attr);
		}
	}

}
?>