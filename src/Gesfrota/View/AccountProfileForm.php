<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Format\DateTimeParser;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Validate\Pattern\Pattern;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\DateBox;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Mask;

class AccountProfileForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $password
	 * @param Action $logout
	 */
	public function __construct(Action $submit, Action $password, Action $logout ) {
		$this->buildPanel('Minha Conta', 'Editar Meu Perfil');
		$form = $this->buildForm('account-form');
		
		$input = new TextBox('nif');
		$input->setSpan(2);
		$input->setDisabled(true);
		$form->buildField('CPF', $input);
		
		$input = new TextBox('name');
		$input->setSpan(6);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome', $input);
		
		$input = new TextBox('email');
		$input->setSpan(6);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('E-mail', $input);
		
		$input = new TextBox('cell');
		$input->setSpan(2);
		$input->setMask(Mask::PhoneBR);
		$input->setPattern(new Pattern(Pattern::PhoneBR, 'Por favor, informe um telefone'));
		$form->buildField('Celular', $input);
		
		$input = new ComboBox('gender');
		$input->setSpan(2);
		$input->setOptions(array_merge(['Não informado'], User::getGenderAllowed()));
		$form->buildField('Sexo', $input);
		
		$input = new DateBox('birthday', new Date(new DateFormat('dd/mm/yyyy', DateTimeParser::getInstance())));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Data de Nascimento', $input);
		
		$input = new Output('lotation');
		$form->buildField('Lotação', $input);
		
		$form->buildButton('submit', 'Salvar Perfil', $submit);
		$form->buildButton('password', 'Alterar Senha', $password);
		$form->buildButton('logout', 'Logout', $logout);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( User $object ) {
		$data['nif'] = $object->getNif();
		$data['name'] = $object->getName();
		$data['email'] = $object->getEmail();
		$data['cell'] = $object->getCell();
		$data['gender'] = $object->getGender();
		$data['birthday'] = $object->getBirthday();
		
		$data['lotation'] = $object->getLotation()->getAgency()->getAcronym() .' / '. $object->getLotation()->getPartialDescription();
		
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( User $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setEmail($data['email']);
		$object->setCell($data['cell']);
		$object->setGender($data['gender']);
		$object->setBirthday($data['birthday']);
	}
	
}
?>