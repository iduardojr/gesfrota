<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Request;
use Gesfrota\Model\Domain\RequestTrip;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Button\TgButtonRadio;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Tooltip\Tooltip;
use PHPBootstrap\Widget\Form\Controls\CheckBoxList;
use PHPBootstrap\Widget\Form\Controls\DateBox;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Widget\Button\ButtonGroup;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Action\TgWindows;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use Gesfrota\Model\Domain\AdministrativeUnit;
use PHPBootstrap\Widget\Form\Controls\ChosenBox;

class RequestList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $newTrip
	 * @param Action $newFreight
	 * @param Action $cancel
	 * @param Action $print
	 * @param array $optResultCenter
	 * @param Action $do
	 * @param \Closure $closure
	 * @param array $showAgencies
	 */
	public function __construct( Action $filter, Action $newTrip, Action $newFreight, Action $cancel, Action $print, array $optResultCenter, Action $do = null, $closure = null, array $showAgencies = null ) {
		$this->buildPanel('Minhas Viagens', 'Gerenciar Requisições');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$btnGroup = new ButtonGroup();
		
		$btn = new Button('Todos');
		$btn->setName('type_all');
		$btnGroup->addButton($btn);
		
		$btn = new Button('Viagem');
		$btn->setName('type_trip');
		$btnGroup->addButton($btn);
		
		$btn = new Button('Entrega');
		$btn->setName('type_freight');
		$btnGroup->addButton($btn);
		
		$btnGroup->setToggle(new TgButtonRadio());
		$input = new Hidden('type');
		$form->buildField(null, [$btnGroup, $input])->setName('request_types');
		
		if ($showAgencies) {
			$input = new ComboBox('agency');
			$input->setSpan(2);
			$input->setOptions($showAgencies);
			$form->buildField('Órgão', $input);
		}
		
		if ( $showAgencies || $optResultCenter) {
			$input = new ChosenBox('results-center', true);
			$input->setOptions($optResultCenter);
			$input->setSpan(5);
			$input->setPlaceholder('Selecione uma ou mais opções');
			$input->setTextNoResult('Nenhum resultado encontrado para ');
			$input->setDisabled($optResultCenter ? false : true);
			$form->buildField('Centro de Resultado', $input);
		}
		
		$input = new TextBox('from');
		$input->setSpan(5);
		$form->buildField('De', $input);
		
		$input = new TextBox('to');
		$input->setSpan(5);
		$form->buildField('Para', $input);
		
		$input = [];
		$input[1] = new DateBox('date-initial', new Date(new DateFormat('dd/mm/yyyy')));
		$input[1]->setSpan(2);
		
		$input[2] = new DateBox('date-final', new Date(new DateFormat('dd/mm/yyyy')));
		$input[2]->setSpan(2);
		$form->buildField('Período', $input);
		
		$input = new CheckBoxList('status', true);
		$input->setOptions(Request::getStatusAllowed());
		$form->buildField('Status', $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$modalFilter->setWidth(750);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar([new Button('Nova Viagem', new TgLink($newTrip), Button::Primary)],
							new Button('Nova Entrega', new TgLink($newFreight), Button::Success),
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('request-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		
		$table->buildColumnText('itinerary', 'Itinerário', null, null, ColumnText::Left, function ( $value ) {
			$points[] = '<div class="text-inline place-from">' . array_shift($value) . '</div>';
			$points[] = '<div class="text-inline place-to">' . array_pop($value) . '</div>';
			return implode('', $points);
		});
		$table->buildColumnText('requestType', null, null, 60, null, function ( $value ) {
			return new Label($value, $value == RequestTrip::REQUEST_TYPE ? Label::Info : Label::Success);
		});
		$table->buildColumnText('openedAt', 'Aberto em', clone $filter, 150, null, function ( $value ) {
			return $value->format('d/m/Y H:i');
		});
		$table->buildColumnText('status', 'Status', clone $filter, 70, null, function ( $value ) {
			return Request::getStatusAllowed()[$value];
		});
		if ($showAgencies) {
			$table->buildColumnText('requesterUnit', 'Órgão', null, 80, null, function (AdministrativeUnit $value) {
				return (string) $value->getAgency();
			});
		}
		if ($do) {
			$table->buildColumnAction('do', new Icon('icon-remove'), $do, null, $closure);
		}
		$table->buildColumnAction('cancel', new Icon('icon-remove'), $cancel, null, function (Button $btn, Request $obj) {
			$allowed = $obj->getStateAllowed();
			
			if (isset($allowed[Request::CANCELED])) {
				$btn->setIcon(new Icon($allowed[Request::CANCELED][0]));
				$btn->setTooltip(new Tooltip($allowed[Request::CANCELED][1]));
			} else {
				$btn->setDisabled(true);
				$btn->setTooltip(new Tooltip($obj->getRequestType() . ' Encerrada'));
			}
			
		});
		$table->buildColumnAction('print', new Icon('icon-print'), new TgWindows($print, 1024, 720));
	}
	
}
?>