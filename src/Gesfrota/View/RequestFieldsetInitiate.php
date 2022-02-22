<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Request;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Form\Controls\NumberBox;
use PHPBootstrap\Format\NumberFormat;
use PHPBootstrap\Validate\Pattern\Number;
use Gesfrota\Model\Domain\User;

class RequestFieldsetInitiate extends RequestFieldSetStep {
	
	/**
	 * @var integer
	 */
	const STEP_TYPE = Request::INITIATED;
	
	public function __construct() {
		parent::__construct();
		
		$input = new NumberBox('odometer-initial', new Number(new NumberFormat(0, '', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->addFilter('trim');
		$input->addFilter('strip_tags');
		
		$this->append(new ControlGroup(new Label('Hodômetro Inicial (Km)', $input), $input));
		
	}
	
	public function toDo(User $user, Request $obj, array $data, EntityManager $em) {
		$obj->toInitiate($user, $data['odometer-initial']);
	}

	public function toArray(Request $obj) {
		return ['odometer-initial' => $obj->getOdometerInitial()];
	}


}
?>