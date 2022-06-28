<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\ResultCenter;
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

class ResultCenterList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $active
	 * @param array $showAgencies
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $active, array $showAgencies = null ) {
		$this->buildPanel('Estrutura Organizacional', 'Gerenciar Centro de Resultados');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		if ($showAgencies) {
			$input = new ComboBox('agency');
			$input->setSpan(2);
			$input->setOptions($showAgencies);
			$form->buildField('Órgão', $input);
		}
		
		$input = new TextBox('description');
		$input->setSpan(3);
		$form->buildField('Descrição', $input);
		
		$input = new CheckBox('only-active', 'Apenas ativos');
		$form->buildField(null, $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), 
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('result-center-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId();
		$table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		if ($showAgencies) {
			$table->buildColumnText('agency', 'Órgão', clone $filter, 75);
		}
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, ResultCenter $unit ) {
			if ( ! $unit->getActive() ) {
				$button->setIcon(new Icon('icon-ok'));
			} else {
				$button->setIcon(new Icon('icon-remove'));
			}
		});
	}
	
}
?>