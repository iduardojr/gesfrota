<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;

class ServiceProviderList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $active
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $active ) {
	    $this->buildPanel('Entidades Externas', 'Gerenciar Prestadores de Serviço');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('name');
		$input->setSpan(3);
		$form->buildField('Nome', $input);
		
		$input = new TextBox('nif');
		$input->setMask('99.999.999/9999-99');
		$input->setSpan(3);
		$form->buildField('CNPJ', $input);
		
		$input = new ComboBox('status');
		$input->setSpan(2);
		$input->addOption(0, 'Todos');
		$input->addOption(1, 'Ativos');
		$input->addOption(-1, 'Inativos');
		$form->buildField('Status', $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), 
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('service-providers-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('name', 'Razão Social', clone $filter, 300, ColumnText::Left);
		$table->buildColumnText('alias', 'Nome Fantasia', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('nif', 'CNPJ', clone $filter, 200, ColumnText::Left);
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, ServiceProvider $obj ) {
		    $button->setIcon(new Icon($obj->getActive() ? 'icon-remove' : 'icon-ok'));
		});
	}
	
}
?>