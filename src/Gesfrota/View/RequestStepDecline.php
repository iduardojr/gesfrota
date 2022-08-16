<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Request;
use Gesfrota\Model\Domain\User;
use PHPBootstrap\Validate\Measure\Max;
use PHPBootstrap\Validate\Measure\Ruler\RulerLength;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use PHPBootstrap\Widget\Misc\Icon;

class RequestStepDecline extends RequestStepForm {
    
    /**
     * @param Request $obj
     * @param Action $confirm
     * @param Action $decline
     * @param Action $cancel
     */
    public function __construct(Request $obj, Action $confirm, Action $decline, Action $cancel) {
        $this->buildPanel('Recusar ' . $obj->getRequestType());
        $form = $this->buildForm('request-step-decline-form');
		
		$input = new TextArea('justify');
		$input->setSpan(8);
		$input->setRows(4);
		$input->setLength(new Max(250, 'Max. 250 caracteres', RulerLength::getInstance()));
		$input->setPlaceholder('Descreva o motivo do cancelamento da requisição (Max. 250 caracteres)');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Motivo', $input);
		
		$form->buildButton('confirm', [new Icon('icon-ok', true), 'Confirmar ' . $obj->getRequestType()], $confirm, Button::Primary);
		$form->buildButton('decline', [new Icon('icon-remove-sign', true), 'Recusar ' . $obj->getRequestType()], new TgFormSubmit($decline, $form), Button::Danger);
		$form->buildButton('cancel', 'Retornar', $cancel);
		
	}
	
	
	public function toDo(User $user, Request $obj, array $data, EntityManager $em) {
		$obj->toDecline($user, $data['justify']);
	}
	
	public function toArray(Request $obj) {
		return ['justify' => $obj->getJustify()];
	}

}
?>