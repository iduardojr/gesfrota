<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Pattern\Email;
use PHPBootstrap\Validate\Pattern\Pattern;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Mask;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use Gesfrota\Model\Domain\Agency;


class AdministrativeUnitForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $seek
	 * @param Action $search
	 * @param Action $seekAgency
	 * @param Action $searchAgency
	 * @param Action $cancel
	 * @param Agency $showAgency
	 */
	public function __construct( Action $submit, Action $seek, Action $search, Action $seekAgency, Action $searchAgency, Action $cancel, Agency $showAgency = null ) {
		$this->buildPanel('Estrutura Organizacional', 'Gerenciar Unidades Administrativas');
		$form = $this->buildForm('administrative-unit-form');
		
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
		
		$modal = new Modal('administrative-unit-search', new Title('Unidades Administrativas', 3));
		$modal->setWidth(900);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		
		$input = array();
		$input[0] = new TextBox('administrative-unit-id');
		$input[0]->setSuggestion(new Seek($seek));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('administrative-unit-name', $search, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(6);
		
		$form->buildField('Unidade Superior', $input, null, $general);
		
		$input = new TextBox('name');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome', $input, null, $general);
		
		$input = new TextBox('acronym');
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->addFilter('strtoupper');
		$form->buildField('Sigla', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$input->setValue(true);
		$form->buildField(null, $input, null, $general);
		
		$accountable = new Fieldset('Responsável');
		
		$input = new TextBox('contact');
		$input->setSpan(7);
		$form->buildField('Responsável', $input, null, $accountable);
		
		$input = new TextBox('email');
		$input->setSpan(7);
		$input->setPattern(new Email('Por favor, informe um e-mail'));
		$form->buildField('E-mail', $input, null, $accountable);
		
		$input = new TextBox('phone');
		$input->setSpan(2);
		$input->setMask(Mask::PhoneBR);
		$input->setPattern(new Pattern(Pattern::PhoneBR, 'Por favor, informe um telefone'));
		$form->buildField('Telefone', $input, null, $accountable);
		
		$tab = new Tabbable('administrative-unit-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Responsável'), null, new TabPane($accountable));
		
		$form->append($tab);
		
		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( AdministrativeUnit $object ) {
		$parent = $object->getParent();
		if ($object->getAgency() && ! $object->getAgency()->isGovernment()) {
			$data['agency-id'] = $object->getAgency()->getCode();
			$data['agency-name'] = $object->getAgency()->getName();
		}
		if ( $parent ) {
			$data['administrative-unit-id'] = $parent->getCode();
			$data['administrative-unit-name'] = $parent->getFullDescription();
		}
		$data['name'] = $object->getName();
		$data['acronym'] = $object->getAcronym();
		$data['active'] = $object->getActive();
		$data['contact'] = $object->getContact();
		$data['email'] = $object->getEmail();
		$data['phone'] = $object->getPhone();
		$this->component->setData($data);
	}
	
	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( AdministrativeUnit $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setActive($data['active']);
		$object->setAcronym($data['acronym']);
		$object->setContact($data['contact']);
		$object->setEmail($data['email']);
		$object->setPhone($data['phone']);
		if ( $data['administrative-unit-id'] ) {
			$object->setParent($em->find(AdministrativeUnit::getClass(), $data['administrative-unit-id']));
		} 
	}

}
?>