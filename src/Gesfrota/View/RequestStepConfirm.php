<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Request;
use Gesfrota\Model\Domain\RequestTrip;
use Gesfrota\Model\Domain\User;
use Gesfrota\Model\Domain\Vehicle;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;

class RequestStepConfirm extends RequestStepForm {
	
	/**
	 * @param Request $obj
	 * @param Action $confirm
	 * @param Action $decline
	 * @param Action $cancel
	 * @param Action $seekVehicle
	 * @param Action $searchVehicle
	 * @param Action $seekDriver
	 * @param Action $searchDriver
	 */
	public function __construct(Request $obj, Action $confirm, Action $decline, Action $cancel, Action $seekVehicle, Action $searchVehicle, Action $seekDriver, Action $searchDriver) {
	    $this->buildPanel('Confirmar ' . $obj->getRequestType());
		
		$form = $this->buildForm('request-step-confirm-form');
		$modal = new Modal('vehicle-search', new Title('Veículos', 3));
		$modal->setWidth(700);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		
		$input = [];
		$input[0] = new TextBox('vehicle-plate');
		$input[0]->setSuggestion(new Seek($seekVehicle));
		$input[0]->setSpan(2);
		$input[0]->setMask('aaa9*99');
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		
		$input[1] = new SearchBox('vehicle-description', $searchVehicle, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(6);
		
		$input[2] = new Hidden('vehicle-id');
		
		$form->buildField('Veículo', $input);
		
		
		$modal = new Modal('driver-search', new Title('Motoristas', 3));
		$modal->setWidth(700);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		
		$input = [];
		$input[0] = new TextBox('driver-id');
		$input[0]->setSuggestion(new Seek($seekDriver));
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('driver-name', $searchDriver, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(7);
		
		$form->buildField('Motorista', $input);
		
		if ($obj instanceof RequestTrip && $obj->getRoundTrip()) {
			$input = new CheckBox('round-trip', 'Confirmar viagem de volta?');
			$form->buildField(null, $input);
		}
		
		
	    $form->buildButton('confirm', [new Icon('icon-ok', true), 'Confirmar ' . $obj->getRequestType()], new TgFormSubmit($confirm, $form), Button::Primary);
	    $form->buildButton('decline', [new Icon('icon-remove-sign', true), 'Recusar ' . $obj->getRequestType()], $decline, Button::Danger);
		$form->buildButton('cancel', 'Retornar', $cancel);
	}
	
	public function toDo(User $user, Request $obj, array $data, EntityManager $em) {
		$vehicle = $em->find(Vehicle::getClass(), $data['vehicle-id']);
		$driver = $em->find(User::getClass(), $data['driver-id']);
		$obj->toConfirm($user, $vehicle, $driver, isset($data['round-trip']) ? $data['round-trip'] : false);
	}

	public function toArray(Request $obj) {
		$data = [];
		if ($obj->getVehicle()) {
			$data['vehicle-id'] = $obj->getVehicle()->getId();
			$data['vehicle-plate'] = $obj->getVehicle()->getPlate();
			$data['vehicle-description'] = $obj->getVehicle()->getDescription();
		}
		if ($obj->getDriverLicense()) {
			$data['driver-id'] = $obj->getDriverLicense()->getUser()->getId();
			$data['driver-name'] = $obj->getDriverLicense()->getUser()->getName();
		}
		if ($obj instanceof RequestTrip && $obj->getRoundTrip()) {
			$data['round-trip'] = true;
		}
		return $data;
	}
}
?>