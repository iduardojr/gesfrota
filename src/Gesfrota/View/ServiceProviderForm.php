<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Pattern\CNPJ;
use PHPBootstrap\Validate\Pattern\Email;
use PHPBootstrap\Validate\Pattern\Pattern;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\CheckBoxList;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Mask;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;

class ServiceProviderForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 */
	public function __construct( Action $submit, Action $cancel ) {
	    $this->buildPanel('Entidades Externas', 'Gerenciar Prestadores de Serviço');
	    
		$form = $this->buildForm('service-provider-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$input = new TextBox('name');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Razão Social', $input, null, $general);
		
		$input = new TextBox('alias');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome Fantasia', $input, null, $general);
		
		$input = new TextBox('nif');
		$input->setSpan(2);
		$input->setMask('99.999.999/9999-99');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->setPattern(new CNPJ('Por favor, informe um CNPJ válido'));
		$form->buildField('CNPJ', $input, null, $general);
		
		$input = new CheckBoxList('services', true);
		$input->setOptions(ServiceProvider::getServicesAllowed());
		$input->setSpan(2);
		$form->buildField('Serviços contratados', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$accountable = new Fieldset('Contato');
		
		$input = new TextBox('contact');
		$input->setSpan(7);
		$form->buildField('Nome', $input, null, $accountable);
		
		$input = new TextBox('email');
		$input->setSpan(7);
		$input->setPattern(new Email('Por favor, informe um e-mail'));
		$form->buildField('E-mail', $input, null, $accountable);
		
		$input = new TextBox('phone');
		$input->setSpan(2);
		$input->setMask(Mask::PhoneBR);
		$input->setPattern(new Pattern(Pattern::PhoneBR, 'Por favor, informe um telefone'));
		$form->buildField('Telefone', $input, null, $accountable);
		
		$tab = new Tabbable('agency-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Responsável'), null, new TabPane($accountable));
		
		$form->append($tab);

		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( ServiceProvider $object ) {
		$data['name'] = $object->getName();
		$data['alias'] = $object->getAlias();
		$data['nif'] = $object->getNif();
		$data['services'] = $object->getServices();
		$data['contact'] = $object->getContact();
		$data['email'] = $object->getEmail();
		$data['phone'] = $object->getPhone();
		$data['active'] = $object->getActive();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( ServiceProvider $object ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setNif($data['nif']);
		$object->setAlias($data['alias']);
		$object->setServices($data['services']);
		$object->setContact($data['contact']);
		$object->setEmail($data['email']);
		$object->setPhone($data['phone']);
		$object->setActive($data['active']);
	}

}
?>