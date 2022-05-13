<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\VehicleModel;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;

class VehicleModelList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $active
	 * @param array $families
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $active, array $families ) {
		$this->buildPanel('Especificações de Veículos', 'Gerenciar Modelos');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('name');
		$input->setSpan(3);
		$form->buildField('Descrição', $input);
		
		$input = new ComboBox('family');
		$input->setOptions($families);
		$input->setSpan(3);
		$form->buildField('Família', $input);
		
		$input = new CheckBox('only-active', 'Apenas ativos');
		$form->buildField(null, $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), 
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('vehicle-model-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnText('fipe', '#', clone $filter, 70);
		$table->buildColumnText('name', 'Modelo', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('maker', 'Fabricante', clone $filter, 3, ColumnText::Left);
		$table->buildColumnText('family', 'Família', clone $filter, 3, ColumnText::Left);
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, VehicleModel $object ) {
		    $button->setIcon(new Icon($object->getActive() ? 'icon-remove' : 'icon-ok'));
		});
	}
	
}
?>