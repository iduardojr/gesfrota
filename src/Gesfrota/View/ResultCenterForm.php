<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\ResultCenter;
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


class ResultCenterForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $seekAgency
	 * @param Action $searchAgency
	 * @param Action $cancel
	 * @param Agency $showAgency
	 */
	public function __construct( Action $submit, Action $seekAgency, Action $searchAgency, Action $cancel, Agency $showAgency = null ) {
		$this->buildPanel('Estrutura Organizacional', 'Gerenciar Centro de Resultados');
		$form = $this->buildForm('result-center-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$modal = new Modal('agency-search', new Title('Órgãos', 3));
		$modal->setWidth(600);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		$this->modals['agency'] = $modal;
		
		$input = [];
		$input[0] = new TextBox('agency-id');
		$input[0]->setSuggestion(new Seek($seekAgency));
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('agency-name', $searchAgency, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(6);
		
		$form->buildField('Órgão', $input, null, $general);
		if ($showAgency) {
			$input[0]->setValue($showAgency->getCode());
			$input[1]->setValue($showAgency->getName());
			$input[1]->setEnableQuery(true);
			$input[0]->setDisabled(true);
			$input[1]->setDisabled(true);
			$form->unregister($input[0]);
			$form->unregister($input[1]);
		}
		
		$input = new TextBox('description');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Descrição', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$tab = new Tabbable('result-center-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		
		$form->append($tab);
		
		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( ResultCenter $object ) {
		if ($object->getAgency() && ! $object->getAgency()->isGovernment()) {
			$data['agency-id'] = $object->getAgency()->getCode();
			$data['agency-name'] = $object->getAgency()->getName();
		}
		$data['description'] = $object->getDescription();
		$data['active'] = $object->getActive();
		$this->component->setData($data);
	}
	
	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( ResultCenter $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setDescription($data['description']);
		$object->setActive($data['active']);
		if ( $data['agency-id'] ) {
			$object->setAgency($em->find(Agency::getClass(), $data['agency-id']));
		}
	}

}
?>