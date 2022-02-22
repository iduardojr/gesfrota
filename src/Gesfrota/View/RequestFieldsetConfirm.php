<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\Request;
use Gesfrota\Model\Domain\Vehicle;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use Gesfrota\Model\Domain\RequestTrip;
use Gesfrota\Model\Domain\User;

class RequestFieldsetConfirm extends RequestFieldSetStep {
	
	/**
	 * @var array
	 */
	protected $modals;
	
	/**
	 * @var integer
	 */
	const STEP_TYPE = Request::CONFIRMED;
	
	/**
	 * @param Action $seekVehicle
	 * @param Action $seekDriver
	 * @param Action $searchDriver
	 */
	public function __construct(Action $seekVehicle, $searchVehicle, Action $seekDriver, Action $searchDriver) {
		parent::__construct();
		
		$modal = new Modal('vehicle-search', new Title('Veículos', 3));
		$modal->setWidth(700);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$this->append($modal);
		$this->modals['vehicle'] = $modal;
		
		$input = [];
		$input[0] = new TextBox('vehicle-plate');
		$input[0]->setSuggestion(new Seek($seekVehicle));
		$input[0]->setSpan(2);
		$input[0]->setMask('aaa-9*99');
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->addFilter('trim');
		$input[0]->addFilter('strip_tags');
		
		$input[1] = new SearchBox('vehicle-description', $searchVehicle, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(6);
		
		$input[2] = new Hidden('vehicle-id');
		
		$this->append(new ControlGroup(new Label('Veículo', $input[0]), $input));
		
		$modal = new Modal('driver-search', new Title('Motoristas', 3));
		$modal->setWidth(700);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$this->append($modal);
		$this->modals['driver'] = $modal;
		
		$input = [];
		$input[0] = new TextBox('driver-id');
		$input[0]->setSuggestion(new Seek($seekDriver));
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setSpan(1);
		$input[0]->addFilter('trim');
		$input[0]->addFilter('strip_tags');
		
		$input[1] = new SearchBox('driver-name', $searchDriver, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(7);
		
		$this->append(new ControlGroup(new Label('Motorista', $input[0]), $input));
	}
	
	public function create (Request $obj) {
		if ($obj instanceof RequestTrip && $obj->getRoundTrip()) {
			$input = new CheckBox('round-trip', 'Confirmar viagem de volta?');
			$this->append(new ControlGroup(null, $input));
		}
		
	}
	
	public function toDo(User $user, Request $obj, array $data, EntityManager $em) {
		$vehicle = $em->find(Vehicle::getClass(), $data['vehicle-id']);
		$driver = $em->find(Driver::getClass(), $data['driver-id']);
		$obj->toConfirm($user, $vehicle, $driver, isset($data['round-trip']) ? $data['round-trip'] : false);
	}

	public function toArray(Request $obj) {
		$data = [];
		if ($obj->getVehicle()) {
			$data['vehicle-id'] = $obj->getVehicle()->getId();
			$data['vehicle-plate'] = $obj->getVehicle()->getPlate();
			$data['vehicle-description'] = $obj->getVehicle()->getDescription();
		}
		if ($obj->getDriver()) {
			$data['driver-id'] = $obj->getDriver()->getId();
			$data['driver-name'] = $obj->getDriver()->getName();
		}
		if ($obj instanceof RequestTrip && $obj->getRoundTrip()) {
			$data['round-trip'] = true;
		}
		return $data;
	}
	
	public function getModalDriver() {
		return $this->modals['driver'];
	}
	
	public function getModalVehicle() {
		return $this->modals['vehicle'];
	}
	
}
?>