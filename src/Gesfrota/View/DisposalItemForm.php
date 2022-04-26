<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\Model\Domain\Survey;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Form\Controls\Uneditable;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Widget\Misc\Title;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Disposal;
use PHPBootstrap\Widget\Misc\Image;

class DisposalItemForm extends AbstractForm {
	
	/**
	 * @param DisposalItem $obj
	 */
    public function __construct( DisposalItem $obj ) {
    	
    	$disposal = $obj->getDisposal();
    	
    	$panel = $this->buildPanel('Disposição #' . $disposal->getCode(), $disposal->getDescription());
    	$panel->setName('disposal-item-page');
    	
    	$header = new Row(true);
    	$header->setName('page-header');
    	$header->append(new Box(1, new Image('/images/brasao-go.png')));
    	$header->append(new Box(11, new Title('Estado de Goiás<br>'. $obj->getDisposal()->getRequesterUnit()->getName(), 1)));
    	$panel->prepend($header);
    	
    	$form = $this->buildForm('disposal-item-form');
    	
    	$form->append($this->buildSectionGeneral($obj->getAsset()));
    	if ($obj->getAsset() instanceof Vehicle) {
    	   $form->append($this->buildSectionDebit());
    	   $form->append($this->buildSectionSurvey());
    	} else {
    	    $section = new Fieldset('Inspeção do Equipamento');
    	    $section->setName('section-survey');
    	    $input = new Output('report');
    	    $form->buildField('Laudo', $input, null, $section);
    	    $form->append($section);
    	}
    	$form->append(new Box(['offset'=> 4], $this->buildSectionFooter($obj->getDisposal())));
	}
	
	/**
	 * @param FleetItem $asset
	 * @return Fieldset
	 */
	private function buildSectionGeneral(FleetItem $asset) {
		
		$section = new Fieldset('ATIVO #' . $asset->getAssetCode());
		$section->setName('section-general');
		$form = $this->component;
		
		if ( $asset instanceof Vehicle ) {
		    $input = [];
		    $input[0] = new Output('asset-plate');
		    $input[0]->setSpan(2);
		    $input[0]->setValue($asset->getPlate());
		    
		    $input[1] = new Output('asset-description');
		    $input[1]->setSpan(6);
		    $input[1]->setValue($asset->getDescription());
		    $form->buildField('Ativo', $input, null, $section);
		    $form->unregister($input[0]);
		    $form->unregister($input[1]);
		    
		    $input = new Output('asset-vin');
		    $input->setValue($asset->getVin());
		    $form->buildField('Chassi', $input, null, $section);
		    $form->unregister($input);
		    
    		$input = [];
    		$input[0] = new Output('asset-owner-nif');
    		$input[0]->setSpan(2);
    		$input[0]->setValue($asset->getOwner()->getNif());
    		
    		$input[1] = new Output('asset-owner-name');
    		$input[1]->setSpan(6);
    		$input[1]->setValue($asset->getOwner()->getName());
    		$form->buildField('Proprietário', $input, null, $section);
    		
    		$form->unregister($input[0]);
    		$form->unregister($input[1]);
		} else {
		    $input = new Output('asset-description');
		    $input->setValue($asset->getDescription());
		    $form->buildField('Ativo', $input, null, $section);
		    $form->unregister($input);
		}
		
		$input = new Output('courtyard');
		$form->buildField('Pátio', $input, null, $section);
		
		$input = new Output('classification');
		$form->buildField('Classificação', $input, null, $section);
		
		$input = new Output('conservation');
		$form->buildField('Estado de Conservação', $input, null, $section);
		
		$input = new Output('value');
		$form->buildField('Valor do Bem', $input, null, $section);
		
		return $section;
	}
	
	/**
	 * @return Fieldset
	 */
	private function buildSectionDebit() {
		
		$section = new Fieldset('Débitos do Veículo');
		$section->setName('section-debit');
		$form = $this->component;
		
		$box1 = new Box(6);
		$box2 = new Box(6);
		
		$input = new Output('debit-license');
		$form->buildField('Licenciamento', $input, null, $box1);
		
		$input = new Output('debit-penalty');
		$form->buildField('Multas', $input, null, $box1);
		
		$input = new Output('debit-tax');
		$form->buildField('IPVA', $input, null, $box2);
		
		$input = new Output('debit-safe');
		$form->buildField('Seguro DPVA', $input, null, $box2);
		
		$section->append(new Row(true, [$box1, $box2]));
		
		return $section;
	}
	
