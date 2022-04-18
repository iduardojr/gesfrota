<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\Model\Domain\Place;
use Gesfrota\Model\Domain\Survey;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\BuilderForm;
use Gesfrota\View\Widget\PlaceInput;
use PHPBootstrap\Format\NumberFormat;
use PHPBootstrap\Validate\Pattern\Number;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\NumberBox;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use PHPBootstrap\Widget\Form\Controls\Uneditable;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use Gesfrota\Model\Domain\Vehicle;

class DisposalSurveyVehicleForm extends AbstractForm {
	
	/**
	 * @param DisposalItem $obj
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $location
	 */
    public function __construct( DisposalItem $obj, Action $submit, Action $cancel, Action $location ) {
    	
        $this->buildPanel('Minha Frota', 'Gerenciar Disposições para Alienação');
    	
    	$form = $this->buildForm('disposal-survey-form');
    	
    	$tab = new Tabbable('disposal-tabs');
    	$tab->setPlacement(Tabbable::Left);
    	
    	$link = new NavLink('Seleção');
    	$link->setDisabled(true);
    	$tab->addItem($link);
    	
    	$general = $this->buildSectionGeneral($obj->getAsset(), $location);
    	$debit = $this->buildSectionDebit();
		$survey = $this->buildSectionSurvey();
		$tab->addItem(new NavLink('Avaliação'), null, new TabPane(new Box(0, [$general, $debit, $survey])));
		
		$link = new NavLink('Confirmação');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$form->append($tab);
		
		$form->buildButton('submit', 'Avaliar Ativo', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @param Vehicle $obj
	 * @param Action $location
	 * @return Fieldset
	 */
	private function buildSectionGeneral(Vehicle $obj, Action $location) {
		
	    $section = new Fieldset('Ativo #' . $obj->getAssetCode());
		$form = $this->component;
		
		$input = [];
		$input[0] = new Uneditable('asset-plate');
		$input[0]->setSpan(2);
		$input[0]->setValue($obj->getPlate());
		
		$input[1] = new Uneditable('asset-description');
		$input[1]->setSpan(6);
		$input[1]->setValue($obj->getDescription());
		$form->buildField('Ativo', $input, null, $section);
		
		$form->unregister($input[0]);
		$form->unregister($input[1]);
		
		$input = [];
		$input[0] = new Uneditable('asset-owner-nif');
		$input[0]->setSpan(2);
		$input[0]->setValue($obj->getOwner()->getNif());
		
		$input[1] = new Uneditable('asset-owner-name');
		$input[1]->setSpan(6);
		$input[1]->setValue($obj->getOwner()->getName());
		$form->buildField('Proprietário', $input, null, $section);
		
		$form->unregister($input[0]);
		$form->unregister($input[1]);
		
		$input = new Uneditable('asset-vin');
		$input->setSpan(2);
		$input->setValue($obj->getVin());
		$form->buildField('Chassi', $input, null, $section);
		
		$form->unregister($input);
		
		$input = new PlaceInput('courtyard', $location, '<i class="icon-map-marker"></i>');
		$input->setSpan(8);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Pátio', $input, null, $section);
		
		$input = new ComboBox('classification');
		$input->setSpan(2);
		$input->setOptions(DisposalItem::getClassificationAllowed());
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Classificação', $input, null, $section);
		
		$input = new ComboBox('conservation');
		$input->setSpan(2);
		$input->setOptions(DisposalItem::getConservationAllowed());
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Estado de Conservação', $input, null, $section);
		
		$input = new NumberBox('value', new Number(new NumberFormat(2, ',', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Valor do Bem', $input, null, $section);
		
		return $section;
	}
	
	/**
	 * @return Fieldset
	 */
	private function buildSectionDebit() {
		
		$section = new Fieldset('Registrar Débitos');
		$form = $this->component;
		
		$input = new NumberBox('debit-license', new Number(new NumberFormat(2, ',', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Licenciamento', $input, null, $section);
		
		$input = new NumberBox('debit-penalty', new Number(new NumberFormat(2, ',', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Multas', $input, null, $section);
		
		$input = new NumberBox('debit-tax', new Number(new NumberFormat(2, ',', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('IPVA', $input, null, $section);
		
		$input = new NumberBox('debit-safe', new Number(new NumberFormat(2, ',', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Seguro DPVA', $input, null, $section);
		
		return $section;
	}
	
	/**
	 * @return Fieldset
	 */
	private function buildSectionSurvey() {
		
		$section = new Fieldset('Inspecionar Veículo');
		$form = $this->component;
		
		$box1 = $this->buildSurveyVehicleFront();
		$box2 = $this->buildSurveyVehicleBack();
		$box1->setSpan(6);
		$box2->setSpan(6);
		$section->append(new Row(true, [$box1, $box2]));
		
		$box1 = $this->buildSurveyVehicleSideLeft();
		$box2 = $this->buildSurveyVehicleSideRight();
		$box1->setSpan(6);
		$box2->setSpan(6);
		$section->append(new Row(true, [$box1, $box2]));
		
		$box1 = $this->buildSurveyVehicleInside();
		$box2 = $this->buildSurveyVehicleAccessories();
		$box1->setSpan(6);
		$box2->setSpan(6);
		$section->append(new Row(true, [$box1, $box2]));
		
		$box1 = $this->buildSurveyVehicleMechanics();
		$box2 = $this->buildSurveyVehicleElectrical();
		$box1->setSpan(6);
		$box2->setSpan(6);
		$section->append(new Row(true, [$box1, $box2]));
		
		$box1 = $this->buildSurveyVehicleSecurity();
		$section->append($box1);
		
		$input = new TextArea('survey-note');
		$input->setSpan(8);
		$input->setRows(4);
		$input->setPlaceholder('Caso necessário, preencha o campo com informações adicionais.');
		$form->buildField('Observação', $input, null, $section);
		
		return $section;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleFront() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Parte Dianteira');
		$this->buildSubsectionSubtitle($subsection, ['survey-front-left' => 'Esquerda', 'survey-front-right' => 'Direita']);
		
		$this->buildFieldComboBox($subsection, 'Farol', ['survey-lantern-fl' => false, 'survey-lantern-fr' => false]);
		$this->buildFieldComboBox($subsection, 'Seta', ['survey-lantern-arrow-fl' => false, 'survey-lantern-arrow-fr' => false]);
		
		$this->buildFieldComboBox($subsection, 'Para-choque', ['survey-bumper-f' => true]);
		$this->buildFieldComboBox($subsection, 'Capô do Motor', ['survey-cover-f' => true]);
		
		$this->buildFieldComboBox($subsection, 'Parabrisa', ['survey-windshield-f' => false]);
		$this->buildFieldComboBox($subsection, 'Teto', ['survey-roof' => true]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleBack() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Parte Traseira');
		$this->buildSubsectionSubtitle($subsection, ['survey-back-left' => 'Esquerda', 'survey-back-right' => 'Direita']);
		
		$this->buildFieldComboBox($subsection, 'Luz de Freio/Ré', ['survey-lantern-bl' => false, 'survey-lantern-br' => false]);
		$this->buildFieldComboBox($subsection, 'Seta', ['survey-lantern-arrow-bl' => false, 'survey-lantern-arrow-br' => false]);
		
		$this->buildFieldComboBox($subsection, 'Para-choque', ['survey-bumper-b' => true]);
		$this->buildFieldComboBox($subsection, 'Tampa Traseira', ['survey-cover-b' => true]);
		
		$this->buildFieldComboBox($subsection, 'Parabrisa', ['survey-windshield-b' => false]);
		$this->buildFieldComboBox($subsection, 'Escapamento', ['survey-exhaust' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleSideLeft() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Lateral Esquerda');
		$this->buildSubsectionSubtitle($subsection, ['survey_sideleft_front' => 'Dianteira', 'survey_sideleft_back' => 'Traseira']);
		
		$this->buildFieldComboBox($subsection, 'Vidros', ['survey-window-fl' => false, 'survey-window-bl' => false]);
		$this->buildFieldComboBox($subsection, 'Porta', ['survey-door-fl' => true, 'survey-door-bl' => true]);
		$this->buildFieldComboBox($subsection, 'Colunas', ['survey-column-fl' => true, 'survey-column-bl' => true]);
		$this->buildFieldComboBox($subsection, 'Estribos', ['survey-stirrup-fl' => true, 'survey-stirrup-bl' => true]);
		$this->buildFieldComboBox($subsection, 'Paralamas', ['survey-fender-fl' => true, 'survey-fender-bl' => true]);
		$this->buildFieldComboBox($subsection, 'Pneus', ['survey-tire-fl' => false, 'survey-tire-bl' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleSideRight() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Lateral Direita');
		$this->buildSubsectionSubtitle($subsection, ['survey_sideright_front' => 'Dianteira', 'survey_sideright_back' => 'Traseira']);
		
		$this->buildFieldComboBox($subsection, 'Vidros', ['survey-window-fr' => false, 'survey-window-br' => false]);
		$this->buildFieldComboBox($subsection, 'Porta', ['survey-door-fr' => true, 'survey-door-br' => true]);
		$this->buildFieldComboBox($subsection, 'Colunas', ['survey-column-fr' => true, 'survey-column-br' => true]);
		$this->buildFieldComboBox($subsection, 'Estribos', ['survey-stirrup-fr' => true, 'survey-stirrup-br' => true]);
		$this->buildFieldComboBox($subsection, 'Paralamas', ['survey-fender-fr' => true, 'survey-fender-br' => true]);
		$this->buildFieldComboBox($subsection, 'Pneus', ['survey-tire-fr' => false, 'survey-tire-br' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleInside() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Parte Interna');
		
		$this->buildFieldComboBox($subsection, 'Banco do Motorista', ['survey-seat-driver' => false]);
		$this->buildFieldComboBox($subsection, 'Banco do Passageiro', ['survey-seat-passenger' => false]);
		$this->buildFieldComboBox($subsection, 'Banco Traseiro', ['survey-seat-rear' => false]);
		$this->buildFieldComboBox($subsection, 'Painel de Instrumentos', ['survey-dashboard' => false]);
		$this->buildFieldComboBox($subsection, 'Volante', ['survey-steering-wheel' => false]);
		$this->buildFieldComboBox($subsection, 'Buzina', ['survey-horn' => false]);
		$this->buildFieldComboBox($subsection, 'Console Central', ['survey-central-console' => false]);
		$this->buildFieldComboBox($subsection, 'Tapeçaria do Teto', ['survey-roof-tapestry' => false]);
		$this->buildFieldComboBox($subsection, 'Tampão do Porta-malas', ['survey-trunk-cap' => false]);
		$this->buildFieldComboBox($subsection, 'Forro das Portas', ['survey-door-lining' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleAccessories() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Acessórios');
		
		$this->buildFieldComboBox($subsection, 'Ar Condicionado', ['survey-air-conditioning' => false]);
		$this->buildFieldComboBox($subsection, 'Alarme', ['survey-alarm' => false]);
		$this->buildFieldComboBox($subsection, 'Direção Hidraúlica', ['survey-steering-hydraulic' => false]);
		$this->buildFieldComboBox($subsection, 'Aparelho de Som', ['survey-device-sound' => false]);
		$this->buildFieldComboBox($subsection, 'Vidro Elétrico', ['survey-electric-glass' => false]);
		$this->buildFieldComboBox($subsection, 'Trava Elétrica', ['survey-electric-lock' => false]);
		$this->buildFieldComboBox($subsection, 'Tapetes', ['survey-carpet' => false]);
		$this->buildFieldComboBox($subsection, 'Roda de Ferro', ['survey-wheel-iron' => false]);
		$this->buildFieldComboBox($subsection, 'Roda de Liga Leve', ['survey-wheel-alloy' => false]);
		$this->buildFieldComboBox($subsection, 'Faróis de Neblina', ['survey-lantern-fog' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleMechanics() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Mecânica');
		
		$this->buildFieldComboBox($subsection, 'Carburador', ['survey-carburetor' => false]);
		$this->buildFieldComboBox($subsection, 'Bomba Injetora', ['survey-injection-pump' => false]);
		$this->buildFieldComboBox($subsection, 'Câmbio', ['survey-exchange' => false]);
		$this->buildFieldComboBox($subsection, 'Diferencial', ['survey-differential' => false]);
		$this->buildFieldComboBox($subsection, 'Motor', ['survey-engine' => false]);
		$this->buildFieldComboBox($subsection, 'Radiador', ['survey-radiator' => false]);
		$this->buildFieldComboBox($subsection, 'Turbina', ['survey-turbine' => false]);
		$this->buildFieldComboBox($subsection, 'Suspensão', ['survey-suspension' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleElectrical() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Elétrica');
		
		$this->buildFieldComboBox($subsection, 'Bomba de Gasolina', ['survey-gasoline-pump' => false]);
		$this->buildFieldComboBox($subsection, 'Motor de Arranque', ['survey-engine-starter' => false]);
		$this->buildFieldComboBox($subsection, 'Módulo de Ignição', ['survey-ignition-module' => false]);
		$this->buildFieldComboBox($subsection, 'Alternador', ['survey-alternator' => false]);
		$this->buildFieldComboBox($subsection, 'Distribuidor', ['survey-distributor' => false]);
		$this->buildFieldComboBox($subsection, 'Bateria', ['survey-battery' => false]);
		$this->buildFieldComboBox($subsection, 'Bico de Injeção', ['survey-injection-nozzle' => false]);
		$this->buildFieldComboBox($subsection, 'Injeção Eletrônica', ['survey-injection-electronic' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleSecurity() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Segurança');
		
		$box1 = new Box(6);
		$this->buildFieldComboBox($box1, 'Cintos de Segurança', ['survey-safety-belts' => false]);
		$this->buildFieldComboBox($box1, 'Air Bag', ['survey-airbag' => false]);
		$this->buildFieldComboBox($box1, 'Retrovisor Interno', ['survey-rearview-i' => false]);
		$this->buildFieldComboBox($box1, 'Retrovisor Esquerdo', ['survey-rearview-l' => false]);
		$this->buildFieldComboBox($box1, 'Retrovisor Direito', ['survey-rearview-r' => false]);
		
		$box2 = new Box(6);
		$this->buildFieldComboBox($box2, 'Triângulo de Segurança', ['survey-safety-triangle' => false]);
		$this->buildFieldComboBox($box2, 'Macaco', ['survey-monkey' => false]);
		$this->buildFieldComboBox($box2, 'Chave de Roda', ['survey-wheel-wrench' => false]);
		$this->buildFieldComboBox($box2, 'Estepe', ['survey-wheel-spare' => false]);
		
		$subsection->append(new Row(true, [$box1, $box2]));
		
		return $subsection;
	}
	
	/**
	 * @param string $text
	 * @return Title
	 */
	private function buildSubsectionTitle(Box $subsection, $text) {
		$title = new Title($text, 5);
		$subsection->append($title);
		return $title;
	}
	
	/**
	 * @param Box $subsection
	 * @param array $subtitles
	 * @return ControlGroup
	 */
	private function buildSubsectionSubtitle(Box $subsection, array $subtitles) {
		$inputs = [];
		$span = 10/count($subtitles);
		
		foreach ($subtitles as $name => $text) {
			$input = new Output($name);
			$input->setValue($text);
			$input->setSpan($span);
			$inputs[] = $input;
		}
		
		$control = $this->component->buildField(null, $inputs, null, $subsection);
		
		foreach ($inputs as $input) {
			$this->component->unregister($input);
		}
		
		return $control;
	}
	
	/**
	 * 
	 * @param Box $subsection
	 * @param string $label
	 * @param array $fieldset
	 * @return ControlGroup
	 */
	private function buildFieldComboBox(Box $subsection, $label, array $fieldset) {
		
		$inputs = [];
		$span = 10/count($fieldset);
		
		foreach ($fieldset as $field => $isBodyPart) {
			$input = new ComboBox($field);
			$input->setOptions(Survey::getStatusAllowed($isBodyPart));
			$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
			$input->setSpan($span);
			$inputs[] = $input;
		}
		
		return $this->component->buildField($label, $inputs, null, $subsection);
	}
	
	/**
	 * @param DisposalItem $object
	 */
	public function extract( DisposalItem $object ) {
		
		$data['courtyard'] = $object->getCourtyard();
		$data['classification'] = $object->getClassification();
		$data['conservation'] = $object->getConservation();
		$data['value'] = $object->getValue();
		
		$data['debit-license'] = $object->getDebitLicense();
		$data['debit-penalty'] = $object->getDebitPenalty();
		$data['debit-tax'] = $object->getDebitTax();
		$data['debit-safe'] = $object->getDebitSafe();
		
		$survey = $object->getSurvey();
		
		$data['survey-lantern-fl'] = $survey->getLanternFrontLeft();
		$data['survey-lantern-fr'] = $survey->getLanternFrontRight();
		$data['survey-lantern-bl'] = $survey->getLanternBackLeft();
		$data['survey-lantern-br'] = $survey->getLanternBackRight();
		
		$data['survey-lantern-arrow-fl'] = $survey->getLanternArrowFrontLeft();
		$data['survey-lantern-arrow-fr'] = $survey->getLanternArrowFrontRight();
		$data['survey-lantern-arrow-bl'] = $survey->getLanternArrowBackLeft();
		$data['survey-lantern-arrow-br'] = $survey->getLanternArrowBackRight();
		
		$data['survey-bumper-f'] = $survey->getBumperFront();
		$data['survey-bumper-b'] = $survey->getBumperBack();
		$data['survey-cover-f']  = $survey->getCoverFront();
		$data['survey-cover-b']  = $survey->getCoverBack();
		
		$data['survey-windshield-f'] = $survey->getWindshieldFront();
		$data['survey-windshield-b'] = $survey->getWindshieldBack();
		
		$data['survey-roof']    = $survey->getRoof();
		$data['survey-exhaust'] = $survey->getExhaust();
		
		$data['survey-window-fl'] = $survey->getWindowFrontLeft();
		$data['survey-window-fr'] = $survey->getWindowFrontRight();
		$data['survey-window-bl'] = $survey->getWindowBackLeft();
		$data['survey-window-br'] = $survey->getWindowBackRight();
		
		$data['survey-door-fl'] = $survey->getDoorFrontLeft();
		$data['survey-door-fr'] = $survey->getDoorFrontRight();
		$data['survey-door-bl'] = $survey->getDoorBackLeft();
		$data['survey-door-br'] = $survey->getDoorBackRight();
		
		$data['survey-column-fl'] = $survey->getColumnFrontLeft();
		$data['survey-column-fr'] = $survey->getColumnFrontRight();
		$data['survey-column-bl'] = $survey->getColumnBackLeft();
		$data['survey-column-br'] = $survey->getColumnBackRight();
		
		$data['survey-stirrup-fl'] = $survey->getStirrupFrontLeft();
		$data['survey-stirrup-fr'] = $survey->getStirrupFrontRight();
		$data['survey-stirrup-bl'] = $survey->getStirrupBackLeft();
		$data['survey-stirrup-br'] = $survey->getStirrupBackRight();
		
		$data['survey-fender-fl'] = $survey->getFenderFrontLeft();
		$data['survey-fender-fr'] = $survey->getFenderFrontRight();
		$data['survey-fender-bl'] = $survey->getFenderBackLeft();
		$data['survey-fender-br'] = $survey->getFenderBackRight();
		
		$data['survey-tire-fl'] = $survey->getTireFrontLeft();
		$data['survey-tire-fr'] = $survey->getTireFrontRight();
		$data['survey-tire-bl'] = $survey->getTireBackLeft();
		$data['survey-tire-br'] = $survey->getTireBackRight();
		
		$data['survey-seat-driver'] = $survey->getSeatDriver();
		$data['survey-seat-passenger'] = $survey->getSeatPassenger();
		$data['survey-seat-rear'] = $survey->getSeatRear();
		$data['survey-dashboard'] = $survey->getDashboard();
		$data['survey-steering-wheel'] = $survey->getSteeringWheel();
		$data['survey-horn'] = $survey->getHorn();
		$data['survey-central-console'] = $survey->getCentralConsole();
		$data['survey-roof-tapestry'] = $survey->getRoofTapestry();
		$data['survey-trunk-cap'] = $survey->getTrunkCap();
		$data['survey-door-lining'] = $survey->getDoorLining();
		
		$data['survey-air-conditioning'] = $survey->getAirConditioning();
		$data['survey-alarm'] = $survey->getAlarm();
		$data['survey-steering-hydraulic'] = $survey->getSteeringHydraulic();
		$data['survey-device-sound'] = $survey->getDeviceSound();
		$data['survey-electric-glass'] = $survey->getElectricGlass();
		$data['survey-electric-lock'] = $survey->getElectricLock();
		$data['survey-carpet'] = $survey->getCarpet();
		$data['survey-wheel-iron'] = $survey->getWheelIron();
		$data['survey-wheel-alloy'] = $survey->getWheelAlloy();
		$data['survey-lantern-fog'] = $survey->getLanternFog();
		
		$data['survey-carburetor'] = $survey->getCarburetor();
		$data['survey-exchange'] = $survey->getExchange();
		$data['survey-differential'] = $survey->getDifferential();
		$data['survey-engine'] = $survey->getEngine();
		$data['survey-radiator'] = $survey->getRadiator();
		$data['survey-turbine'] = $survey->getTurbine();
		$data['survey-suspension'] = $survey->getSuspension();
		$data['survey-injection-pump'] = $survey->getInjectionPump();
		
		$data['survey-injection-nozzle'] = $survey->getInjectionNozzle();
		$data['survey-injection-electronic'] = $survey->getInjectionElectronic();
		$data['survey-gasoline-pump'] = $survey->getGasolineBump();
		$data['survey-engine-starter'] = $survey->getEngineStarter();
		$data['survey-ignition-module'] = $survey->getIgnitionModule();
		$data['survey-alternator'] = $survey->getAlternator();
		$data['survey-distributor'] = $survey->getDistributor();
		$data['survey-battery'] = $survey->getBattery();
		
		$data['survey-safety-belts'] = $survey->getSafetyBelts();
		$data['survey-airbag'] = $survey->getAirbag();
		$data['survey-rearview-i'] = $survey->getRearviewInternal();
		$data['survey-rearview-l'] = $survey->getRearviewLeft();
		$data['survey-rearview-r'] = $survey->getRearviewRight();
		$data['survey-safety-triangle'] = $survey->getSafetyTriangle();
		$data['survey-monkey'] = $survey->getMonkey();
		$data['survey-wheel-wrench'] = $survey->getWheelWrench();
		$data['survey-wheel-spare'] = $survey->getWheelSpare();
		
		$data['survey-note'] = $survey->getNote();
		
		$this->component->setData($data);
	}

	/**
	 * @param DisposalItem $object
	 * @param EntityManager $em
	 */
	public function hydrate( DisposalItem $object, EntityManager $em ) {
		$data = $this->component->getData();
		
		$object->setCourtyard(new Place($data['courtyard']['place'], $data['courtyard']['description']));
		$object->setClassification($data['classification']);
		$object->setConservation($data['conservation']);
		$object->setValue($data['value']);
		
		$object->setDebitLicense($data['debit-license']);
		$object->setDebitPenalty($data['debit-penalty']);
		$object->setDebitTax($data['debit-tax']);
		$object->setDebitSafe($data['debit-safe']);
		
		$survey = $object->getSurvey();
		
		$survey->setLanternFrontLeft($data['survey-lantern-fl']);
		$survey->setLanternFrontRight($data['survey-lantern-fr']);
		$survey->setLanternBackLeft($data['survey-lantern-bl']);
		$survey->setLanternBackRight($data['survey-lantern-br']);
		
		$survey->setLanternArrowFrontLeft($data['survey-lantern-arrow-fl']);
		$survey->setLanternArrowFrontRight($data['survey-lantern-arrow-fr']);
		$survey->setLanternArrowBackLeft($data['survey-lantern-arrow-bl']);
		$survey->setLanternArrowBackRight($data['survey-lantern-arrow-br']);
		
		$survey->setBumperFront($data['survey-bumper-f']);
		$survey->setBumperBack($data['survey-bumper-b']);
		$survey->setCoverFront($data['survey-cover-f']);
		$survey->setCoverBack($data['survey-cover-b']);
		
		$survey->setWindshieldFront($data['survey-windshield-f']);
		$survey->setWindshieldBack($data['survey-windshield-b']);
		
		$survey->setRoof($data['survey-roof']);
		$survey->setExhaust($data['survey-exhaust']);
		
		$survey->setWindowFrontLeft($data['survey-window-fl']);
		$survey->setWindowFrontRight($data['survey-window-fr']);
		$survey->setWindowBackLeft($data['survey-window-bl']);
		$survey->setWindowBackRight($data['survey-window-br']);
		
		$survey->setDoorFrontLeft($data['survey-door-fl']);
		$survey->setDoorFrontRight($data['survey-door-fr']);
		$survey->setDoorBackLeft($data['survey-door-bl']);
		$survey->setDoorBackRight($data['survey-door-br']);
		
		$survey->setColumnFrontLeft($data['survey-column-fl']);
		$survey->setColumnFrontRight($data['survey-column-fr']);
		$survey->setColumnBackLeft($data['survey-column-bl']);
		$survey->setColumnBackRight($data['survey-column-br']);
		
		$survey->setStirrupFrontLeft($data['survey-stirrup-fl']);
		$survey->setStirrupFrontRight($data['survey-stirrup-fr']);
		$survey->setStirrupBackLeft($data['survey-stirrup-bl']);
		$survey->setStirrupBackRight($data['survey-stirrup-br']);
		
		$survey->setFenderFrontLeft($data['survey-fender-fl']);
		$survey->setFenderFrontRight($data['survey-fender-fr']);
		$survey->setFenderBackLeft($data['survey-fender-bl']);
		$survey->setFenderBackRight($data['survey-fender-br']);
		
		$survey->setTireFrontLeft($data['survey-tire-fl']);
		$survey->setTireFrontRight($data['survey-tire-fr']);
		$survey->setTireBackLeft($data['survey-tire-bl']);
		$survey->setTireBackRight($data['survey-tire-br']);
		
		$survey->setSeatDriver($data['survey-seat-driver']);
		$survey->setSeatPassenger($data['survey-seat-passenger']);
		$survey->setSeatRear($data['survey-seat-rear']);
		$survey->setDashboard($data['survey-dashboard']);
		$survey->setSteeringWheel($data['survey-steering-wheel']);
		$survey->setHorn($data['survey-horn']);
		$survey->setCentralConsole($data['survey-central-console']);
		$survey->setRoofTapestry($data['survey-roof-tapestry']);
		$survey->setTrunkCap($data['survey-trunk-cap']);
		$survey->setDoorLining($data['survey-door-lining']);
		
		$survey->setAirConditioning($data['survey-air-conditioning']);
		$survey->setAlarm($data['survey-alarm']);
		$survey->setSteeringHydraulic($data['survey-steering-hydraulic']);
		$survey->setDeviceSound($data['survey-device-sound']);
		$survey->setElectricGlass($data['survey-electric-glass']);
		$survey->setElectricLock($data['survey-electric-lock']);
		$survey->setCarpet($data['survey-carpet']);
		$survey->setWheelIron($data['survey-wheel-iron']);
		$survey->setWheelAlloy($data['survey-wheel-alloy']);
		$survey->setLanternFog($data['survey-lantern-fog']);
		
		$survey->setCarburetor($data['survey-carburetor']);
		$survey->setExchange($data['survey-exchange']);
		$survey->setDifferential($data['survey-differential']);
		$survey->setEngine($data['survey-engine']);
		$survey->setRadiator($data['survey-radiator']);
		$survey->setTurbine($data['survey-turbine']);
		$survey->setSuspension($data['survey-suspension']);
		$survey->setInjectionPump($data['survey-injection-pump']);
		
		$survey->setInjectionNozzle($data['survey-injection-nozzle']);
		$survey->setInjectionElectronic($data['survey-injection-electronic']);
		$survey->setGasolineBump($data['survey-gasoline-pump']);
		$survey->setEngineStarter($data['survey-engine-starter']);
		$survey->setIgnitionModule($data['survey-ignition-module']);
		$survey->setAlternator($data['survey-alternator']);
		$survey->setDistributor($data['survey-distributor']);
		$survey->setBattery($data['survey-battery']);
		
		$survey->setSafetyBelts($data['survey-safety-belts']);
		$survey->setAirbag($data['survey-airbag']);
		$survey->setRearviewInternal($data['survey-rearview-i']);
		$survey->setRearviewLeft($data['survey-rearview-l']);
		$survey->setRearviewRight($data['survey-rearview-r']);
		$survey->setSafetyTriangle($data['survey-safety-triangle']);
		$survey->setMonkey($data['survey-monkey']);
		$survey->setWheelWrench($data['survey-wheel-wrench']);
		$survey->setWheelSpare($data['survey-wheel-spare']);
		
		$survey->setNote($data['survey-note']);
	}
	
}
?>