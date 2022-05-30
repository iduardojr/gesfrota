<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\Model\Domain\ServiceCard;
use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use Gesfrota\Model\Domain\Agency;
use PHPBootstrap\Widget\Form\Controls\ChosenBox;
use Gesfrota\Model\Domain\ResultCenter;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\Decorator\InputContext;

class FleetEquipmentForm extends AbstractForm {
    
	
	/**
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $seekAgency
	 * @param Action $searchAgency
	 * @param array $optResultCenter
	 * @param boolean $showAgencies
	 * @param ServiceCardForm $subform
	 */
	public function __construct(Action $submit, Action $cancel, Action $seekAgency, Action $searchAgency, array $optResultCenter, $showAgencies = false, ServiceCardForm $subform = null ) {
	    $this->buildPanel('Minha Frota', 'Gerenciar Veículos e Equipamentos');
		$form = $this->buildForm('fleet-equipment-form');
		
		$general = new Fieldset('Identificação do Equipamento');
		
		$input = new TextBox('asset-code');
		$input->setSpan(3);
		$input->addFilter('strtoupper');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Cód. Patrimonial', $input, null, $general);
		
		$input = new TextBox('description');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Descrição', $input, null, $general);
		
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
			$input[1]->setSpan(6);
			
			$form->buildField('Órgão', $input, null, $general);
		}
		
		$required = new Hidden('result-center-required');
		$required->setValue(count($optResultCenter) > 0 ? 1 : 0);
		
		$input = new ChosenBox('results-center', true);
		$input->setOptions($optResultCenter);
		$input->setSpan(7);
		$input->setPlaceholder('Selecione uma ou mais opções');
		$input->setTextNoResult('Nenhum resultado encontrado para ');
		$input->setRequired(new Required(new InputContext($required, 1), 'Por favor, preencha esse campo'));
		$form->buildField('Centro de Resultado', [$input, $required], null, $general)->setName('results-center-group');
		
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
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$form->buildField('Criado em', new Output('created-at'), null, $general);
		$form->buildField('Atualizado em', new Output('updated-at'), null, $general);
		
		$tab = new Tabbable('fleet-vehicle-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		if ($subform) {
		    $tab->addItem(new NavLink('Cartões Associados'), null, new TabPane($subform));
		    $form->register($subform->getByName('cards'));
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
	 * @see AbstractForm::extract()
	 */
	public function extract( Equipment $object ) {
	    $data['asset-code'] = $object->getAssetCode();
	    $data['description'] = $object->getDescription();
	    if ($object->getResponsibleUnit()) {
	    	$data['agency-id'] = $object->getResponsibleUnit()->getCode();
	    	$data['agency-name'] = $object->getResponsibleUnit()->getName();
	    }
	    $data['results-center'] = array_keys($object->getAllResultCenters());
		$data['engine'] = $object->getEngine();
		$data['fleet'] = $object->getFleet();
		$data['active'] = $object->getActive();
		$data['cards'] = $object->getAllCards();
		if ($object->getId()) {
			$data['created-at'] = $object->getCreatedAt()->format('d/m/Y H:m:s');
			$data['updated-at'] = $object->getUpdatedAt()->format('d/m/Y H:m:s');
		}
		
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Equipment $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setAssetCode($data['asset-code']);
		$object->setDescription($data['description']);
		$object->setEngine((int) $data['engine']);
		$object->setFleet((int) $data['fleet']);
		$object->setActive($data['active']);
		if (isset($data['agency-id'])) {
			$object->setResponsibleUnit($em->find(Agency::getClass(), $data['agency-id']));
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
	}
	
}
?>