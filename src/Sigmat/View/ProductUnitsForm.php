<?php
namespace Sigmat\View;

use PHPBootstrap\Mvc\Session\Session;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Button\Button;
use Sigmat\View\GUI\TableCollection;
use Sigmat\View\GUI\BuilderForm;

class ProductUnitsForm extends BuilderForm {
	
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
		parent::__construct('product-units-form');
		$panel = new Fieldset('Unidades de Medida');
		
		$modal = new Modal('product-unit-search', new Title('Unidades de Medida', 3));
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$this->append($modal);
		
		$input[0] = new TextBox('product-unit-id');
		$input[0]->setSuggestion(new Seek($seek));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('product-unit-description', $search, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(5);
		$this->buildField('Unidade de Medida', $input, null, $panel);
		
		$button = new Button('Adicionar', new TgFormSubmit($add, $this), array(Button::Primary, Button::Mini));
		$button->setName('product-unit-add');
		$this->buildField(null, $button, null, $panel);
		
		$table = new TableCollection('units', $session);
		$table->buildColumnAction('product-unit-remove', new Icon('icon-remove'), new TgAjax($remove, $this, TgAjax::Json));
		$table->buildColumnTextId();
		$table->buildColumnText('description', 'Unidade de Medida', null, null, ColumnText::Left);
		$table->buildColumnText('active', 'Status', null, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		
		$panel->append($table);
	}
	
}
?>