<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Place;
use Gesfrota\Model\Domain\RequestTrip;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\Direction;
use Gesfrota\View\Widget\DynInput;
use Gesfrota\View\Widget\DynInputAdd;
use Gesfrota\View\Widget\PlaceInput;
use Gesfrota\View\Widget\WaypointsInput;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Format\TimeFormat;
use PHPBootstrap\Validate\Measure\Max;
use PHPBootstrap\Validate\Measure\Range;
use PHPBootstrap\Validate\Measure\Ruler\RulerLength;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Validate\Pattern\Time;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\DateBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use PHPBootstrap\Widget\Form\Controls\TimeBox;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use Doctrine\ORM\EntityManager;

class RequestTripForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $location
	 * @param array $options
	 */
	public function __construct( Action $submit, Action $cancel, Action $location, array $options ) {
		$this->buildPanel('Minhas Viagens', 'Nova Viagem');
		$form = $this->buildForm('request-trip-form');
		
		$itinerary = new Fieldset('Itinerário');
		
		$directions = new Direction('directions', '{A}');
		$directions->setOptions($options);
		
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
		
		$passangers = new Fieldset('Passageiros');
		
		$input = new DynInput('passangers');
		$input->getComponent()->setPlaceholder('Informe o nome do passageiro');
		$input->getComponent()->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->getComponent()->setSpan(5);
		$input->setLength(new Range(1, 4));
		
		$form->buildField(null, $input, null, $passangers);
		
		$input = new Button('Adicionar', new DynInputAdd($input), [Button::Mini]);
		$form->buildField(null, $input, null, $passangers);
		
		$service = new Fieldset('Serviço a Executar');
		
		$input = new TextArea('service');
		$input->setLength(new Max(250, 'Max. 250 caracteres', RulerLength::getInstance()));
		$input->setPlaceholder('Descreva o serviço a ser executado (Max. 250 caracteres)');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Serviço', $input, null, $service);
		
		$input = new ComboBox('duration');
		$input->setOptions(['+0 min' => '0 minuto', 
							'+30 min' => '30 minutos', 
							'+1 hour' => '1 hora', 
							'+90 min' => '90 minutos', 
							'+2 hour' => '2 horas', 
							'+3 hour' => '3 horas', 
							'+4 hour' => '4 horas',
							'6pm' => 'O dia inteiro',
							'custom' => 'Personalizado']);
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Duração', $input, null, $service);
		
		$input = [];
		$input[1] = new DateBox('duration-date', new Date(new DateFormat('dd/mm/yyyy')));
		$input[1]->setSpan(2);
		
		$input[2] = new TimeBox('duration-time', new Time(new TimeFormat('HH:mm')));
		$input[2]->setSpan(1);
		$form->buildField('Termina em', $input, null, $service)->setName('duration-group');
		
		$tab = new Tabbable('request-trip-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Itinerário'), null, new TabPane($itinerary));
		$tab->addItem(new NavLink('Passageiros'), null, new TabPane($passangers));
		$tab->addItem(new NavLink('Serviço'), null, new TabPane($service));
		
		$form->append($tab);

		$form->buildButton('submit', 'Solicitar Viagem ', $submit);
		$submit = clone $submit;
		$submit->setParameter('round-trip', 1);
		$form->buildButton('submit', 'Solicitar Ida e Volta', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( RequestTrip $object ) {
		$data = [];
		$data['from'] = $object->getFrom();
		$data['to'] = $object->getTo();
		$data['waypoints'] = $object->getWaypoints();
		$schedule = $object->getSchedule();
		if ( !$schedule ) {
			$schedule = new \DateTime('+30min');
			$schedule->setTime($schedule->format('H'), ceil($schedule->format('i')/$this->getStep())*$this->getStep());
		} 
		$data['schedule-date'] = $data['schedule-time'] = $schedule;
		$data['passangers'] = $object->getPassengers();
		$data['service'] = $object->getService();
		if ($object->getDuration()) {
			$data['duration'] = 'custom';
			$data['duration-date'] = $data['duration-time'] = $object->getDuration();
		} else {
			$data['duration'] = '+30 min';
		}
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( RequestTrip $object, EntityManager $em) {
		$data = $this->component->getData();
		$object->setFrom(new Place($data['from']['place'], $data['from']['description']));
		$object->setTo(new Place($data['to']['place'], $data['to']['description']));
		$waypoints = [];
		foreach($data['waypoints'] as $point) {
			$waypoints[] = new Place($point['place'], $point['description']);
		}
		$object->setWaypoints($waypoints);
		$object->setSchedule(new \DateTime($data['schedule-date'] . ' ' . $data['schedule-time']));
		$object->setPassengers($data['passangers']);
		
		$object->setService($data['service']);
		if ($data['duration'] == 'custom') {
			$object->setDuration(new \DateTime($data['duration-date'] . ' ' . $data['duration-time']));
		} else {
			$object->setDuration(new \DateTime($data['schedule-date'] . ' ' . $data['schedule-time'] . ' ' . $data['duration']));
		}
	}
	
	protected function getStep() {
		return $this->getBuilderForm()->getControl('schedule-time')->getMinuteStep();
	}

}
?>