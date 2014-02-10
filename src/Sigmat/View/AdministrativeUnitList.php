<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use Sigmat\View\GUI\AbstractList;
use Sigmat\View\GUI\BuilderForm;
use Sigmat\Model\Domain\AdministrativeUnit;

class AdministrativeUnitList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $active
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $active ) {
		$panel = $this->buildPanel('Estrutura Organizacional', 'Gerenciar Unidades Administrativas');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('name');
		$input->setSpan(3);
		$form->buildField('Descrição', $input);
		
		$input = new CheckBox('only-active', 'Apenas unidades administrativas ativas');
		$form->buildField(null, $input);
		
		$modal = new Modal('administrative-unit-search', new Title('Unidades Administrativas', 3));
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), 
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('administrative-unit-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId();
		$table->buildColumnText('fullDescription', 'Descrição', null, null, ColumnText::Left);
		$table->buildColumnText('active', 'Status', null, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, AdministrativeUnit $unit ) {
			if ( ! $unit->getActive() ) {
				$button->setIcon(new Icon('icon-ok'));
			} else {
				$button->setIcon(new Icon('icon-remove'));
			}
			if ( $unit->getParent() ) {
				$button->setDisabled(! $unit->getParent()->getActive());
			}
		});
		$table->buildColumnAction('new', new Icon('icon-plus-sign'), clone $new, null);
	}
	
}
?>