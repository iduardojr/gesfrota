<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\Model\Domain\Place;
use Gesfrota\Model\Domain\RequestFreight;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\Direction;
use Gesfrota\View\Widget\DynInput;
use Gesfrota\View\Widget\DynInputAdd;
use Gesfrota\View\Widget\PlaceInput;
use Gesfrota\View\Widget\WaypointsInput;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Format\TimeFormat;
use PHPBootstrap\Validate\Measure\Max;
use PHPBootstrap\Validate\Measure\Min;
use PHPBootstrap\Validate\Measure\Range;
use PHPBootstrap\Validate\Measure\Ruler\RulerLength;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Validate\Pattern\Time;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\DateBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\TimeBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use Gesfrota\Model\Domain\ResultCenter;

class RequestFreightForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $location
	 * @param Action $seekUnit
	 * @param Action $searchUnit
	 * @param Action $seekAgency
	 * @param Action $searchAgency
	 * @param array $optMaps
	 * @param array $optResultCenter
	 * @param integer $showLevelUnit
	 */
	public function __construct( Action $submit, Action $cancel, Action $location, Action $seekUnit, Action $searchUnit, Action $seekAgency, Action $searchAgency, array $optMaps, array $optResultCenter, $isResultCenterRequired, $showLevelUnit) {
		$this->buildPanel('Minhas Viagens', 'Nova Entrega');
		$form = $this->buildForm('request-freight-form');
		
		$itinerary = new Fieldset('Itinerário');
		
		$directions = new Direction('directions', '{A}');
		$directions->setOptions($optMaps);
		
		$input = new PlaceInput('from', $location);
		$input->setPlaceholder('Infome o local de partida');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$directions->setFrom($input);
		$form->buildField('De', $input, null, $itinerary);
		
		$input = new WaypointsInput('waypoints', $location);
		$input->setLabel('<i class="icon-waypoint"></i>');
		$input->setLength(new Range(0, 8));
		$input->getComponent()->setPlaceholder('Infome o local de parada');
		$input->getComponent()->setSpan(7);
		$directions->setWay($input);
		$itinerary->append($input);
		$form->register($input);
		
		$input = new PlaceInput('to', $location);
		$input->setPlaceholder('Infome o local de destino');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$directions->setTo($input);
		$form->buildField('Para', [$input, new Button('Adicionar Parada', new DynInputAdd($directions->getWay()), [Button::Mini])], null, $itinerary);
		
		$form->buildField(null, $directions, null, $itinerary);
		
		$input = [];
		$input[1] = new DateBox('schedule-date', new Date(new DateFormat('dd/mm/yyyy')));
		$input[1]->setSpan(2);
		$input[1]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		
		$input[2] = new TimeBox('schedule-time', new Time(new TimeFormat('HH:mm')));
		$input[2]->setSpan(1);
		$input[2]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Início', $input, null, $itinerary);
		
		$package = new Fieldset('Itens e Encomendas');
		
		$input = new DynInput('items');
		$input->getComponent()->setPlaceholder('Descreva o item a ser transportado');
		$input->getComponent()->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->getComponent()->setSpan(7);
		$input->setLength(new Min(1));
		
		$form->buildField(null, $input, null, $package);
		
		$input = new Button('Adicionar', new DynInputAdd($input), [Button::Mini]);
		$form->buildField(null, $input, null, $package);
		
		$service = new Fieldset('Serviço a Executar');
		
		if ( !$showLevelUnit ) {
			$required = new Hidden('result-center-required');
			$required->setValue($isResultCenterRequired ? '1' : null);
			
			$input = new ComboBox('result-center-id');
			$input->setOptions($optResultCenter);
			$input->setSpan(7);
			$input->setRequired(new Required($required, 'Por favor, preencha esse campo'));
			$form->buildField('Centro de Resultado', [$input, $required], null, $service)->setName('results-center-group');
			$form->unregister($required);
		}
		
		$input = new TextArea('service');
		$input->setLength(new Max(250, 'Max. 250 caracteres', RulerLength::getInstance()));
		$input->setPlaceholder('Descreva intruções de envio para o motorista (Max. 250 caracteres)');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Instruções de Envio', $input, null, $service);
		
		$tab = new Tabbable('request-trip-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Itinerário'), null, new TabPane($itinerary));
		$tab->addItem(new NavLink('Encomenda'), null, new TabPane($package));
		$tab->addItem(new NavLink('Serviço'), null, new TabPane($service));
		
		if ($showLevelUnit) {
			$requester = new Fieldset('Unidade Requisitante');
			
			if ($showLevelUnit == 2) {
				$modal = new Modal('agency-search', new Title('Órgãos', 3));
				$modal->setWidth(600);
				$modal->addButton(new Button('Cancelar', new TgModalClose()));
				$form->append($modal);
				
				$input = [];
				$input[0] = new TextBox('agency-id');
				$input[0]->setSuggestion(new Seek($seekAgency));
				$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
				$input[0]->setSpan(1);
				
				$input[1] = new SearchBox('agency-name', $searchAgency, $modal);
				$input[1]->setEnableQuery(false);
				$input[1]->setSpan(6);
				
				$form->buildField('Órgão', $input, null, $requester);
			}
			
			$modal = new Modal('administrative-unit-search', new Title('Unidades Administrativas', 3));
			$modal->setWidth(900);
			$modal->addButton(new Button('Cancelar', new TgModalClose()));
			$form->append($modal);
			
			$input = [];
			$input[0] = new TextBox('administrative-unit-id');
			$input[0]->setSuggestion(new Seek($seekUnit));
			$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
			$input[0]->setSpan(1);
			
			$input[1] = new SearchBox('administrative-unit-name', $searchUnit, $modal);
			$input[1]->setEnableQuery(false);
			$input[1]->setSpan(6);
			
			$form->buildField('Unidade Administrativa', $input, null, $requester);
			
			$required = new Hidden('result-center-required');
			$required->setValue($isResultCenterRequired ? '1' : null);
			
			$input = new ComboBox('result-center-id');
			$input->setOptions($optResultCenter);
			$input->setSpan(7);
			$input->setRequired(new Required($required, 'Por favor, preencha esse campo'));
			$form->buildField('Centro de Resultado', [$input, $required], null, $requester)->setName('results-center-group');
			$form->unregister($required);
			
			$form->buildField("<br>", [], null, $requester);
			
			$tab->addItem(new NavLink('Requisitante'), null, new TabPane($requester));
		}
		
		$form->append($tab);

		$submit->setParameter('to', 'send');
		$form->buildButton('submit', 'Enviar itens', $submit, Button::Success );
		
		$submit = clone $submit;
		$submit->setParameter('to', 'receive');
		
		$form->buildButton('submit', 'Receber itens', $submit, Button::Success);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( RequestFreight $object ) {
		$data = [];
		$data['from'] = $object->getFrom();
		$data['to'] = $object->getTo();
		$data['waypoints'] = $object->getWaypoints();
		$schedule = $object->getSchedule();
		if ( !$schedule ) {
			$schedule = new \DateTime('+30min');
			$schedule->setTime($schedule->format('H'), ceil($schedule->format('i')/$this->getStep())*$this->getStep());
		} 
		if ($object->getRequesterUnit()) {
			$data['agency-id'] = $object->getRequesterUnit()->getAgency()->getCode();
			$data['agency-name'] = $object->getRequesterUnit()->getAgency()->getName();
			
			$data['administrative-unit-id'] = $object->getRequesterUnit()->getCode();
			$data['administrative-unit-name'] = $object->getRequesterUnit()->getName();
		}
		if ($object->getResultCenter()) {
			$data['result-center-id'] = $object->getResultCenter()->getId();
		}
		$data['schedule-date'] = $data['schedule-time'] = $schedule;
		$data['items'] = $object->getItems();
		$data['service'] = $object->getService();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( RequestFreight $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setFrom(new Place($data['from']['place'], $data['from']['description']));
		$object->setTo(new Place($data['to']['place'], $data['to']['description']));
		$waypoints = [];
		foreach($data['waypoints'] as $point) {
			$waypoints[] = new Place($point['place'], $point['description']);
		}
		if ($data['administrative-unit-id']) {
			$unit = $em->find(AdministrativeUnit::getClass(), $data['administrative-unit-id']);
			$object->setRequesterUnit($unit);
		}
		if ($data['result-center-id']) {
			$object->setResultCenter($em->find(ResultCenter::getClass(), $data['result-center-id']));
		}
		$object->setWaypoints($waypoints);
		$object->setSchedule(new \DateTime($data['schedule-date'] . ' ' . $data['schedule-time']));
		$object->setItems($data['items']);
		$object->setService($data['service']);
	}
	
	protected function getStep() {
		return $this->getBuilderForm()->getControl('schedule-time')->getMinuteStep();
	}

}
?>