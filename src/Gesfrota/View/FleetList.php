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
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\ChosenBox;
use PHPBootstrap\Widget\Button\ButtonGroup;
use PHPBootstrap\Widget\Button\TgButtonRadio;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\RadioButtonList;

class FleetList extends AbstractList {
	
	/**
	 * 
	 * @param Action $filter
	 * @param Action $newVehicle
	 * @param Action $newGear
	 * @param Action $edit
	 * @param Action $active
	 * @param Action $seekVehicle
	 * @param Action $transfer
	 * @param array $optResultCenter
	 * @param array $showAgencies
	 */
	public function __construct( Action $filter, Action $newVehicle, Action $newGear, Action $edit, Action $active, Action $seekVehicle, Action $transfer, array $optResultCenter, array $showAgencies = null ) {
		$this->buildPanel('Minha Frota', 'Gerenciar Veículos e Equipamentos');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$btnGroup = new ButtonGroup();
		
		$btn = new Button('Todos');
		$btn->setName('type_all');
		$btnGroup->addButton($btn);
		
		$btn = new Button('Veículo');
		$btn->setName('type_vehicle');
		$btnGroup->addButton($btn);
		
		$btn = new Button('Equipamento');
		$btn->setName('type_equipment');
		$btnGroup->addButton($btn);
		
		$btnGroup->setToggle(new TgButtonRadio());
		$input = new Hidden('type');
		$form->buildField(null, [$btnGroup, $input])->setName('fleet_types');
		
		if ($showAgencies) {
			$input = new ComboBox('agency');
			$input->setSpan(3);
			$input->setOptions($showAgencies);
			$form->buildField('Órgão', $input);
		}
		if ( $showAgencies || $optResultCenter) {
			$input = new ChosenBox('results-center', true);
			$input->setOptions($optResultCenter);
			$input->setSpan(4);
			$input->setPlaceholder('Selecione uma ou mais opções');
			$input->setTextNoResult('Nenhum resultado encontrado para ');
			$input->setDisabled($optResultCenter ? false : true);
			$form->buildField('Centro de Resultado', $input);
		}
		
		$input = new TextBox('description');
		$input->setSpan(4);
		$form->buildField('Descrição/Placa', $input);
		
		$input = new CheckBoxList('engine', true);
		$input->setOptions(Vehicle::getEnginesAllowed());
		$form->buildField('Motor a', $input);
		
		$input = new CheckBoxList('fleet', true);
		$input->setOptions(Vehicle::getFleetAllowed());
		$form->buildField('Tipo da Frota', $input);
		
		$input = new ComboBox('status');
		$input->setSpan(2);
		$input->addOption(0, 'Todos');
		$input->addOption(1, 'Ativos');
		$input->addOption(-1, 'Inativos');
		$form->buildField('Status', $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$form = new BuilderForm('transfer-vehicle-form');
		
		if ($showAgencies) {
			$showAgencies[''] = 'Selecionar Órgão';
			$input = new ComboBox('agency-to');
			$input->setSpan(2);
			$input->setOptions($showAgencies);
			$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
			$form->buildField('Transferir Para', $input);
		}
		
		$input = new TextBox('vehicle-plate');
		$input->setSuggestion(new Seek($seekVehicle));
		$input->setSpan(2);
		$input->setMask('aaa9*99');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
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
		$table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left, function ($value, FleetItem $obj) {
			if ($obj instanceof Vehicle) {
				$value .= ' <small class="pull-right">' . $obj->getModel()->getCode() . '</small>';
			}
			return $value;
		});
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
		if ($showAgencies) {
			$table->buildColumnText('responsibleUnit', 'Órgão', clone $filter, 70);
		}
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, FleetItem $obj ) {
		    $button->setIcon(new Icon($obj->getActive() ? 'icon-remove' : 'icon-ok'));
		});
	}
	
}
?>