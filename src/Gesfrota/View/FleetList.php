<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Dropdown\Dropdown;
use PHPBootstrap\Widget\Dropdown\DropdownLink;
use PHPBootstrap\Widget\Dropdown\TgDropdown;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\CheckBoxList;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Badge;

class FleetList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $newVehicle
	 * @param Action $newGear
	 * @param Action $edit
	 * @param Action $active
	 */
	public function __construct( Action $filter, Action $newVehicle, $newGear, Action $edit, Action $active, Action $seekVehicle, Action $transfer  ) {
		$this->buildPanel('Minha Frota', 'Gerenciar Veículos e Equipamentos');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('description');
		$input->setSpan(4);
		$form->buildField('Descrição', $input);
		
		$input = new CheckBoxList('engine', true);
		$input->setOptions(Vehicle::getEnginesAllowed());
		$form->buildField('Motor a', $input);
		
		$input = new CheckBoxList('fleet', true);
		$input->setOptions(Vehicle::getFleetAllowed());
		$form->buildField('Tipo da Frota', $input);
		
		$input = new CheckBox('only-active', 'Apenas ativos');
		$form->buildField(null, $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$form = new BuilderForm('transfer-vehicle-form');
		$input = new TextBox('vehicle-plate');
		$input->setSuggestion(new Seek($seekVehicle));
		$input->setSpan(2);
		$input->setMask('aaa-9*99');
		$form->buildField('Placa', $input);
		
		$input = new Output('vehicle-description');
		$form->buildField('Veículo', $input);
		
		$input = new Output('responsible-unit-description');
		$form->buildField('Unidade Responsável', $input);
		
		$modalTransfer = new Modal('transfer-vehicle-modal', new Title('Transferir Veículo', 3));
		$modalTransfer->setWidth(650);
		$modalTransfer->setBody($form);
		$modalTransfer->addButton(new Button('Transferir', new TgFormSubmit($transfer, $form), Button::Primary));
		$modalTransfer->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($modalTransfer);
		
		$drop = new Dropdown();
		$drop->addItem(new DropdownLink('Veículo', new TgLink($newVehicle)));
		$drop->addItem(new DropdownLink('Equipamento', new TgLink($newGear)));
		
		$this->buildToolbar(array(new Button('Novo', null, Button::Primary), new Button('', new TgDropdown($drop), Button::Primary)),
							array(new Button('Transferir Veículo', new TgModalOpen($modalTransfer), Button::Success)),
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('owner-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('class', null, null, 70, null, function ( $value ) {
			$label = new Badge(constant($value.'::FLEET_TYPE'));
			if ($value == Vehicle::getClass()) {
				$label->setStyle(Badge::Info);
			} else {
				$label->setStyle(Badge::Warning);
			}
			return $label;
		});
		$table->buildColumnText('fleet', 'Frota', clone $filter, 100, null, function ($value) {
		    return FleetItem::getFleetAllowed()[$value];
		});
		
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, FleetItem $obj ) {
		    $button->setIcon(new Icon($obj->getActive() ? 'icon-remove' : 'icon-ok'));
		});
	}
	
}
?>