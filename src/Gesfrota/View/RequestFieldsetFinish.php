<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Request;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Form\Controls\NumberBox;
use PHPBootstrap\Validate\Pattern\Number;
use PHPBootstrap\Format\NumberFormat;
use Gesfrota\Model\Domain\User;

class RequestFieldsetFinish extends RequestFieldSetStep {
	
	/**
	 * @var integer
	 */
	const STEP_TYPE = Request::FINISHED;
	
	public function __construct() {
		parent::__construct();
		
		$input = new NumberBox('odometer-final', new Number(new NumberFormat(0, '', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->addFilter('trim');
		$input->addFilter('strip_tags');
		
		$this->append(new ControlGroup(new Label('Hodômetro Final (Km)', $input), $input));
		
	}
	
	public function toDo(User $user, Request $obj, array $data, EntityManager $em) {
	    try {
		    $obj->toFinish($user, $data['odometer-final']);
	    } catch (\InvalidArgumentException $e) {
	        throw new \InvalidArgumentException('O hodômetro final não pode ser menor que o hodômetro do veículo: ' . $obj->getVehicle()->getOdometer() . ' km.');
	    }
	}

	public function toArray(Request $obj) {
		return ['odometer-final' => $obj->getOdometerFinal()];
	}


}
?>