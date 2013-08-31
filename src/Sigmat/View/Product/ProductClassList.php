<?php
namespace Sigmat\View\Product;

use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Paragraph;
use Sigmat\View\AbstractList;
use Sigmat\View\BuilderForm;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Form\Controls\Decorator\Suggest;

class ProductClassList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $remove
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $remove ) {
		$panel = $this->buildPanel('Administração', 'Gerenciar Classes de Produto');
		$modalConfirm = $this->buildConfirm('confirm-remove', new Paragraph('Deseja realmente excluir essa Classe de Produto?'));
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');

		$input = new TextBox('description');
		$input->setSpan(3);
		$form->buildField('Descrição', $input);
		
		$input = new TextBox('attribute1');
		$input->setSpan(3);
		$input->setSuggestion(new Suggest(new Action('ddd')));
		$form->buildField('Atributos', $input);
		
		$input = clone $input;
		$input->setName('attribute2');
		$form->buildField(null, $input);
		
		$input = clone $input;
		$input->setName('attribute3');
		$form->buildField(null, $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), 
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('product-class-table');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left);
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('remove', new Icon('icon-remove'), $remove, $modalConfirm);
		
	}
	
}
?>