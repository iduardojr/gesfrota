<?php
namespace Sigmat\View\Stockroom;

use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Table\ColumnAction;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Table\Table;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use Sigmat\View\ArrayDatasource;
use Sigmat\Controller\StockroomController;

class RequestersUnitsForm extends Form {
	
	/**
	 * @var ArrayDatasource
	 */
	protected $datasource;
	
	/**
	 * Construtor
	 * 
	 * @param Action $add
	 * @param Action $remove
	 */
	public function __construct( Action $add, Action $remove ) {
		parent::__construct('requesters-units');
		$panel = new Fieldset('Unidades Requisitantes');
		$control = new ControlGroup(new Label('Unidade Administrativa'));
		
		$input = new TextBox('unit-id');
		$input->setSuggestion(new Seek(new Action(StockroomController::getClass(), 'seek-unit')));
		$input->setSpan(1);
		$control->append($input);
		$control->getLabel()->setTarget($input);
		$this->register($input);
		
		$modal = new Modal('unit-search', new Title('Unidades Administrativas', 3));
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$this->append($modal);
		
		$input = new SearchBox('unit-name', new Action(StockroomController::getClass(), 'search-unit'), $modal);
		$input->setEnableQuery(false);
		$input->setSpan(4);
		$control->append($input);
		$this->register($input);
		
		$panel->append($control);
		$btn = new Button('Adicionar', new TgFormSubmit($add, $this), array(Button::Primary, Button::Mini));
		$btn->setName('unit-add');
		$panel->append(new ControlGroup(null, $btn));
		
		$this->datasource = new ArrayDatasource();
		$table = new Table('unit-table', $this->datasource);
		$table->setStyle(Table::Condensed);
		$table->addColumn(new ColumnText('id', '#'));
		$table->addColumn(new ColumnText('name', 'Unidade Administrativa'));
		$table->addColumn(new ColumnAction('unit-remove', new Icon('icon-remove'), new TgAjax($remove, $table, TgAjax::Json)));
		$table->setAlertNoRecords('Nenhum registro encontrado');
		$table->setFooter(new Panel(null));
		$panel->append($table);
		
		$this->append($panel);
	}
	
	/**
	 * Atribui dados
	 * 
	 * @param array $data
	 */
	public function setDatasource( array $data ) {
		$this->datasource->setData($data);
	}
	
	/**
	 * @see Inputable::setValue()
	 */
	public function setValue( array $value ) {
		$this->datasource->setData($value);
	}

	/**
	 * @see Inputable::getValue()
	 */
	public function getValue() {
		return $this->datasource->getData();
	}

}
?>