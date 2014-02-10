<?php
namespace Sigmat\View;

use Doctrine\ORM\EntityManager;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Title;
use Sigmat\View\GUI\AbstractForm;
use Sigmat\View\ProductUnitsForm;
use Sigmat\Model\Domain\Product;
use Sigmat\Model\Domain\ProductCategory;
use Sigmat\Model\Domain\ProductUnit;

class ProductForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $search
	 * @param Action $seek
	 * @param Action $cancel
	 */
	public function __construct( Action $submit, Action $search, Action $seek, Action $cancel, ProductUnitsForm $subform ) {
		$this->buildPanel('Banco de Especificações', 'Gerenciar Produtos');
		$form = $this->buildForm('product-form');
		
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
		$input[1]->setSpan(6);
		
		$form->buildField('Categoria Superior', $input, null, $general);
		
		$input = new TextArea('description');
		$input->setSpan(7);
		$input->setRows(3);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Descrição', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$tab = new Tabbable('product-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Unidades de Medida'), null, new TabPane($subform));
		
		$form->append($tab);
		$form->remove($general);
		
		$form->register($subform->getByName('units'));
		
		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Product $object ) {
		$form = $this->component;
		$data['description'] = $object->getDescription();
		$data['active'] = $object->getActive();
		$category = $object->getCategory();
		if ( $category ) {
			$data['product-category-id'] = $category->getCode();
			$data['product-category-description'] = $category->getFullDescription();
		}
		$data['units'] = $object->getUnits();
		$form->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Product $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setDescription($data['description']);
		$object->setActive($data['active']);
		$object->setCategory($em->find(ProductCategory::getClass(), $data['product-category-id']));
		$object->removeAllUnits();
		foreach( $data['units'] as $unit ) {
			$unit = $em->find(ProductUnit::getClass(), $unit->getId());
			$object->addUnit($unit);
		}
	}
}
?>