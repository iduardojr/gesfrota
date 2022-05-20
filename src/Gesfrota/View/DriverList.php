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
use Gesfrota\Model\Domain\AdministrativeUnit;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\ChosenBox;

class DriverList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $active
	 * @param Action $search
	 * @param Action $transfer
	 * @param Action $password
	 * @param array $optResultCenter
	 * @param array $showAgencies
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $active, Action $search, Action $transfer, Action $password, array $optResultCenter, array $showAgencies = null) {
		$this->buildPanel('Minha Frota', 'Gerenciar Motorista');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		if ($showAgencies) {
			$input = new ComboBox('agency');
			$input->setSpan(2);
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
		
		$input = new TextBox('name');
		$input->setSpan(4);
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
		
		if ($showAgencies) {
			$showAgencies[''] = 'Selecionar Órgão';
			$input = new ComboBox('agency-to');
			$input->setSpan(2);
			$input->setOptions($showAgencies);
			$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
			$form->buildField('Transferir Para', $input);
		}
		
		$input = new TextBox('driver-nif');
		$input->setSuggestion(new Seek($search));
		$input->setSpan(2);
		$input->setMask('999.999.999-99');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
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
		$table->buildColumnText('nif', 'CPF', clone $filter, 120);
		$table->buildColumnText('cell', 'Celular', clone $filter, 100);
		$table->buildColumnText('vehicles', 'CNH', clone $filter, 80, null, function ($value) {
			return implode('', $value);
		});
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		if ($showAgencies) {
			$table->buildColumnText('lotation', 'Órgão', null, 100, null, function (AdministrativeUnit $value) {
				return (string) $value->getAgency();
			});
		}
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, Driver $obj ) {
			$button->setIcon(new Icon($obj->getActive() ? 'icon-remove' : 'icon-ok'));
		});
		$table->buildColumnAction('reset', new Icon('icon-asterisk'), $password);
	}
	
}
?>