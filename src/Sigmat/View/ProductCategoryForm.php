<?php
namespace Sigmat\View;

use Doctrine\ORM\EntityManager;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Title;
use Sigmat\View\GUI\AbstractForm;
use Sigmat\Model\Domain\ProductCategory;

/**
 * Formulario
 */
class ProductCategoryForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $search
	 * @param Action $seek
	 * @param Action $cancel
	 */
	public function __construct( Action $submit, Action $search, Action $seek, Action $cancel ) {
		$this->buildPanel('Banco de Especificações', 'Gerenciar Categorias');
		$form = $this->buildForm('product-category-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$input = array();
		$input[0] = new TextBox('product-category-id');
		$input[0]->setSuggestion(new Seek($seek));
		$input[0]->setSpan(1);
		
		$modal = new Modal('product-category-search', new Title('Categorias', 3));
		$modal->setWidth(900);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		
		$input[1] = new SearchBox('product-category-description', $search, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(5);
		
		$form->buildField('Categoria Superior', $input, null, $general);
		
		$input = new TextBox('description');
		$input->setSpan(6);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Descrição', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$tab = new Tabbable('product-category-tabs');
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
	public function extract( ProductCategory $object ) {
		$data['description'] = $object->getDescription();
		$data['active'] = $object->getActive();
		$parent = $object->getParent();
		if ( $parent ) {
			$data['product-category-id'] = str_repeat('0', 3 - strlen($parent->getId())) . $parent->getId();
			$data['product-category-description'] = $parent->getFullDescription();
		}
		$this->component->setData($data);
	}
	
	
	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( ProductCategory $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setDescription($data['description']);
		$object->setActive($data['active']);
		$object->setParent($em->find(ProductCategory::getClass(), $data['product-category-id']));
	}

}
?>