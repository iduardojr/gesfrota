<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Pattern\CPF;
use PHPBootstrap\Validate\Pattern\Pattern;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\ChosenBox;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Mask;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Row;

class AccountCreateForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param array $options
	 * @param integer $step
	 */
    public function __construct(Action $submit, array $options, $step = 1) {
		$this->buildPanel();
		$form = $this->buildForm('account-create-form');
		$form->setStyle(null);
		
		$button = new Button('', new TgFormSubmit($submit, $form), [Button::Large, Button::Success]);
		$submit->setParameter('step', $step);
		
		if ( $step == 1 ) {
		    
		    $input = new ChosenBox('agency');
		    $input->setPlaceholder('Selecione o Órgão da sua Lotação');
		    $input->setTextNoResult('Nenhum órgão encontrado para:');
		    $input->setOptions($options);
		    $form->buildField(null, $input);
		    
		    $button->setLabel('Continuar');
		    
		} else {
		    
		    $input = new Hidden('agency');
		    $form->buildField(null, $input);
		    
    		$input = new TextBox('name');
    		$input->setPlaceholder('Digite o seu nome');
    		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
    		$form->buildField(null, $input);
    		
    		$input = new TextBox('nif');
    		$input->setMask('999.999.999-99');
    		$input->setPlaceholder('Digite o seu CPF');
    		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
    		$input->setPattern(new CPF('Por favor, informe um CPF válido'));
    		$form->buildField(null, $input);
    		
    		$input = new TextBox('email');
    		$input->setPlaceholder('Digite o seu e-mail');
    		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
    		$form->buildField(null, $input);
    		
    		$input = new TextBox('cell');
    		$input->setPlaceholder('Digite o seu telefone celular');
    		$input->setMask(Mask::CellBR);
    		$input->setPattern(new Pattern(Pattern::CellBR, 'Por favor, informe um telefone'));
    		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
    		$form->buildField(null, $input);
    		
    		$input = new ChosenBox('gender');
    		$input->setPlaceholder('Selecione o seu gênero');
    		$input->setDisabledSearch(true);
    		$input->setOptions([''] + User::getGenderAllowed());
    		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
    		$form->buildField(null, $input);
    		
    		$input = new TextBox('birthday');
    		$input->setPlaceholder('Digite a sua data de nascimento');
    		$input->setMask(Mask::DateBR);
    		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
    		$form->buildField(null, $input);
    		
    		$input = new ChosenBox('lotation');
    		$input->setPlaceholder('Selecione a Unidade Adminstrativa da sua Lotação');
    		$input->setTextNoResult('Nenhum unidade administrativa encontrada para:');
    		$input->setOptions($options);
    		$form->buildField(null, $input);
    		
    		$button->setLabel('Cadastrar');
		}
		
		$form->append(new Row(true, new Box(['offset' => 6, 'span' => 6], $button)));
		
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( User $object ) {
	    
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( User $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setNif($data['nif']);
		$object->setName($data['name']);
		$object->setEmail($data['email']);
		$object->setCell($data['cell']);
		$object->setGender($data['gender']);
		$object->setBirthday(new \DateTime(implode('-', array_reverse(explode('/', $data['birthday'])))));
		$object->setLotation($em->find(AdministrativeUnit::getClass(), $data['lotation']));
	}
	
}
?>