	/**
	 * @return Fieldset
	 */
	private function buildSectionSurvey() {
		
		$section = new Fieldset('Inspeção do Veículo');
		$section->setName('section-survey');
		$form = $this->component;
		
		$inputs = [];
		foreach(Survey::getStatusAllowed(true) as $key => $label) {
		    $input = new Uneditable('l1');
		    $input->setSpan(2);
		    $input->setValue('['.$key.'] ' . $label);
		    $inputs[] = $input;
		}
		
		$form->buildField('LEGENDA', array_slice($inputs, 0, 3), null, $section);
		$form->buildField(null, array_slice($inputs, 3), null, $section);
		
		foreach ($inputs as $input) {
		    $form->unregister($input);
		}
		
		
		$box1 = $this->buildSurveyVehicleFront();
		$box2 = $this->buildSurveyVehicleBack();
		$box1->setSpan(6);
		$box2->setSpan(6);
		$section->append(new Row(false, [$box1, $box2]));
		
		$box1 = $this->buildSurveyVehicleSideLeft();
		$box2 = $this->buildSurveyVehicleSideRight();
		$box1->setSpan(6);
		$box2->setSpan(6);
		$section->append(new Row(false, [$box1, $box2]));
		
		$box1 = $this->buildSurveyVehicleInside();
		$box2 = $this->buildSurveyVehicleAccessories();
		$box1->setSpan(6);
		$box2->setSpan(6);
		$section->append(new Row(false, [$box1, $box2]));
		
		$box1 = $this->buildSurveyVehicleMechanics();
		$box2 = $this->buildSurveyVehicleElectrical();
		$box1->setSpan(6);
		$box2->setSpan(6);
		$section->append(new Row(false, [$box1, $box2]));
		
		$box1 = $this->buildSurveyVehicleSecurity();
		$section->append($box1);
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'OBSERVAÇÃO');
		$input = new Output('survey-note');
		$form->buildField(null, $input, null, $subsection);
		
