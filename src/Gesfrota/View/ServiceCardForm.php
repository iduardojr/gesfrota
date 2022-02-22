<?php
namespace Gesfrota\View;

use Gesfrota\View\Widget\BuilderForm;
use Gesfrota\View\Widget\TableCollection;
use PHPBootstrap\Mvc\Session\Session;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;

class ServiceCardForm extends BuilderForm {
    
	/**
	 * @param Action $add
	 * @param Action $remove
	 * @param array $providers
	 * @param Session $session
	 */
    public function __construct(Action $add, Action $remove, array $providers, Session $session ) {
        parent::__construct('service-card-form');
        $panel = new Fieldset('Cartões de Serviços');
		
		$input = new TextBox('service-card-number');
		$input->setSpan(4);
		$input->addFilter('strtoupper');
		$this->buildField('Número', $input, null, $panel);
		
		$input = new ComboBox('service-provider-id');
		$input->setSpan(4);
		$input->setOptions($providers);
		$this->buildField('Provedor de Serviço', $input, null, $panel);
		
		$button = new Button('Adicionar', new TgFormSubmit($add, $this), array(Button::Primary, Button::Mini));
		$button->setName('service-card-add');
		$this->buildField(null, $button, null, $panel);
		
		$table = new TableCollection('cards', $session);
		$table->buildColumnAction('service-card-remove', new Icon('icon-remove'), new TgAjax($remove, $this, TgAjax::Json));
		$table->buildColumnText('number', '#', null, 70);
		$table->buildColumnText('serviceProvider', 'Provedor de Serviço', null, null, ColumnText::Left);
        $panel->append($table);
        $this->append($panel);
	}
	
	/**
	 * @return TableCollection
	 */
	public function getTableCollection() {
		return $this->getByName('cards');
	}
		
}
?>