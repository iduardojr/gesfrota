<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Misc\Label;
use Sigmat\View\GUI\AbstractList;
use Sigmat\View\GUI\BuilderForm;
use Sigmat\Model\Domain\Product;

class ProductList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $active
	 * @param Action $search
	 * @param Action $seek
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $active, Action $search, Action $seek ) {
		$panel = $this->buildPanel('Banco de Especificações', 'Gerenciar Produtos');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$modal = new Modal('product-category-search', new Title('Categorias', 3));
		$modal->setWidth(900);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		
		$input = new TextBox('description');
		$input->setSpan(5);
		$form->buildField('Descrição', $input);
		
		$input = array();
		$input[0] = new TextBox('product-category-id');
		$input[0]->setSuggestion(new Seek($seek));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('product-category-description', $search, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(4);
		$form->buildField('Categoria', $input);

		$input = new CheckBox('only-active', 'Apenas produtos ativos');
		$form->buildField(null, $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$modalFilter->setWidth(720);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), 
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('product-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter, null, function( $value ) {
			return str_repeat('0', 5 - strlen($value)) . $value; 
		});
		$table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('category', 'Categoria', null, 350, null, function( $value ) {
			return $value->getFullDescription();
		});
		$table->buildColumnText('active', 'Status', null, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, Product $product ) {
			$button->setIcon(new Icon($product->getActive() ? 'icon-remove' : 'icon-ok'));
		});
		
	}
	
}
?>