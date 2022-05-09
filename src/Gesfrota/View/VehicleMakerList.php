<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\VehicleMaker;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Form\Controls\ComboBox;

class VehicleMakerList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $active
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $active ) {
		$this->buildPanel('Especificações de Veículos', 'Gerenciar Fabricante');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('name');
		$input->setSpan(3);
		$form->buildField('Descrição', $input);
		
		$input = new ComboBox('type');
		$input->setOptions(['Todos'] + VehicleMaker::getTypesAllowed());
		$input->setSpan(3);
		$form->buildField('Descrição', $input);
		
		$input = new CheckBox('only-active', 'Apenas ativos');
		$form->buildField(null, $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), 
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('vehicle-maker-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('name', 'Nome', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('type', 'Tipo', clone $filter, 150, null, function ( $value ) {
			return VehicleMaker::getTypesAllowed()[$value];
		});
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, VehicleMaker $object ) {
		    $button->setIcon(new Icon($object->getActive() ? 'icon-remove' : 'icon-ok'));
		});
	}
	
}
?>