<?php
namespace Sigmat\View\Product;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Misc\Anchor;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Mvc\Session\Session;
use Sigmat\View\TableCollection;
use Sigmat\View\BuilderForm;

class AttributeOptionsForm extends BuilderForm {
	
	/**
	 * Construtor
	 * 
	 * @param Session $session
	 * @param Action $submit
	 * @param Action $edit
	 * @param Action $remove
	 * @param Action $cancel
	 */
	public function __construct( Session $session, Action $submit, Action $edit, Action $remove, Action $cancel = null ) {
		parent::__construct('product-attribute-options-form');
		$panel = new Fieldset('Opções do Atributo');

		$input[0] = new TextBox('option');
		$input[0]->setSpan(4);
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		
		$input[1] = new Button('Adicionar', new TgFormSubmit($submit, $this), array(Button::Primary, Button::Mini));
		$input[1]->setName('option-submit');
		
		if ( $cancel !== null ) { 
			$input[2] = new Button('Cancelar', new TgAjax($cancel, $this, TgAjax::Json), array(Button::Link, Button::Mini));
			$input[2]->setName('option-cancel');
		}
		
		$this->buildField(null, $input, null, $panel)->setName('option-input');
		
		$table = new TableCollection('options', $session);
		$table->setAlertNoRecords(null);
		$table->buildColumnAction('option-remove', new Icon('icon-remove'), new TgAjax($remove, $this, TgAjax::Json));
		$table->buildColumnText('description', '', null, null, ColumnText::Left, function( $value, $data, $key ) use ( $edit, $table ) {
			$edit = clone $edit;
			$edit->setParameter('key', $key);
			return new Anchor($value, new TgAjax($edit, $table, TgAjax::Json));
		});
		
		$panel->append($table);
		
		$this->append($panel);
	}
	
}
?>