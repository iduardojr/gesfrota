<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Owner;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Dropdown\Dropdown;
use PHPBootstrap\Widget\Dropdown\DropdownLink;
use PHPBootstrap\Widget\Dropdown\TgDropdown;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;
use Gesfrota\Model\Domain\OwnerCompany;

class OwnerList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $newPerson
	 * @param Action $newCompany
	 * @param Action $edit
	 * @param Action $active
	 */
	public function __construct( Action $filter, Action $newPerson, $newCompany, Action $edit, Action $active ) {
		$this->buildPanel('Entidades Externas', 'Gerenciar Proprietários');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('name');
		$input->setSpan(3);
		$form->buildField('Proprietário', $input);
		
		$input = new TextBox('nif');
		$input->setSpan(3);
		$form->buildField('CPF/CNPJ', $input);
		
		$input = new CheckBox('only-active', 'Apenas ativos');
		$form->buildField(null, $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$drop = new Dropdown();
		$drop->addItem(new DropdownLink('Pessoa Física', new TgLink($newPerson)));
		$drop->addItem(new DropdownLink('Pessoa Jurídica', new TgLink($newCompany)));
		
		$this->buildToolbar(array(new Button('Novo', null, Button::Primary), new Button('', new TgDropdown($drop), Button::Primary)),
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('owner-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('name', 'Proprietário', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('nif', 'CPF/CNPJ', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit, null, function (Button $button, Owner $obj) {
			if ($obj instanceof OwnerCompany && $obj->isReadOnly()) {
				$button->setDisabled(true);
			}
			
		});
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, Owner $obj ) {
			if ($obj instanceof OwnerCompany && $obj->isReadOnly()) {
				$button->setDisabled(true);
			}
		    $button->setIcon(new Icon($obj->getActive() ? 'icon-remove' : 'icon-ok'));
		});
	}
	
}
?>