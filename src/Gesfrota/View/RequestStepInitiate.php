<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Request;
use Gesfrota\Model\Domain\User;
use PHPBootstrap\Format\NumberFormat;
use PHPBootstrap\Validate\Pattern\Number;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\NumberBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Form\TgFormSubmit;

class RequestStepInitiate extends RequestStepForm {
    
    /**
     * @param Request $obj
     * @param Action $confirm
     * @param Action $decline
     * @param Action $cancel
     */
    public function __construct(Request $obj, Action $initiate, Action $cancel) {
        $this->buildPanel('Iniciar ' . $obj->getRequestType());
        $form = $this->buildForm('request-step-initiate-form');
        
		$input = new NumberBox('odometer-initial', new Number(new NumberFormat(0, '', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Hodômetro Inicial (Km)', $input);
		
		$form->buildButton('initiate', [new Icon('icon-play', true), 'Iniciar ' . $obj->getRequestType()], new TgFormSubmit($initiate, $form), Button::Primary);
		$form->buildButton('cancel', 'Retornar', $cancel);
		
    }
	
	public function toDo(User $user, Request $obj, array $data, EntityManager $em) {
	    try {
		    $obj->toInitiate($user, $data['odometer-initial']);
	    } catch (\InvalidArgumentException $e) {
	        throw new \InvalidArgumentException('O hodômetro inicial não pode ser menor que o hodômetro do veículo: ' . $obj->getVehicle()->getOdometer() . ' km.');
	    }
	}

	public function toArray(Request $obj) {
		return ['odometer-initial' => $obj->getOdometerInitial()];
	}


}
?>