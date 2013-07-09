<?php
namespace Sigmat\View\Stockroom;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\AbstractForm;
use Sigmat\Model\Stockroom\Stockroom;

/**
 * Formulario
 */
class StockroomForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 */
	public function __construct( Action $submit, Action $cancel ) {
		$this->buildPanel('Administração', 'Gerenciar Almoxarifados');
		$this->buildForm('stockroom-form');
		
		$input = new TextBox('name');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$this->buildField('Nome', $input);
		
		$input = new CheckBox('status', 'Ativo');
		$input->setValue(true);
		$this->buildField(null, $input);

		$this->buildButton('submit', 'Incluir', $submit);
		$this->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Stockroom $object ) {
		$data['name'] = $object->getName();
		$data['status'] = $object->getStatus();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Stockroom $object ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setStatus($data['status']);
	}

}
?>