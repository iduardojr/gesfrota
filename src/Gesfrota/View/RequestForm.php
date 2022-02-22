<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Request;
use Gesfrota\Model\Domain\RequestFreight;
use Gesfrota\Model\Domain\RequestTrip;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\BuilderForm;
use Gesfrota\View\Widget\Direction;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Image;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalOpen;


class RequestForm extends AbstractForm {
	
	/**
	 * @var RequestFieldSetStep
	 */
	private $step;
	
	/**
	 * @var User
	 */
	private $user;
	
	/**
	 * @param Request $request
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $decline
	 * @param RequestFieldSetStep $step
	 */
	public function __construct(Request $request, Action $submit, Action $cancel, Action $decline = null, RequestFieldSetStep $step = null ) {
		$this->step = $step;
		if ($this->step) {
			$text = 'Minhas Viagens';
			$subtext = $request->getRequestType() . ' #' .$request->getCode();
		} else {
			$text = $request->getRequestType() . ' #' .$request->getCode();
			$subtext = Request::getStatusAllowed()[$request->getStatus()] . ' em '.$request->getUpdateAt()->format('d/m/Y H:i');
		}
		$panel = $this->buildPanel($text, $subtext);
		if (! $this->step) {
			$header = new Row(true);
			$header->setName('page-header');
			$header->append(new Box(1, new Image('/images/brasao-go.png')));
			$header->append(new Box(11, new Title('Estado de Goiás<br>'. $request->getRequesterUnit()->getAgency()->getName(), 1)));
			$panel->prepend($header);
		}
		$form = $this->buildForm('request-form');
		
		if ( $request->getCanceledAt() ) {
			$input = new Output('justify');
			$input->setValue(nl2br($request->getJustify()));
			$form->buildField('Justificativa', $input);
			$form->unregister($input);
			
			if ($request->getStatus() == Request::CANCELED ) {
				$input = new Output('canceled-by');
				$input->setValue($request->getCanceledBy()->getName());
				$form->buildField('Cancelado por', $input);
				$form->unregister($input);
			} 
		}
		
		$itinerary = new Fieldset('Itinerário');
		$itinerary->setName('itinerary');
		
		$input = new Output('schedule');
		$value[] = '<span class="schedule">' . $request->getSchedule()->format('d/m/Y H:i') . '</span>';
		
		if ($request instanceof RequestTrip && $request->getDuration() > $request->getSchedule()) {
			$value[] = '<span class="duration"><label>' . 'Termina em </label>' . $request->getDuration()->format('d/m/Y H:i') . '</span>';
		}
		$input->setValue(implode('', $value));
		$form->buildField('Agendado para', $input, null, $itinerary);
		$form->unregister($input);
		$value = null;
		
		$directions = new Direction('directions', '{A}');
		
		$input = new Output('from');
		$input->setValue($request->getFrom()->getDescription());
		$options['origin'] = ['placeId' => $request->getFrom()->getPlace()];
		$form->buildField('<i class="icon-place-from"></i>', $input, null, $itinerary)->setName('no-margin');
		$form->unregister($input);
		
		foreach($request->getWaypoints() as $key => $point) {
			$input = new Output('waypoint-' .$key);
			$input->setValue($point->getDescription());
			$options['waypoints'][] = ['location' => ['placeId' => $point->getPlace()], 'stopover' => true];
			$form->buildField(null, $input, null, $itinerary)->setName('no-margin');
			$form->unregister($input);
		}
		
		$input = new Output('to');
		$input->setValue($request->getTo()->getDescription());
		$options['destination'] = ['placeId' => $request->getTo()->getPlace()];
		$form->buildField('<i class="icon-place-to"></i>', $input, null, $itinerary)->setName('no-margin-last');
		$form->unregister($input);
		
		$directions->setOptions([], $options);
		if ($this->step) {
			$itinerary->append($directions);
		}
		$form->append($itinerary);
		
		$info = new Fieldset('Informações');
		
		if ($this->step) {
			$input = new Label(Request::getStatusAllowed()[$request->getStatus()]);
			switch ($request->getStatus()) {
				case Request::CONFIRMED:
					$input->setStyle(Label::Info);
					break;
					
				case Request::INITIATED:
					$input->setStyle(Label::Warning);
					break;
					
				case Request::FINISHED:
					$input->setStyle(Label::Success);
					break;
					
				case Request::CANCELED:
				case Request::DECLINED:
					$input->setStyle(Label::Inverse);
					break;
					
			}
			
			$form->buildField(null, $input, null, $info);
		}
		
		$panel2 = new Box();
		$panel2->setSpan(3);
		
		$input = new Output('opened-at');
		$input->setSpan(5);
		$input->setValue($request->getOpenedAt()->format('d/m/Y H:i'));
		$form->buildField('Aberta em', [$input, $panel2], null, $info);
		$form->unregister($input);
		
		if ($this->step && $request->getOpenedAt() != $request->getUpdateAt()) {
			$input = new Output('updated-at');
			$input->setValue($request->getUpdateAt()->format('d/m/Y H:i'));
			$form->buildField('Última atualização em', $input, null, $panel2)->setName('no-margin');
			$form->unregister($input);
		}
		
		$input = new Output('requester-user');
		$input->setValue($request->getOpenedBy()->getName());
		$form->buildField('Requisitante', $input, null, $info);
		$form->unregister($input);
		
		$input = new Output('requester-unit');
		$input->setValue($request->getRequesterUnit()->getPartialDescription());
		$form->buildField('Unidade Administrativa', $input, null, $info);
		$form->unregister($input);
		
		if ($request instanceof RequestTrip) {
			
			$input = new Output('passangers');
			$input->setValue(implode('<br />', $request->getPassengers()));
			$form->buildField('Passageiros', $input, null, $info);
			$form->unregister($input);
			
			$input = new Output('service');
			$input->setValue(nl2br($request->getService()));
			$form->buildField('Serviço a Executar', $input, null, $info);
			$form->unregister($input);
			
		} elseif ($request instanceof RequestFreight) {
			$input = new Output('freight');
			$input->setValue(($request->getFreight() == RequestFreight::TO_SEND ? 'Enviar' : 'Receber') . ' itens');
			$form->buildField('Serviço a Executar', $input, null, $info);
			$form->unregister($input);
			
			$input = new Output('package');
			$input->setValue(implode('<br />', $request->getItems()));
			$form->buildField('Encomenda(s)', $input, null, $info);
			$form->unregister($input);
			
			$input = new Output('service');
			$input->setValue($request->getService());
			$form->buildField('Instruções de Envio', $input, null, $info);
			$form->unregister($input);
		}
		
		$form->append($info);
		
		$traffic = new Fieldset('Autorização');
		$append = false;
			
		if ($request->getConfirmedAt()) {
			$input = new Output('authorized');
			$input->setValue($request->getConfirmedBy()->getName() . ' às ' . $request->getConfirmedAt()->format('d/m/Y H:i:s'));
			$form->buildField('Autorizado por', $input, null, $traffic);
			$form->unregister($input);
			
			$append = true;
			$input = new Output('vehicle');
			$value[] = '<span class="plate">' . $request->getVehicle()->getPlate() . '</span>';
			$value[] = '<span class="description">' . $request->getVehicle()->getDescription() . '</span>';
			$input->setValue(implode('', $value));
			$form->buildField('Veículo', $input, null, $traffic);
			$form->unregister($input);
			$value = null;
			
			$input = new Output('driver');
			$value[] = '<span class="name">' . $request->getDriver()->getName() . '</span>';
			$value[] = '<span class="license">CNH ' . implode('', $request->getDriver()->getVehicles()) . '</span>';
			
			$expires = $request->getDriver()->getExpires();
			$now = $request->getConfirmedAt();
			$value[]  = '<span class="expires label ' . ($expires > $now ? 'label-success' : 'label-important' ). '">' . $expires->format('d/m/Y') . '</span>';
			
			$input->setValue(implode('', $value));
			$form->buildField('Motorista', $input, null, $traffic);
			$form->unregister($input);
			$value = null;
			
			
		}
		
		if ( $request->getInitiatedAt() ) {
			$append = true;
			
			$input = new Output('initial');
			$value[] = '<span class="date">' . $request->getInitiatedAt()->format('d/m/Y H:i') . '</span>';
			$value[] = '<span class="odometer">Hodômetro ' . number_format($request->getOdometerInitial(), 0, '', '.') . ' Km</span>';
			
			$input->setValue(implode('', $value));
			$form->buildField('Início', $input, null, $traffic);
			$form->unregister($input);
			$value = null;
		}
		
		if ( $request->getFinishedAt() ) {
			$append = true;
			
			$input = new Output('final');
			$value[] = '<span class="date">' . $request->getFinishedAt()->format('d/m/Y H:i') . '</span>';
			$value[] = '<span class="odometer">Hodômetro ' . number_format($request->getOdometerFinal(), 0, '', '.') . ' Km</span>';
			
			$input->setValue(implode('', $value));
			$form->buildField('Fim', $input, null, $traffic);
			$form->unregister($input);
			$value = null;
		}
		
		if ($append) {
			$form->append($traffic);
		}
		
		if (! $this->step && $request->getStatus() == Request::FINISHED ) {
			$footer = new Fieldset('Termo de Resposabilidade');
			$footer->setName('term');
			$term = 'Durante o período supramencionado, 
					 declaro que ficarei responsável pelo USO e pela GUARDA do veículo, 
					 o qual será conduzido consoante as determinações do Código de Trânsito Brasileiro, 
					 e utilizado no exclusivo interesse do serviço público inerente a este Estado.';
			$footer->append(new Paragraph($term));
			
			$signature = new Paragraph('MOTORISTA');
			$signature->setName('signature');
			$footer->append($signature);
			$panel->append($footer);
		}
		
		
		if ($this->step ) {
			$allowed = $request->getStateAllowed();
			
			if (isset($allowed[$step->getStepType()])) {
				$step->setName('request-step');
				$step->setLegend($allowed[$step->getStepType()][1]);
				$step->create($request);
				$form->setAutoRegister(true);
				$form->append($step);
				
				$form->addButton(new Button([new Icon($allowed[$step->getStepType()][0], true), $allowed[$step->getStepType()][1]], new TgFormSubmit($submit, $form), Button::Primary));
			}
			
			if ($decline && isset($allowed[Request::DECLINED])) {
				$subform = new BuilderForm('decline-form');
				$subform->setAutoRegister(true);
				$subform->append(new RequestFieldSetDecline());
				
				$modal = new Modal('decline-modal', new Title($allowed[Request::DECLINED][1], 3));
				$modal->addButton(new Button([new Icon($allowed[Request::DECLINED][0], true), $allowed[Request::DECLINED][1]], new TgFormSubmit($decline, $subform), Button::Danger));
				$modal->setBody($subform);
				$modal->setWidth(750);
				$form->append($modal);
				
				$form->buildButton('decline', [new Icon($allowed[Request::DECLINED][0], true), $allowed[Request::DECLINED][1]], new TgModalOpen($modal), Button::Danger);
			}
			
			$form->buildButton('cancel', 'Retornar', $cancel);
		}
	}
	
	/**
	 * @param User $user
	 */
	public function initialize(User $user) {
		$this->user = $user;
	}

	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Request $object ) {
		if ($this->step) {
			$this->component->setData($this->step->toArray($object));
		}
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Request $object, EntityManager $em ) {
		if ($this->step) {
			$data = $this->component->getData();
			$this->step->toDo($this->user, $object, $data, $em);
		}
	}


}
?>