<?php
namespace Sigmat\View\Product;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Mvc\Session\Session;
use Sigmat\View\TableCollection;
use Sigmat\View\BuilderForm;
use PHPBootstrap\Widget\Misc\Label;


class AttributesForm extends BuilderForm {
	
	/**
	 * Construtor
	 * 
	 * @param Session $session
	 * @param Action $add
	 * @param Action $remove
	 * @param Action $seek
	 * @param Action $search
	 */
	public function __construct( Session $session, Action $add, Action $remove, Action $seek, Action $search ) {
		parent::__construct('product-attributes-form');
		$panel = new Fieldset('Atributos');

		$modal = new Modal('attribute-search', new Title('Atributos', 3));
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$this->append($modal);
		
		$input[0] = new TextBox('attribute-id');
		$input[0]->setSuggestion(new Seek($seek));
		$input[0]->setSpan(1);
		
		$input[1] = new SearchBox('attribute-description', $search, $modal);
		$input[1]->setEnableQuery(false);
		$input[1]->setSpan(5);
		$this->buildField('Atributo', $input, null, $panel);
		
		$button = new Button('Adicionar', new TgFormSubmit($add, $this), array(Button::Primary, Button::Mini));
		$button->setName('attribute-add');
		$this->buildField(null, $button, null, $panel);
		
		$table = new TableCollection('attributes', $session);
		$table->setAlertNoRecords(null);
		$table->buildColumnAction('attribute-remove', new Icon('icon-remove'), new TgAjax($remove, $this, TgAjax::Json));
		$table->buildColumnTextId();
		$table->buildColumnText('description', 'Descrição', null, null, ColumnText::Left);
		$table->buildColumnText('status', 'Status', null, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		
		$panel->append($table);
		
		$this->append($panel);
	}
	
}
?>