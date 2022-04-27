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
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Action\TgAjax;

class TransferFleetForm extends AbstractForm {
	
	/**
	 * 
	 * @var TransferFleetTable
	 */
	protected $tableFrom;
	
	/**
	 * 
	 * @var TransferFleetTable
	 */
	protected $tableTo;
	
	/**
	 * @param Action $submit
	 * @param Action $seekFrom
	 * @param Action $seekTo
	 * @param Action $searchAgency
	 * @param Action $cancel
	 */
	public function __construct(Action $submit, Action $seekFrom, Action $seekTo, Action $searchAgency, Action $cancel) {
		$this->buildPanel('Estrutura Organizacional', 'Transferir Frota');
		$form = $this->buildForm('transfer-fleet-form');
		$form->setStyle(null);
		
		$from = new Fieldset('DE');
		
		$modalFrom = new Modal('transfer-from-modal', new Title('Órgãos', 3));
		$modalFrom->setWidth(600);
		$modalFrom->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modalFrom);
		
		$input = [];
		$input[0] = new TextBox('from-agency-id');
		$input[0]->setSuggestion(new Seek($seekFrom));
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('from-agency-name', $searchAgency, $modalFrom);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(4);
		
		$form->buildField(null, $input, null, $from);
		
		$this->tableFrom = new TransferFleetTable('from-fleet', true);
		$this->tableFrom->buildPagination(new TgAjax($seekFrom, $this->tableFrom));
		$form->register($this->tableFrom);
		$from->append($this->tableFrom);
		
		$to = new Fieldset('PARA');
		
		$modalTo = new Modal('transfer-to-modal', new Title('Órgãos', 3));
		$modalTo->setWidth(600);
		$modalTo->addButton(new Button('Cancelar', new TgModalClose()));
		$form->append($modalTo);
		
		$input = [];
		$input[0] = new TextBox('to-agency-id');
		$input[0]->setSuggestion(new Seek($seekTo));
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('to-agency-name', $searchAgency, $modalTo);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(4);
		
		$form->buildField(null, $input, null, $to);
		
		$this->tableTo = new TransferFleetTable('to-fleet', false);
		$this->tableTo->buildPagination(new TgAjax($seekTo, $this->tableTo));
		$form->register($this->tableTo);
		$to->append($this->tableTo);
		
		$form->append(new Row(false, [new Box(6, $from), new Box(6, $to)]));
		
		$form->buildButton('submit', 'Transferir', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
		
		$this->tableFrom->prepare($form);
		$this->tableTo->prepare($form);
	}
	
	/**
	 * @return TransferFleetTable
	 */
	public function getTableFrom() {
		return $this->tableFrom;
	}

	/**
	 * @return TransferFleetTable
	 */
	public function getTableTo() {
		return $this->tableTo;
	}

	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( AdministrativeUnit $object ) {

	}
	
	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( AdministrativeUnit $object, EntityManager $em ) {
		
	}

}
?>