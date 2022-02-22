<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\VehicleFamily;
use Gesfrota\Model\Domain\VehicleMaker;
use Gesfrota\Model\Domain\VehicleModel;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;

class VehicleModelForm extends AbstractForm {
    
    private $modals;
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 */
    public function __construct( Action $submit, Action $seekFamily, Action $searchFamily, Action $seekMaker, Action $searchMaker, Action $cancel ) {
	    $this->buildPanel('Especificações de Veículos', 'Gerenciar Modelos');
		$form = $this->buildForm('vehicle-model-form');
		
		$general = new Fieldset('Identificação do Veículo');
		
		$input = new TextBox('name');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome', $input, null, $general);
		
		$modal = new Modal('vehicle-family-search', new Title('Família', 3));
		$modal->setWidth(900);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		$this->modals['family'] = $modal;
		
		$input = array();
		$input[0] = new TextBox('vehicle-family-id');
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setSuggestion(new Seek($seekFamily));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('vehicle-family-name', $searchFamily, $modal);
		$input[1]->setEnableQuery(true);
		$input[1]->setSpan(6);
		
		$form->buildField('Família', $input, null, $general);
		
		$modal = new Modal('vehicle-maker-search', new Title('Fabricante', 3));
		$modal->setWidth(900);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		$this->modals['maker'] = $modal;
		
		$input = array();
		$input[0] = new TextBox('vehicle-maker-id');
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setSuggestion(new Seek($seekMaker));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('vehicle-maker-name', $searchMaker, $modal);
		$input[1]->setEnableQuery(true);
		$input[1]->setSpan(6);
		
		$form->buildField('Fabricante', $input, null, $general);
		
		$input = new TextBox('fipe');
		$input->setSpan(3);
		$input->setMask('999999-9');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Código Fipe', $input, null, $general);
			
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
			
		$tab = new Tabbable('vehicle-model-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		
		$form->append($tab);

		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( VehicleModel $object ) {
		$data['name'] = $object->getName();
		$data['fipe'] = $object->getFipe();
		$data['active'] = $object->getActive();
		$family = $object->getFamily();
		if ( $family ) {
		    $data['vehicle-family-id'] = $family->getCode();
		    $data['vehicle-family-name'] = $family->getName();
		}

		$maker = $object->getMaker();
		if ( $maker ) {
		    $data['vehicle-maker-id'] = $maker->getCode();
		    $data['vehicle-maker-name'] = $maker->getName();
		}
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( VehicleModel $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setFipe($data['fipe']);
		if ( $data['vehicle-family-id'] ) {
		    $object->setFamily($em->find(VehicleFamily::getClass(), $data['vehicle-family-id']));
		}
		if ( $data['vehicle-maker-id'] ) {
		    $object->setMaker($em->find(VehicleMaker::getClass(), $data['vehicle-maker-id']));
		}
		$object->setActive($data['active']);
	}
	
	public function getModalFamily() {
	    return $this->modals['family'];
	}
	
	public function getModalMaker(){
	    return $this->modals['maker'];
	}

}
?>