<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Owner;
use Gesfrota\Model\Domain\ServiceCard;
use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\VehicleModel;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Format\NumberFormat;
use PHPBootstrap\Validate\Pattern\Number;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Button\ButtonGroup;
use PHPBootstrap\Widget\Dropdown\Dropdown;
use PHPBootstrap\Widget\Dropdown\DropdownLink;
use PHPBootstrap\Widget\Dropdown\TgDropdown;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\NumberBox;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalLoad;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use Gesfrota\Model\Domain\Asset;
use PHPBootstrap\Widget\Form\Controls\Decorator\Embed;
use PHPBootstrap\Widget\Form\Controls\Decorator\AddOn;

class FleetVehicleForm extends AbstractForm {
    
    private $modals = [];
	
	/**
	 * @param Action $submit
	 * @param Action $seekVehiclePlate
	 * @param Action $seekVehicleModel
	 * @param Action $searchVehicleModel
	 * @param Action $seekOwner
	 * @param Action $searchOwner
	 * @param Action $cancel
	 * @param ServiceCardForm $subform
	 */
    public function __construct(Action $submit, Action $seekVehiclePlate, Action $seekVehicleModel, Action $searchVehicleModel, Action $seekOwner, Action $searchOwner, Action $newOwerPerson, Action $newOwerCompany, Action $cancel, ServiceCardForm $subform = null ) {
	    $this->buildPanel('Minha Frota', 'Gerenciar Veículos');
		$form = $this->buildForm('fleet-vehicle-form');
		
		$general = new Fieldset('Identificação do Veículo');
		
		$input = new TextBox('plate');
		$input->setSuggestion(new Seek($seekVehiclePlate));
		$input->setSpan(2);
		$input->setMask('aaa-9*99');
		$input->addFilter('strtoupper');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Placa', $input, null, $general);
		
		$input = new TextBox('asset-code');
		$input->setSpan(2);
		$input->addFilter('strtoupper');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Cód. Patrimonial', $input, null, $general);
		
		$modal = new Modal('vehicle-model-search', new Title('Modelo', 3));
		$modal->setWidth(900);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		$this->modals['vehicle-model'] = $modal;
		
		$input = array();
		$input[0] = new TextBox('vehicle-model-id');
		$input[0]->setSuggestion(new Seek($seekVehicleModel));
		$input[0]->setSpan(1);
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		
		$input[1] = new SearchBox('vehicle-model-name', $searchVehicleModel, $modal);
		$input[1]->setEnableQuery(true);
		$input[1]->setSpan(6);
		
		$form->buildField('Modelo', $input, null, $general);
		
		$input = array();
		$input[0] = new TextBox('vehicle-maker-id');
		$input[0]->setSpan(1);
		$input[0]->setDisabled(true);
		
		$input[1] = new TextBox('vehicle-maker-name');
		$input[1]->setDisabled(true);
		$input[1]->setSpan(6);
		
		$form->buildField('Marca', $input, null, $general);
		
		$input = array();
		$input[0] = new TextBox('vehicle-family-id');
		$input[0]->setSpan(1);
		$input[0]->setDisabled(true);
		
		$input[1] = new TextBox('vehicle-family-name');
		$input[1]->setDisabled(true);
		$input[1]->setSpan(6);
		
		$form->buildField('Família', $input, null, $general);
		
		$input = array();
		$input[0] = new TextBox('year-manufacture');
		$input[0]->setSpan(1);
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setMask('9999?');
		
		$input[1] = new TextBox('year-model');
		$input[1]->setSpan(1);
		$input[1]->setMask('9999?');
		
		$form->buildField('Ano Fab/Mod', $input, null, $general);
		
		$input = new TextBox('vin');
		$input->setSpan(2);
	    $input->setMask('***************');
	    $input->addFilter('strtoupper');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Chassi', $input, null, $general);
		
		$input = new TextBox('renavam');
		$input->setSpan(2);
		$input->setMask('99999999999?');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Renavam', $input, null, $general);
		
		$input = new NumberBox('odometer', new Number(new NumberFormat(0, '', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		
		$form->buildField('Hodômetro', $input, null, $general);
		
		$input = new ComboBox('engine');
		$input->setSpan(2);
		$input->setOptions(Vehicle::getEnginesAllowed());
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Motor a', $input, null, $general);
		
		$input = new ComboBox('fleet');
		$input->setSpan(2);
		$input->setOptions(Vehicle::getFleetAllowed());
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Tipo da Frota', $input, null, $general);
		
		$input = new ComboBox('asset-status');
		$input->setSpan(2);
		$input->setOptions(Asset::getStatusAllowed());
		$form->buildField('Classificação do Bem', $input, null, $general)->setName('group-asset-status');
		
		$input = new NumberBox('asset-value', new Number(new NumberFormat(2, ',', '.'), 'Por favor, informe um número válido'));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Valor do Bem (R$)', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$form->buildField('Criado em', new Output('created-at'), null, $general);
		$form->buildField('Atualizado em', new Output('updated-at'), null, $general);
		
		$owner = new Fieldset('Proprietário');
		$owner->setName('owner');
		
		$modal = new Modal('owner-search', new Title('Proprietário', 3));
		$modal->setWidth(800);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		$this->modals['owner'] = $modal;
		
		$input = array();
		$input[0] = new TextBox('owner-id');
		$input[0]->setSuggestion(new Seek($seekOwner));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('owner-name', $searchOwner, $modal);
		$input[1]->setEnableQuery(true);
		$input[1]->setSpan(6);
		
		
		$form->buildField('Proprietário', $input, null, $owner);
		
		$modal = new Modal('owner-new', new Title('Proprietário', 3));
		$modal->setWidth(800);
		$form->append($modal);
		
		$drop = new Dropdown();
		$drop->addItem(new DropdownLink('Pessoa Física', new TgModalLoad($newOwerPerson, $modal)));
		$drop->addItem(new DropdownLink('Pessoa Jurídica', new TgModalLoad($newOwerCompany, $modal)));
		
		$input = new ButtonGroup(new Button('Novo Proprietário', null, Button::Primary), new Button(null, new TgDropdown($drop), Button::Primary));
		$form->buildField(null, $input, null, $owner);
		
		$form->buildField("<br>", [], null, $owner);
		
		$tab = new Tabbable('fleet-vehicle-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Proprietário'), null, new TabPane($owner));
		if ($subform) {
		    $tab->addItem(new NavLink('Cartões Associados'), null, new TabPane($subform));
		    $form->register($subform->getTableCollection());
		} else {
		    $nav = new NavLink('Cartões Associados');
		    $nav->setDisabled(true);
		    $tab->addItem($nav);
		}
		
		$form->append($tab);

		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @return Modal
	 */
	public function getModalVehicleModel() {
	    return $this->modals['vehicle-model'];
	}
	
	/**
	 * @return Modal
	 */
	public function getModalOwner() {
		return $this->modals['owner'];
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Vehicle $object ) {
	    $data['plate'] = $object->getPlate();
	    $model = $object->getModel();
	    if ($model) {
	        $data['vehicle-model-id'] = $model->getCode();
	        $data['vehicle-model-name'] = $model->getName();
	        $data['vehicle-maker-id'] = $model->getMaker()->getCode();
	        $data['vehicle-maker-name'] = $model->getMaker()->getName();
	        $data['vehicle-family-id'] = $model->getFamily()->getCode();
	        $data['vehicle-family-name'] = $model->getFamily()->getName();
	    }
	    if ($object->getOwner()) {
	    	$data['owner-id'] = $object->getOwner()->getCode();
	    	$data['owner-name'] = $object->getOwner()->getName();
	    }
	    $data['year-manufacture'] = $object->getYearManufacture();
	    $data['year-model'] = $object->getYearModel();
	    $data['asset-code'] = $object->getAsset()->getCode();
	    $data['asset-value'] = $object->getAsset()->getValue();
	    $data['asset-status'] = $object->getAsset()->getStatus();
	    $data['cards'] = $object->getAllCards();
		$data['vin'] = $object->getVin();
		$data['renavam'] = $object->getRenavam();
		$data['vin'] = $object->getVin();
		$data['engine'] = $object->getEngine();
		$data['odometer'] = $object->getOdometer();
		$data['fleet'] = $object->getFleet();
		$data['active'] = $object->getActive();
		if ($object->getId()) {
			$data['created-at'] = $object->getCreatedAt()->format('d/m/Y H:m:s');
			$data['updated-at'] = $object->getUpdatedAt()->format('d/m/Y H:m:s');
		}
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Vehicle $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setPlate($data['plate']);
		if (isset($data['vehicle-model-id'])) {
		    $object->setModel($em->find(VehicleModel::getClass(), $data['vehicle-model-id']));
		}
		if (isset($data['owner-id'])) {
			$object->setOwner($em->find(Owner::getClass(), $data['owner-id']));
		} else {
			$object->setOwner(null);
		}
		$oldcards = $object->getAllCards();
		foreach( $data['cards'] as $dto ) {
			if ($dto->getId()) {
				unset($oldcards[$dto->getId()]);
				$card = $em->find(ServiceCard::getClass(), $dto->getId());
			} else {
				$card = new ServiceCard();
				$em->persist($card);
			}
			$card->setNumber($dto->getNumber());
			$provider = $em->find(ServiceProvider::getClass(), $dto->getServiceProvider()->getId());
			$card->setServiceProvider($provider);
			$object->addCard($card);
		}
		foreach ( $oldcards as $card ) {
			$em->remove($card);
		}
		$object->setYear((int) $data['year-manufacture'], (int) $data['year-model']);
		$object->setAsset(new Asset($data['asset-code'], $data['asset-value'], $data['asset-status']));
		$object->setVin($data['vin']);
		$object->setRenavam((int) $data['renavam']);
		$object->setOdometer((int) $data['odometer']);
		$object->setEngine((int) $data['engine']);
		$object->setFleet((int) $data['fleet']);
		$object->setActive($data['active']);
	}
	
}
?>