		$section->append($subsection);
		return $section;
	}
	
	/**
	 * @param Disposal $disposal
	 * @return Fieldset
	 */
	private function buildSectionFooter(Disposal $disposal) {
		$section = new Fieldset(' ');
		$section->setName('section-footer');
		$form = $this->component;
		
		$input = new Output('requester-by');
		$input->setValue($disposal->getRequestedBy()->getNif() . ' ' . $disposal->getRequestedBy()->getName());
		$form->buildField('Avaliador Responsável', $input, null, $section);
		$form->unregister($input);
		
		if ($disposal->getStatus() > Disposal::DRAFTED) {
			$input = new Output('requester-at');
			$input->setValue($disposal->getRequestedAt()->format('d/m/Y H:m:i'));
			$form->buildField('Encaminhada em', $input, null, $section);
			$form->unregister($input);
		}
		
		if ($disposal->getStatus() == Disposal::CONFIRMED) {
			$input = new Output('confirmed-at');
			$input->setValue($disposal->getConfirmedAt()->format('d/m/Y H:m:i'));
			$form->buildField('Confirmada em', $input, null, $section);
			$form->unregister($input);
		}
		
		if ($disposal->getStatus() == Disposal::DECLINED) {
			$input = new Output('declined-at');
			$input->setValue($disposal->getDeclinedAt()->format('d/m/Y H:m:i'));
			$form->buildField('Recusada em', $input, null, $section);
			$form->unregister($input);
		}
		
		return $section;
		
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleFront() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Parte Dianteira');
		$this->buildSubsectionSubtitle($subsection, ['survey-front-left' => 'Esquerda', 'survey-front-right' => 'Direita']);
		
		$this->buildFieldOutput($subsection, 'Farol', ['survey-lantern-fl' => false, 'survey-lantern-fr' => false]);
		$this->buildFieldOutput($subsection, 'Seta', ['survey-lantern-arrow-fl' => false, 'survey-lantern-arrow-fr' => false]);
		
		$this->buildFieldOutput($subsection, 'Para-choque', ['survey-bumper-f' => true]);
		$this->buildFieldOutput($subsection, 'Capô do Motor', ['survey-cover-f' => true]);
		
		$this->buildFieldOutput($subsection, 'Parabrisa', ['survey-windshield-f' => false]);
		$this->buildFieldOutput($subsection, 'Teto', ['survey-roof' => true]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleBack() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Parte Traseira');
		$this->buildSubsectionSubtitle($subsection, ['survey-back-left' => 'Esquerda', 'survey-back-right' => 'Direita']);
		
		$this->buildFieldOutput($subsection, 'Luz de Freio/Ré', ['survey-lantern-bl' => false, 'survey-lantern-br' => false]);
		$this->buildFieldOutput($subsection, 'Seta', ['survey-lantern-arrow-bl' => false, 'survey-lantern-arrow-br' => false]);
		
		$this->buildFieldOutput($subsection, 'Para-choque', ['survey-bumper-b' => true]);
		$this->buildFieldOutput($subsection, 'Tampa Traseira', ['survey-cover-b' => true]);
		
		$this->buildFieldOutput($subsection, 'Parabrisa', ['survey-windshield-b' => false]);
		$this->buildFieldOutput($subsection, 'Escapamento', ['survey-exhaust' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleSideLeft() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Lateral Esquerda');
		$this->buildSubsectionSubtitle($subsection, ['survey_sideleft_front' => 'Dianteira', 'survey_sideleft_back' => 'Traseira']);
		
		$this->buildFieldOutput($subsection, 'Vidros', ['survey-window-fl' => false, 'survey-window-bl' => false]);
		$this->buildFieldOutput($subsection, 'Porta', ['survey-door-fl' => true, 'survey-door-bl' => true]);
		$this->buildFieldOutput($subsection, 'Colunas', ['survey-column-fl' => true, 'survey-column-bl' => true]);
		$this->buildFieldOutput($subsection, 'Estribos', ['survey-stirrup-fl' => true, 'survey-stirrup-bl' => true]);
		$this->buildFieldOutput($subsection, 'Paralamas', ['survey-fender-fl' => true, 'survey-fender-bl' => true]);
		$this->buildFieldOutput($subsection, 'Pneus', ['survey-tire-fl' => false, 'survey-tire-bl' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleSideRight() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Lateral Direita');
		$this->buildSubsectionSubtitle($subsection, ['survey_sideright_front' => 'Dianteira', 'survey_sideright_back' => 'Traseira']);
		
		$this->buildFieldOutput($subsection, 'Vidros', ['survey-window-fr' => false, 'survey-window-br' => false]);
		$this->buildFieldOutput($subsection, 'Porta', ['survey-door-fr' => true, 'survey-door-br' => true]);
		$this->buildFieldOutput($subsection, 'Colunas', ['survey-column-fr' => true, 'survey-column-br' => true]);
		$this->buildFieldOutput($subsection, 'Estribos', ['survey-stirrup-fr' => true, 'survey-stirrup-br' => true]);
		$this->buildFieldOutput($subsection, 'Paralamas', ['survey-fender-fr' => true, 'survey-fender-br' => true]);
		$this->buildFieldOutput($subsection, 'Pneus', ['survey-tire-fr' => false, 'survey-tire-br' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleInside() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Parte Interna');
		
		$this->buildFieldOutput($subsection, 'Banco do Motorista', ['survey-seat-driver' => false]);
		$this->buildFieldOutput($subsection, 'Banco do Passageiro', ['survey-seat-passenger' => false]);
		$this->buildFieldOutput($subsection, 'Banco Traseiro', ['survey-seat-rear' => false]);
		$this->buildFieldOutput($subsection, 'Painel de Instrumentos', ['survey-dashboard' => false]);
		$this->buildFieldOutput($subsection, 'Volante', ['survey-steering-wheel' => false]);
		$this->buildFieldOutput($subsection, 'Buzina', ['survey-horn' => false]);
		$this->buildFieldOutput($subsection, 'Console Central', ['survey-central-console' => false]);
		$this->buildFieldOutput($subsection, 'Tapeçaria do Teto', ['survey-roof-tapestry' => false]);
		$this->buildFieldOutput($subsection, 'Tampão do Porta-malas', ['survey-trunk-cap' => false]);
		$this->buildFieldOutput($subsection, 'Forro das Portas', ['survey-door-lining' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleAccessories() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Acessórios');
		
		$this->buildFieldOutput($subsection, 'Ar Condicionado', ['survey-air-conditioning' => false]);
		$this->buildFieldOutput($subsection, 'Alarme', ['survey-alarm' => false]);
		$this->buildFieldOutput($subsection, 'Direção Hidraúlica', ['survey-steering-hydraulic' => false]);
		$this->buildFieldOutput($subsection, 'Aparelho de Som', ['survey-device-sound' => false]);
		$this->buildFieldOutput($subsection, 'Vidro Elétrico', ['survey-electric-glass' => false]);
		$this->buildFieldOutput($subsection, 'Trava Elétrica', ['survey-electric-lock' => false]);
		$this->buildFieldOutput($subsection, 'Tapetes', ['survey-carpet' => false]);
		$this->buildFieldOutput($subsection, 'Roda de Ferro', ['survey-wheel-iron' => false]);
		$this->buildFieldOutput($subsection, 'Roda de Liga Leve', ['survey-wheel-alloy' => false]);
		$this->buildFieldOutput($subsection, 'Faróis de Neblina', ['survey-lantern-fog' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleMechanics() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Mecânica');
		
		$this->buildFieldOutput($subsection, 'Carburador', ['survey-carburetor' => false]);
		$this->buildFieldOutput($subsection, 'Bomba Injetora', ['survey-injection-pump' => false]);
		$this->buildFieldOutput($subsection, 'Câmbio', ['survey-exchange' => false]);
		$this->buildFieldOutput($subsection, 'Diferencial', ['survey-differential' => false]);
		$this->buildFieldOutput($subsection, 'Motor', ['survey-engine' => false]);
		$this->buildFieldOutput($subsection, 'Radiador', ['survey-radiator' => false]);
		$this->buildFieldOutput($subsection, 'Turbina', ['survey-turbine' => false]);
		$this->buildFieldOutput($subsection, 'Suspensão', ['survey-suspension' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleElectrical() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Elétrica');
		
		$this->buildFieldOutput($subsection, 'Bomba de Gasolina', ['survey-gasoline-pump' => false]);
		$this->buildFieldOutput($subsection, 'Motor de Arranque', ['survey-engine-starter' => false]);
		$this->buildFieldOutput($subsection, 'Módulo de Ignição', ['survey-ignition-module' => false]);
		$this->buildFieldOutput($subsection, 'Alternador', ['survey-alternator' => false]);
		$this->buildFieldOutput($subsection, 'Distribuidor', ['survey-distributor' => false]);
		$this->buildFieldOutput($subsection, 'Bateria', ['survey-battery' => false]);
		$this->buildFieldOutput($subsection, 'Bico de Injeção', ['survey-injection-nozzle' => false]);
		$this->buildFieldOutput($subsection, 'Injeção Eletrônica', ['survey-injection-electronic' => false]);
		
		return $subsection;
	}
	
	/**
	 * @return Box
	 */
	private function buildSurveyVehicleSecurity() {
		
		$subsection = new Box();
		$this->buildSubsectionTitle($subsection, 'Segurança');
		
		$box1 = new Box(6);
		$this->buildFieldOutput($box1, 'Cintos de Segurança', ['survey-safety-belts' => false]);
		$this->buildFieldOutput($box1, 'Air Bag', ['survey-airbag' => false]);
		$this->buildFieldOutput($box1, 'Retrovisor Interno', ['survey-rearview-i' => false]);
		$this->buildFieldOutput($box1, 'Retrovisor Esquerdo', ['survey-rearview-l' => false]);
		$this->buildFieldOutput($box1, 'Retrovisor Direito', ['survey-rearview-r' => false]);
		
		$box2 = new Box(6);
		$this->buildFieldOutput($box2, 'Triângulo de Segurança', ['survey-safety-triangle' => false]);
		$this->buildFieldOutput($box2, 'Macaco', ['survey-monkey' => false]);
		$this->buildFieldOutput($box2, 'Chave de Roda', ['survey-wheel-wrench' => false]);
		$this->buildFieldOutput($box2, 'Estepe', ['survey-wheel-spare' => false]);
		
		$subsection->append(new Row(false, [$box1, $box2]));
		
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
		foreach ($subtitles as $name => $text) {
			$input = new Uneditable($name);
			$input->setValue($text);
			$input->setSpan(1);
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
	private function buildFieldOutput(Box $subsection, $label, array $fieldset) {
		
		$inputs = [];
		
		foreach ($fieldset as $field => $isBodyPart) {
			$input = new Uneditable($field);
			$input->setSpan(1);
			$inputs[] = $input;
		}
		
		return $this->component->buildField($label, $inputs, null, $subsection);
	}
	
	/**
	 * @param DisposalItem $object
	 */
	public function extract( DisposalItem $object ) {
		
	    $data['courtyard'] = '<i class="icon-map-marker"></i> ' . $object->getCourtyard();
		$data['classification'] = DisposalItem::getClassificationAllowed()[$object->getClassification()];
		
		$data['conservation'] = DisposalItem::getConservationAllowed()[$object->getConservation()];
		$data['value'] = 'R$ ' . number_format($object->getValue(), 2, ',', '.');
		
		$data['debit-license'] = $object->getDebitLicense() > 0 ? 'R$ ' . number_format($object->getDebitLicense(), 2, ',', '.') : '-';
		$data['debit-penalty'] = $object->getDebitPenalty() > 0 ? 'R$ ' . number_format($object->getDebitPenalty(), 2, ',', '.') : '-';
		$data['debit-tax'] = $object->getDebitTax() > 0 ? 'R$ ' . number_format($object->getDebitTax(), 2, ',', '.') : '-';
		$data['debit-safe'] = $object->getDebitSafe() > 0 ? 'R$ ' . number_format($object->getDebitSafe(), 2, ',', '.') : '-';
		
		$data['report'] = nl2br($object->getReport());
		
		$survey = $object->getSurvey();
		
		if ($survey) {
		
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
    		
    		$data['survey-note'] = $survey->getNote() ? nl2br($survey->getNote()) : '-';
    		
		}
		
		$this->component->setData($data);
	}

	/**
	 * @param DisposalItem $object
	 * @param EntityManager $em
	 */
	public function hydrate( DisposalItem $object, EntityManager $em ) {
		
	}
	
}
?>