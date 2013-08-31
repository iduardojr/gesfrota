<?php
namespace Sigmat\View\Product;

use Doctrine\ORM\EntityManager;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Form\ListBox;
use Sigmat\View\AbstractForm;
use Sigmat\Model\Product\Product;
use Sigmat\Model\Product\Category;

class ProductForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $seekCategory
	 * @param Action $searchCategory
	 */
	public function __construct( Action $submit, Action $cancel, Action $seekCategory, Action $searchCategory ) {
		$this->buildPanel('Administração', 'Gerenciar Produtos');
		$form = $this->buildForm('product-form');
		
		$general = new Fieldset('Dados Gerais');
		$general->setName('product-class');
		
		$input = new TextArea('description');
		$input->setSpan(6);
		$input->setRows(3);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Descrição', $input, null, $general);
		
		$modal = new Modal('category-search', new Title('Categorias', 3));
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		
		$input = array();
		$input[0] = new TextBox('category-id');
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setSuggestion(new Seek($seekCategory));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('category-description', $searchCategory, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(5);
		$form->buildField('Categoria', $input, null, $general);
		
		$tab = new Tabbable('product-tabs');
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
	public function extract( Product $object ) {
		$form = $this->component;
		$data['description'] = $object->getDescription();
		if ( $object->getCategory() ) {
			$data['category-id'] = $object->getCategory()->getId();
			$data['category-description'] = $object->getCategory()->getDescription();
		}
		
		$productClass = $object->getProductClass();
		$attributes = new Fieldset('Classe ' . $productClass );
		foreach( $productClass->getAttributes() as $attr ) {
			$input = new ListBox(null);
			$input->setSpan(5);
			$input->setDisabled(true);
			foreach( $attr->getOptions() as $option ) {
				$input->addOption($option->getId(), $option->getDescription());
			}
			$input->setRows(count($attr->getOptions()));
			$form->buildField($attr->getDescription(), $input, null, $attributes);
		}
		
		$form->remove($attributes);
		$form->getByName('product-tabs')
		     ->addItem(new NavLink('Classe ' . $productClass), null, new TabPane($attributes));
		$form->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Product $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setDescription($data['description']);
		$category = empty($data['category-id']) ? null : $em->find(Category::getClass(), $data['category-id']);
		$object->setCategory($category);
	}
}
?>