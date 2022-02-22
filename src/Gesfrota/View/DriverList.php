<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Driver;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\CheckBoxList;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;

class DriverList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $active
	 * @param Action $transfer
	 * @param Action $search
	 * @param Action $password
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $active, Action $search, Action $transfer, Action $password) {
		$this->buildPanel('Minha Frota', 'Gerenciar Motorista');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('name');
		$input->setSpan(3);
		$form->buildField('Motorista', $input);
		
		$input = new TextBox('nif');
		$input->setSpan(2);
		$input->setMask('999.999.999-99');
		$form->buildField('CPF', $input);
		
		$input = new CheckBoxList('vehicles', true);
		$input->setOptions(Driver::getLicenseAllowed());
		$form->buildField('CNH', $input);
		
		$input = new CheckBox('only-active', 'Apenas ativos');
		$form->buildField(null, $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$form = new BuilderForm('transfer-driver-form');
		$form->append(new Panel(null, 'flash-message-driver'));
		
		$input = new TextBox('driver-nif');
		$input->setSuggestion(new Seek($search));
		$input->setSpan(2);
		$input->setMask('999.999.999-99');
		$form->buildField('CPF', $input);
		
		$input = new Output('driver-name');
		$form->buildField('Motorista', $input);
		
		$input = new Output('lotation-description');
		$form->buildField('Órgão de lotação', $input);
		
		$modalTransfer = new Modal('transfer-driver-modal', new Title('Transferir Motorista', 3));
		$modalTransfer->setWidth(650);
		$modalTransfer->setBody($form);
		$modalTransfer->addButton(new Button('Transferir', new TgFormSubmit($transfer, $form), Button::Primary));
		$modalTransfer->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($modalTransfer);
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), 
							array(new Button('Transferir Motorista', new TgModalOpen($modalTransfer), Button::Success)),
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('driver-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('name', 'Motorista', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('nif', 'CPF', clone $filter, 120, ColumnText::Left);
		$table->buildColumnText('cell', 'Celular', clone $filter, 100, ColumnText::Left);
		$table->buildColumnText('vehicles', 'CNH', clone $filter, 150, ColumnText::Left, function ($value) {
			return implode('', $value);
		});
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, Driver $obj ) {
			$button->setIcon(new Icon($obj->getActive() ? 'icon-remove' : 'icon-ok'));
		});
		$table->buildColumnAction('reset', new Icon('icon-asterisk'), $password);
	}
	
}
?>