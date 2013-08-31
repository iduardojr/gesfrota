<?php
namespace Sigmat\View\Product;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\AbstractForm;
use PHPBootstrap\Widget\Form\Controls\Uneditable;
use Sigmat\Model\Product\Category;

/**
 * Formulario
 */
class CategoryForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 */
	public function __construct( Action $submit, Action $cancel ) {
		$this->buildPanel('Administração', 'Gerenciar Categorias');
		$form = $this->buildForm('product-category-form');
		
		$input = new Uneditable('parent');
		$input->setSpan(9);
		$form->buildField('Categoria Superior', $input);
		
		$input = new TextBox('name');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome', $input);
		
		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Category $object ) {
		$data['name'] = $object->getName();
		$parent = $object->getAncestors();
		$data['parent'] = implode(' / ', empty($parent) ? array('<em>root</em>') : $parent);
		$this->component->setData($data);
	}
	
	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Category $object ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
	}

}
?>