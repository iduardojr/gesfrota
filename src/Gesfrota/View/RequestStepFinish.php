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
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\NumberBox;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use PHPBootstrap\Widget\Misc\Icon;

class RequestStepFinish extends RequestStepForm {
    
    /**
     * @param Request $obj
     * @param Action $finish
     * @param Action $cancel
     */
    public function __construct(Request $obj, Action $finish, Action $cancel) {
        $this->buildPanel('Finalizar ' . $obj->getRequestType());
        $form = $this->buildForm('request-step-finish-form');
		
		$input = new NumberBox('odometer-final', new Number(new NumberFormat(0, '', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Hodômetro Final (Km)', $input);
		
		$input = new TextArea('note');
		$input->setRows(4);
		$input->setSpan(8);
		$input->setPlaceholder('Descreva algum problema ocorrido durante a viagem realizada (Max. 250 caracteres)');
		$form->buildField('Informações Adicionais', $input);
		
		$form->buildButton('finish', [new Icon('icon-stop', true), 'Finalizar ' . $obj->getRequestType()], new TgFormSubmit($finish, $form), Button::Primary);
		$form->buildButton('cancel', 'Retornar', $cancel);
		
    }
	
	public function toDo(User $user, Request $obj, array $data, EntityManager $em) {
	    try {
		    $obj->toFinish($user, $data['odometer-final'], $data['note']);
	    } catch (\InvalidArgumentException $e) {
	        throw new \InvalidArgumentException('O hodômetro final não pode ser menor que o hodômetro do veículo: ' . $obj->getVehicle()->getOdometer() . ' km.');
	    }
	}

	public function toArray(Request $obj) {
		return ['odometer-final' => $obj->getOdometerFinal(),
		        'note'           => $obj->getJustify()
		];
	}


}
?>