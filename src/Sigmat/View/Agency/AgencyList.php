<?php
namespace Sigmat\View\Agency;

use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Misc\Paragraph;
use Sigmat\View\AbstractList;
use Sigmat\View\EntityDatasource;
use PHPBootstrap\Widget\Button\ButtonGroup;

class AgencyList extends AbstractList {
	
	/**
	 * @var FormFilter
	 */
	protected $formFilter;
	
	/**
	 * @var ButtonGroup
	 */
	protected $btnGroupFilter;
	
	/**
	 * @var Button
	 */
	protected $btnResetFilter;
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $remove
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $remove ) {
		$panel = $this->buildPanel('Administração', 'Gerenciar Orgãos');
		$modalConfirm = $this->buildConfirm('confirm-remove', new Paragraph('Deseja realmente excluir esse orgão e todos os seus cadastros?'));
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$this->formFilter = new FormFilter();
		$modalFilter = $this->buildFilter($this->formFilter, clone $filter, $reset );
		
		$this->btnGroupFilter = new ButtonGroup(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link)));
		$this->btnResetFilter = new Button(array('Remover Filtros', new Icon('icon-eye-close')), new TgLink($reset), array(Button::Link, Button::Mini));
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary), $this->btnGroupFilter);
		
		$this->buildTable('agency-table', clone $filter);
		
		$this->buildColumnText('id', '#', clone $filter, 70, null, function( $value ) {
			return str_repeat('0', 3 - strlen($value)) . $value; 
		});
		$this->buildColumnText('acronym', 'Sigla', clone $filter, 200, ColumnText::Left);
		$this->buildColumnText('name', 'Nome', clone $filter, null, ColumnText::Left);
		$this->buildColumnText('status', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? '<span class="label label-success">Ativo<span>' : '<span class="label label-important">Inativo</span>';
		});
		$this->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$this->buildColumnAction('remove', new Icon('icon-remove'), $remove, $modalConfirm);
		
	}
	
	/**
	 * @see AbstractList::update()
	 */
	protected  function update( EntityDatasource $datasource ) {
		$this->formFilter->bind($datasource->getFilter());
		$datasource->setFilter($this->formFilter->getData());
		if ( $datasource->hasFilter() ) {
			$this->btnGroupFilter->addButton($this->btnResetFilter);
		}
	}
}
?>