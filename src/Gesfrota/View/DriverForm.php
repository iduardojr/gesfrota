<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Driver;
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
use PHPBootstrap\Widget\Form\Controls\CheckBoxList;
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
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\ChosenBox;
use Gesfrota\Model\Domain\ResultCenter;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\Decorator\InputContext;
use Gesfrota\Model\Domain\DriverLicense;
use PHPBootstrap\Widget\Form\Controls\Uneditable;

class DriverForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $seek
	 * @param Action $seekUnit
	 * @param Action $searchUnit
	 * @param Action $seekAgency
	 * @param Action $searchAgency
	 * @param Action $cancel
	 * @param array $optResultCenter
	 * @param Agency $showAgency
	 */
	public function __construct(Action $submit, Action $seek, Action $seekUnit, Action $searchUnit, Action $seekAgency, Action $searchAgency, Action $cancel, array $optResultCenter, Agency $showAgency = null ) {
		$this->buildPanel('Minha Frota', 'Gerenciar Motorista');
		$form = $this->buildForm('driver-form');
		
		$general = new Fieldset('Dados Pessoais');
		
		$input = new Uneditable('user');
		$input->setSpan(2);
		$form->buildField('Usuário', [$input, new Hidden('user-id')], null, $general);
		
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
		$input->setRequired(new Required(new InputContext($form->getControl('user-id'), '0'), 'Por favor, preencha esse campo'));
		$form->buildField('E-mail', $input, null, $general);
		
		$input = new TextBox('cell');
		$input->setSpan(2);
		$input->setMask(Mask::CellBR);
		$input->setPattern(new Pattern(Pattern::CellBR, 'Por favor, informe um telefone'));
		$form->buildField('Celular', $input, null, $general);
		
		$input = new ComboBox('gender');
		$input->setSpan(2);
		$input->setOptions(array_merge(['Não informado'], User::getGenderAllowed()));
		$form->buildField('Sexo', $input, null, $general);
		
		$input = new DateBox('birthday', new Date(new DateFormat('dd/mm/yyyy', DateTimeParser::getInstance())));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Data de Nascimento', $input, null, $general);
		
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
		
		$required = new Hidden('result-center-required');
		$required->setValue(count($optResultCenter) > 0 ? 1 : 0);
		
		$input = new ChosenBox('results-center', true);
		$input->setOptions($optResultCenter);
		$input->setSpan(7);
		$input->setPlaceholder('Selecione uma ou mais opções');
		$input->setTextNoResult('Nenhum resultado encontrado para ');
		$input->setRequired(new Required(new InputContext($required, 1), 'Por favor, preencha esse campo'));
		$form->buildField('Centro de Resultado', [$input, $required], null, $lotation)->setName('results-center-group');
		$form->unregister($required);
		
		$form->buildField("<br>", [], null, $lotation);
		
		$cnh = new Fieldset('Dados da CNH');
		
		$input = new TextBox('driver-license-number');
		$input->setSpan(2);
		$input->setMask('?99999999999');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nº CNH', $input, null, $cnh);
		
		$input = new CheckBoxList('driver-license-categories', true);
		$input->setOptions(DriverLicense::getCategoriesAllowed());
		$form->buildField('Categoria', $input, null, $cnh);
		
		$input = new DateBox('driver-license-expires', new Date(new DateFormat('dd/mm/yyyy')));
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Validade', $input, null, $cnh);
		
		$input = new CheckBox('driver-license-active', 'Ativo');
		$form->buildField(null, $input, null, $cnh);
		
		$tab = new Tabbable('driver-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Dados Pessoais'), null, new TabPane($general));
		$tab->addItem(new NavLink('Lotação'), null, new TabPane($lotation));
		$tab->addItem(new NavLink('Carteira de Habilitação'), null, new TabPane($cnh));
		
		$form->append($tab);

		$form->buildButton('submit', 'Incluir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( User $object ) {
		if ( $object->getId() > 0 ) {
			$this->getBuilderForm()->getControl('nif')->setSuggestion(null);
			$this->getBuilderForm()->getControl('nif')->setDisabled(true);
			$this->getBuilderForm()->getControl('email')->setDisabled(true);
		}
		$data['user'] = $object->getUserType() . ($object->getId() > 0  ? ' #' . $object->getCode() : '');
		$data['user-id'] = $object->getId();
		$data['name'] = $object->getName();
		$data['nif'] = $object->getNif();
		$data['email'] = $object->getEmail();
		$data['cell'] = $object->getCell();
		$data['gender'] = $object->getGender();
		$data['birthday'] = $object->getBirthday();
		
		if ($object->getLotation()) {
			$data['agency-id'] = $object->getLotation()->getAgency()->getCode();
			$data['agency-name'] = $object->getLotation()->getAgency()->getName();
			
			$data['administrative-unit-id'] = $object->getLotation()->getCode();
			$data['administrative-unit-name'] = $object->getLotation()->getName();
		}
		$data['results-center'] = array_keys($object->getAllResultCenters());
		
		if ($object->getDriverLicense() ) {
			$data['driver-license-number'] = $object->getDriverLicense()->getNumber();
			$data['driver-license-categories'] = $object->getDriverLicense()->getCategories();
			$data['driver-license-expires'] = $object->getDriverLicense()->getExpires();
			$data['driver-license-active'] = $object->getDriverLicense()->getActive();
		}
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( User $object, EntityManager $em ) {
		$data = $this->component->getData();
		
		$object->setName($data['name']);
		
		if ($object->getId() <= 0) {
			$object->setNif($data['nif']);
			$object->setEmail($data['email']);
		}
		
		$object->setCell($data['cell']);
		$object->setGender($data['gender']);
		$object->setBirthday($data['birthday']);
		
		if ($data['administrative-unit-id']) {
			$unit = $em->find(AdministrativeUnit::getClass(), $data['administrative-unit-id']);
			$object->setLotation($unit);
		}
		$object->removeAllResultCenters();
		if (isset($data['results-center'])) {
			foreach($data['results-center'] as $key) {
				$object->addResultCenter($em->find(ResultCenter::getClass(), $key));
			}
		}
		
		$new = new DriverLicense();
		$new->setNumber($data['driver-license-number']);
		$new->setCategories($data['driver-license-categories']);
		$new->setExpires(new \DateTime($data['driver-license-expires']));
		$new->setActive($data['driver-license-active']);
		$object->setDriverLicense($new);
		
	}

}
?>