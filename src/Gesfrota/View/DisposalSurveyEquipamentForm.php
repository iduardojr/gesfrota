<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\Model\Domain\Place;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\PlaceInput;
use PHPBootstrap\Format\NumberFormat;
use PHPBootstrap\Validate\Pattern\Number;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\NumberBox;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use PHPBootstrap\Widget\Form\Controls\Uneditable;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;

class DisposalSurveyEquipamentForm extends AbstractForm {
	
	/**
	 * @param DisposalItem $obj
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $place
	 */
    public function __construct( DisposalItem $obj, Action $submit, Action $cancel, Action $place ) {
    	$this->buildPanel('Minha Frota', 'Gerenciar Disposições para Alienação');
    	
    	$form = $this->buildForm('disposal-survey-form');
    	$page = new Box();
    	
    	$general = new Fieldset('Ativo #' . $obj->getAsset()->getAssetCode());
    	
    	$input = new Uneditable('asset-description');
    	$input->setSpan(8);
    	$input->setValue($obj->getAsset()->getDescription());
    	$form->buildField('Ativo', $input, null, $general);
    	
    	$form->unregister($input);
    	
    	$input = new PlaceInput('courtyard', $place, '<i class="icon-map-marker"></i>');
    	$input->setSpan(8);
    	$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
    	$form->buildField('Pátio', $input, null, $general);
    	
		$input = new ComboBox('classification');
		$input->setSpan(2);
		$input->setOptions(DisposalItem::getClassificationAllowed());
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Classificação', $input, null, $general);
		
		$input = new ComboBox('conservation');
		$input->setSpan(2);
		$input->setOptions(DisposalItem::getConservationAllowed());
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Estado de Conservação', $input, null, $general);
		
		$input = new NumberBox('value', new Number(new NumberFormat(2, ',', '.')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Valor do Bem', $input, null, $general);
		
		$page->append($general);
		
		$survey = new Fieldset('Inspecionar Equipamento');
		
		$input = new TextArea('report');
		$input->setSpan(8);
		$input->setRows(4);
		$input->setPlaceholder('Descreva informações detalhadas referente ao equipamento.');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Laudo', $input, null, $survey);
		
		$page->append($survey);
		
		$tab = new Tabbable('disposal-tabs');
		$tab->setPlacement(Tabbable::Left);
		
		$link = new NavLink('Seleção');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$tab->addItem(new NavLink('Avaliação'), null, new TabPane($page));
		
		$link = new NavLink('Confirmação');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$form->append($tab);
		
		$form->buildButton('submit', 'Avaliar Ativo', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @param DisposalItem $object
	 */
	public function extract( DisposalItem $object ) {
		$data['courtyard'] = $object->getCourtyard();
		$data['classification'] = $object->getClassification();
		$data['conservation'] = $object->getConservation();
		$data['value'] = $object->getValue();
		
		$data['report'] = $object->getReport();
		
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
		
		$object->setReport($data['report']);
		
	}
	

}
?>