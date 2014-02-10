<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Misc\Title;
use Sigmat\View\GUI\AbstractList;
use Sigmat\View\GUI\BuilderForm;
use Sigmat\Model\Domain\Agency;

class AgencyList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $active
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $active ) {
		$panel = $this->buildPanel('Estrutura Organizacional', 'Gerenciar Orgãos');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('name');
		$input->setSpan(3);
		$form->buildField('Descrição', $input);
		
		$input = new CheckBox('only-active', 'Apenas órgãos ativos');
		$form->buildField(null, $input);
		
		$modal = new Modal('agency-search', new Title('Órgãos', 3));
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), 
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('agency-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('acronym', 'Sigla', clone $filter, 200, ColumnText::Left);
		$table->buildColumnText('name', 'Nome', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, Agency $agency ) {
			$button->setIcon(new Icon($agency->getActive() ? 'icon-remove' : 'icon-ok'));
		});
	}
	
}
?>