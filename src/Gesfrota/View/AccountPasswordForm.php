<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\PasswordBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\InputContext;

class AccountPasswordForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $cancel
	 */
	public function __construct(Action $submit, Action $cancel ) {
		$this->buildPanel('Minha Conta', 'Alterar minha senha');
		$form = $this->buildForm('account-form');
		
		$input = new PasswordBox('password');
		$input->setSpan(3);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nova senha', $input);
		
		$input1 = new PasswordBox('repeat-password');
		$input1->setSpan(3);
		$input1->setRequired(new Required(new InputContext($input), 'Por favor, preencha esse campo'));
		$form->buildField('Repita senha', $input1);
		
		$form->buildButton('submit', 'Alterar senha', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( User $object ) {
		$this->component->setData([]);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( User $object ) {
		$data = $this->component->getData();
		
		if ($data['password'] == $data['repeat-password']) {
			$object->setPassword($data['password']);
		}
	}
	
}
?>