<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Format\DateTimeParser;
use PHPBootstrap\Validate\Pattern\CPF;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Validate\Pattern\Pattern;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\DateBox;
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
use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\Model\Domain\Driver;
use PHPBootstrap\Widget\Form\Controls\CheckBoxList;

class UserForm extends AbstractForm {
	
	/**
	 * @var array
	 */
	private $modals;
	
	/**
	 * @param User $user
	 * @param Action $submit
	 * @param Action $seek
	 * @param Action $seekAgency
	 * @param Action $searchAgency
	 * @param Action $seekUnit
	 * @param Action $searchUnit
	 * @param Action $cancel
	 */
	public function __construct( User $user, Action $submit, Action $seek, Action $seekAgency, Action $searchAgency, Action $seekUnit, Action $searchUnit, Action $cancel ) {
		$this->buildPanel('Segurança', ($user->getId() > 0 ? 'Gerenciar ' : 'Novo '). $user->getUserType());
		$form = $this->buildForm('user-form');
		
		$general = new Fieldset('Dados Gerais');
		
		$input = new TextBox('nif');
		$input->setSuggestion(new Seek($seek));
		$input->setSpan(2);
		$input->setMask('999.999.999-99');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->setPattern(new CPF('Por favor, informe um CPF válido'));
		$form->buildField('CPF', $input, null, $general);
		
		$input = new TextBox('name');
		$input->setSpan(6);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome', $input, null, $general);
		
		$input = new TextBox('email');
		$input->setSpan(6);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('E-mail', $input, null, $general);
		
		$input = new TextBox('cell');
		$input->setSpan(2);
		$input->setMask(Mask::PhoneBR);
		$input->setPattern(new Pattern(Pattern::PhoneBR, 'Por favor, informe um telefone'));
		$form->buildField('Celular', $input, null, $general);
		
		$input = new ComboBox('gender');
		$input->setSpan(2);
		$input->setOptions(array_merge(['Não informado'], User::getGenderAllowed()));
		$form->buildField('Sexo', $input, null, $general);
		
		$input = new DateBox('birthday', new Date(new DateFormat('dd/mm/yyyy', DateTimeParser::getInstance())));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Data de Nascimento', $input, null, $general);
		
		$input = new CheckBox('active', 'Ativo');
		$form->buildField(null, $input, null, $general);
		
		$lotation = new Fieldset('Lotação');
		
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
		
		$form->buildField('Órgão', $input, null, $lotation);
		
		$modal = new Modal('administrative-unit-search', new Title('Unidades Administrativas', 3));
		$modal->setWidth(900);
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modal);
		$this->modals['unit'] = $modal;
		
		$input = [];
		$input[0] = new TextBox('administrative-unit-id');
		$input[0]->setSuggestion(new Seek($seekUnit));
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('administrative-unit-name', $searchUnit, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(6);
		
		$form->buildField('Unidade Administrativa', $input, null, $lotation);
		
		$tab = new Tabbable('user-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Gerais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Lotação'), null, new TabPane($lotation));
		
		if ($user instanceof Driver) {
			$cnh = new Fieldset('Dados da CNH');
			
			$input = new TextBox('license');
			$input->setSpan(2);
			$input->setMask('?99999999999');
			$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
			$form->buildField('Nº CNH', $input, null, $cnh);
			
			$input = new TextBox('renach');
			$input->setSpan(2);
			$input->setMask('aa99999999999');
			$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
			$form->buildField('Renach', $input, null, $cnh);
			
			$input = new CheckBoxList('vehicles', true);
			$input->setOptions(Driver::getLicenseAllowed());
			$form->buildField('Categoria', $input, null, $cnh);
			
			$input = new DateBox('expires', new Date(new DateFormat('dd/mm/yyyy')));
			$input->setSpan(2);
			$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
			$form->buildField('Validade', $input, null, $cnh);
			
			$tab->addItem(new NavLink('Carteira de Habilitação'), null, new TabPane($cnh));
		}
		
		$form->append($tab);

		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( User $object ) {
		$data['nif'] = $object->getNif();
		$data['name'] = $object->getName();
		$data['email'] = $object->getEmail();
		$data['cell'] = $object->getCell();
		$data['gender'] = $object->getGender();
		$data['birthday'] = $object->getBirthday();
		$data['active'] = $object->getActive();
		
		if ($object->getLotation()) {
			$data['agency-id'] = $object->getLotation()->getAgency()->getCode();
			$data['agency-name'] = $object->getLotation()->getAgency()->getName();
			
			$data['administrative-unit-id'] = $object->getLotation()->getCode();
			$data['administrative-unit-name'] = $object->getLotation()->getName();
		}
		
		if ($object instanceof Driver) {
			$data['license'] = $object->getLicense();
			$data['renach'] = $object->getRenach();
			$data['vehicles'] = $object->getVehicles();
			$data['expires'] = $object->getExpires();
		}
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( User $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setNif($data['nif']);
		$object->setName($data['name']);
		$object->setEmail($data['email']);
		$object->setCell($data['cell']);
		$object->setGender($data['gender']);
		$object->setBirthday($data['birthday']);
		
		if ($data['administrative-unit-id']) {
			$unit = $em->find(AdministrativeUnit::getClass(), $data['administrative-unit-id']);
			$object->setLotation($unit);
		}
		
		$object->setActive($data['active']);
		
		if ($object instanceof Driver) {
			$object->setLicense((int) $data['license']);
			$object->setRenach($data['renach']);
			
			$object->setVehicles($data['vehicles']);
			$object->setExpires(new \DateTime($data['expires']));
		}
	}
	
	public function getModalAgency() {
		return $this->modals['agency'];
	}
	
	public function getModalUnit() {
		return $this->modals['unit'];
	}

}
?>