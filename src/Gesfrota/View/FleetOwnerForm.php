<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Owner;
use Gesfrota\Model\Domain\OwnerPerson;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Validate\Pattern\CNPJ;
use PHPBootstrap\Validate\Pattern\CPF;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Layout\Panel;

class FleetOwnerForm extends BuilderForm {
	
	/**
	 * Construtor
	 * 
	 * @param string|Owner $owner
	 * @param Action $submit
	 */
    public function __construct( $owner, Action $submit ) {
    	
    	parent::__construct('fleet-owner-form');
    	$this->append(new Panel(null, 'alert-message'));
		$owner = is_object($owner) ? get_class($owner) : $owner;
		
		$input = new TextBox('name');
		$input->setSpan(5);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$this->buildField($owner == OwnerPerson::getClass() ? 'Nome' : 'Razão Social', $input);
		
		$input = new TextBox('nif');
		$input->setSpan(2);
		if ($owner == OwnerPerson::getClass()) {
		    $input->setMask('999.999.999-99');
		    $input->setPattern(new CPF('Por favor, informe um CPF válido'));
		    $label = 'CPF';
		} else {
		    $input->setMask('99.999.999/9999-99');
		    $input->setPattern(new CNPJ('Por favor, informe um CNPJ válido'));
		    $label = 'CNPJ';
		}
		
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$this->buildField($label, $input);
		
		$this->buildButton('submit', 'Novo Proprietário', $submit);
		$this->buildButton('cancel', 'Cancelar' , new TgModalClose());
		
	}
	
}
?>