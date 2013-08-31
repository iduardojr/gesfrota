<?php
namespace Sigmat\View\Product;

use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Paragraph;
use Sigmat\View\AbstractList;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use Sigmat\View\BuilderForm;
use PHPBootstrap\Widget\Form\Controls\TextBox;

class AttributeList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $remove
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $remove ) {
		$panel = $this->buildPanel('Administração', 'Gerenciar Atributos');
		$modalConfirm = $this->buildConfirm('confirm-remove', new Paragraph('Deseja realmente excluir esse atributo?'));
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		$input = new TextBox('name');
		$input->setSpan(3);
		$form->buildField('Nome', $input);
		
		$input = new TextBox('description');
		$input->setSpan(3);
		$form->buildField('Descrição', $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), 
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('product-attribute-table');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('name', 'Nome', clone $filter, 300, ColumnText::Left);
		$table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left);
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('remove', new Icon('icon-remove'), $remove, $modalConfirm);
		
	}
	
}
?>