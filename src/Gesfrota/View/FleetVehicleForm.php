<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Fleet;
use Gesfrota\Model\Domain\Owner;
use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\Model\Domain\ServiceCard;
use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\VehicleModel;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Format\NumberFormat;
use PHPBootstrap\Validate\Pattern\Number;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Button\ButtonGroup;
use PHPBootstrap\Widget\Dropdown\Dropdown;
use PHPBootstrap\Widget\Dropdown\DropdownLink;
use PHPBootstrap\Widget\Dropdown\TgDropdown;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\ChosenBox;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\NumberBox;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Embed;
use PHPBootstrap\Widget\Form\Controls\Decorator\InputContext;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalLoad;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Tooltip\Tooltip;
use PHPBootstrap\Validate\Pattern\Pattern;

class FleetVehicleForm extends AbstractForm {
    
    private $modals = [];
    
    /**
     * @var Button
     */
    private $ownerDefault;
	
	/**
	 * @param Action $submit
	 * @param Action $seekVehiclePlate
	 * @param Action $seekVehicleModel
	 * @param Action $searchVehicleModel
	 * @param Action $seekAgency
	 * @param Action $searchAgency
	 * @param Action $seekOwner
	 * @param Action $searchOwner
	 * @param Action $newOwerPerson
	 * @param Action $newOwerCompany
	 * @param Action $cancel
	 * @param boolean $showAgencies
	 * @param ServiceCardForm $subform
	 */
    public function __construct(Action $submit, Action $seekVehiclePlate, Action $seekVehicleModel, Action $searchVehicleModel, Action $seekAgency, Action $searchAgency, Action $seekOwner, Action $searchOwner,  Action $newOwerPerson, Action $newOwerCompany, Action $cancel, array $optResultCenter, $showAgencies = false, ServiceCardForm $subform = null ) {
	    $this->buildPanel('Minha Frota', 'Gerenciar Veículos e Equipamentos');
		$form = $this->buildForm('fleet-vehicle-form');
		
		$general = new Fieldset('Identificação do Veículo');
		
		$input = new TextBox('plate');
		$input->setSuggestion(new Seek($seekVehiclePlate));
		$input->setSpan(1);
		$input->setMask('aaa9*99');
		$input->addFilter('strtoupper');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Placa', $input, null, $general);
		
		$modal = new Modal('vehicle-model-search', new Title('Modelo', 3));
		$modal->setWidth(900);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		$this->modals['vehicle-model'] = $modal;
		
		$input = array();
		$input[0] = new TextBox('vehicle-model-fipe');
		$input[0]->setSuggestion(new Seek($seekVehicleModel));
		$input[0]->setPlaceholder('Codigo Fipe');
		$input[0]->setMask('999999-9');
		$input[0]->setSpan(1);
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		
		$input[1] = new Hidden('vehicle-model-id');
		
		$input[2] = new SearchBox('vehicle-model-name', $searchVehicleModel, $modal);
		$input[2]->setEnableQuery(false);
		$input[2]->setSpan(6);
		
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
	    $input->setMask('?*****************');
	    $input->addFilter('strtoupper');
	    $input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Chassi', $input, null, $general);
		
		$input = new TextBox('renavam');
		$input->setSpan(2);
		$input->setMask('?99999999999');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->setPattern(new Pattern(Pattern::Digits, utf8_decode('Por favor, informe apenas dígitos')));
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
		
		$context = new InputContext($input, Fleet::OWN);
		
		$input = new TextBox('asset-code');
		$input->setSpan(2);
		$input->addFilter('strtoupper');
		$input->setRequired(new Required($context, 'Por favor, preencha esse campo'));
		$form->buildField('Cód. Patrimonial', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$form->buildField('Criado em', new Output('created-at'), null, $general);
		$form->buildField('Atualizado em', new Output('updated-at'), null, $general);
		
		$owner = new Fieldset('Proprietário');
		$owner->setName('owner');
		
		if ($showAgencies) {
			$modal = new Modal('agency-search', new Title('Órgãos', 3));
			$modal->setWidth(600);
			$modal->addButton(new Button('Cancelar', new TgModalClose()));
			$form->append($modal);
			
			$input = [];
			$input[0] = new TextBox('agency-id');
			$input[0]->setSuggestion(new Seek($seekAgency));
			$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
			$input[0]->setSpan(1);
			
			$input[1] = new SearchBox('agency-name', $searchAgency, $modal);
			$input[1]->setEnableQuery(false);
			$input[1]->setSpan(5);
			
			$form->buildField('Órgão', $input, null, $owner);
		}
		
		$modal = new Modal('owner-search', new Title('Proprietário', 3));
		$modal->setWidth(800);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		$this->modals['owner'] = $modal;
		
		$input = array();
		$input[0] = new TextBox('owner-id');
		$input[0]->setSuggestion(new Seek($seekOwner));
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setSpan(1);
		
		$this->ownerDefault = new Button(new Icon('icon-star'));
		$this->ownerDefault->setTooltip(new Tooltip('Proprietário Favorito'));
		if (! $showAgencies) {
			$input[0] = new Embed([$input[0], $this->ownerDefault]);
		}
		
		$input[1] = new SearchBox('owner-name', $searchOwner, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(5);
		
		$modal = new Modal('owner-new', new Title('Proprietário', 3));
		$modal->setWidth(800);
		$form->append($modal);
		
		$drop = new Dropdown();
		$drop->addItem(new DropdownLink('Pessoa Física', new TgModalLoad($newOwerPerson, $modal)));
		$drop->addItem(new DropdownLink('Pessoa Jurídica', new TgModalLoad($newOwerCompany, $modal)));
		
		$input[2] = new ButtonGroup(new Button('Novo', null, Button::Primary), new Button(null, new TgDropdown($drop), Button::Primary));
		
		$form->buildField('Proprietário', $input, null, $owner);
		
		$required = new Hidden('result-center-required');
		$required->setValue(count($optResultCenter) > 0 ? 1 : 0);
		
		$input = new ChosenBox('results-center', true);
		$input->setOptions($optResultCenter);
		$input->setSpan(7);
		$input->setPlaceholder('Selecione uma ou mais opções');
		$input->setTextNoResult('Nenhum resultado encontrado para ');
		$input->setRequired(new Required(new InputContext($required, 1), 'Por favor, preencha esse campo'));
		$form->buildField('Centro de Resultado', [$input, $required], null, $owner)->setName('results-center-group');
		$form->unregister($required);
		
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
	        $data['vehicle-model-id'] = $model->getId();
	        $data['vehicle-model-fipe'] = $model->getFipe();
	        $data['vehicle-model-name'] = $model->getName();
	        $data['vehicle-maker-id'] = $model->getMaker()->getCode();
	        $data['vehicle-maker-name'] = $model->getMaker()->getName();
	        $data['vehicle-family-id'] = $model->getFamily()->getCode();
	        $data['vehicle-family-name'] = $model->getFamily()->getName();
	    }
	    if ($object->getResponsibleUnit()) {
	    	$data['agency-id'] = $object->getResponsibleUnit()->getCode();
	    	$data['agency-name'] = $object->getResponsibleUnit()->getName();
	    	
	    	$owner = $object->getResponsibleUnit()->getOwner();
	    	$this->ownerDefault->setToggle(new TgStorage(['owner-id' => $owner->getCode(), 'owner-name' => $owner->getName()]));
	    }
	    if ($object->getOwner()) {
	    	$data['owner-id'] = $object->getOwner()->getCode();
	    	$data['owner-name'] = $object->getOwner()->getName();
	    }
	    $data['results-center'] = array_keys($object->getAllResultCenters());
	    $data['year-manufacture'] = $object->getYearManufacture();
	    $data['year-model'] = $object->getYearModel();
	    $data['asset-code'] = $object->getAssetCode();
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
		if (isset($data['agency-id'])) {
			$object->setResponsibleUnit($em->find(Agency::getClass(), $data['agency-id']));
		}
		if (isset($data['owner-id'])) {
			$object->setOwner($em->find(Owner::getClass(), $data['owner-id']));
		} else {
			$object->setOwner(null);
		}
		$object->removeAllResultCenters();
		if (isset($data['results-center'])) {
			foreach($data['results-center'] as $key) {
				$object->addResultCenter($em->find(ResultCenter::getClass(), $key));
			}
		}
		if (isset($data['cards'])) {
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
		}
		$object->setYear((int) $data['year-manufacture'], (int) $data['year-model']);
		$object->setAssetCode($data['asset-code']);
		$object->setVin($data['vin']);
		$object->setRenavam((int) $data['renavam']);
		$object->setOdometer((int) $data['odometer']);
		$object->setEngine((int) $data['engine']);
		$object->setFleet((int) $data['fleet']);
		$object->setActive($data['active']);
	}
	
}
?>