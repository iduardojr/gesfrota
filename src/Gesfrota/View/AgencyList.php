<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Agency;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;

class AgencyList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $transferFleet
	 * @param Action $transferUser
	 * @param Action $edit
	 * @param Action $active
	 */
	public function __construct( Action $filter, Action $new, Action $transferFleet, Action $transferUser, Action $edit, Action $active) {
		$this->buildPanel('Estrutura Organizacional', 'Gerenciar Orgãos');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('name');
		$input->setSpan(3);
		$form->buildField('Descrição', $input);
		
		$input = new ComboBox('status');
		$input->setSpan(2);
		$input->addOption(0, 'Todos');
		$input->addOption(1, 'Ativos');
		$input->addOption(-1, 'Inativos');
		$form->buildField('Status', $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary),
							new Button('Transferir Frota', new TgLink($transferFleet), Button::Success),
							new Button('Transferir Usuários', new TgLink($transferUser), Button::Success),
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