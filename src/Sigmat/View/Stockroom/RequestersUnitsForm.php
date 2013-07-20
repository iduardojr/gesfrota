<?php
namespace Sigmat\View\Stockroom;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Mvc\Session\Session;
use Sigmat\View\TableCollection;
use Sigmat\View\BuilderForm;

class RequestersUnitsForm extends BuilderForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $add
	 * @param Action $remove
	 * @param Action $seek
	 * @param Action $search
	 * @param Session $session
	 */
	public function __construct( Action $add, Action $remove, Action $seek, Action $search, Session $session ) {
		parent::__construct('requesters-units-form');
		$panel = new Fieldset('Unidades Requisitantes');
		
		$modal = new Modal('unit-search', new Title('Unidades Administrativas', 3));
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$this->append($modal);
		
		$input1 = new TextBox('unit-id');
		$input1->setSuggestion(new Seek($seek));
		$input1->setSpan(1);
		
		$input2 = new SearchBox('unit-name', $search, $modal);
		$input2->setEnableQuery(false);
		$input2->setSpan(4);
		$this->buildField('Unidade Administrativa', array($input1, $input2), null, $panel);
		
		$button = new Button('Adicionar', new TgFormSubmit($add, $this), array(Button::Primary, Button::Mini));
		$button->setName('unit-add');
		$this->buildField(null, $button, null, $panel);
		
		$table = new TableCollection('units', $session);
		$table->buildColumnTextId();
		$table->buildColumnText('description', 'Unidade Administrativa', null, null, ColumnText::Left);
		$table->buildColumnAction('unit-remove', new Icon('icon-remove'), new TgAjax($remove, $this, TgAjax::Json));
		
		$panel->append($table);
	}
	
}
?>