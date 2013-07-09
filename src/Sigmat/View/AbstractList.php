<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Widget;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Modal\TgModalConfirm;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\ButtonToolbar;
use PHPBootstrap\Widget\Button\ButtonGroup;
use PHPBootstrap\Widget\Table\DataSource;
use PHPBootstrap\Widget\Table\Table;
use PHPBootstrap\Widget\Pagination\Pagination;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Table\ColumnAction;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Pagination\Scrolling\Sliding;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Pagination\Paginator;


abstract class AbstractList extends Component {
	
	/**
	 * @var Table
	 */
	protected $component;
	
	/**
	 * @var Pagination
	 */
	protected $pagination;
	
	/**
	 * Constroi um modal para filtro
	 * 
	 * @param Form $form
	 * @param Action $submit
	 * @param Action $reset
	 * @return Modal
	 */
	protected function buildFilter( Form $form, Action $submit, Action $reset ) {
		$modal = new Modal('modal-filter', new Title('Pesquisar?', 3));
		$modal->setWidth(600);
		$modal->setBody($form);
		$modal->addButton(new Button('Pesquisar', new TgFormSubmit($submit, $form), Button::Primary));
		$modal->addButton(new Button('Remover Filtros', new TgLink($reset)));
		$this->panel->append($modal);
		return $modal;
	}
	
	/**
	 * Constroi um modal de confirmação
	 * 
	 * @param string $name
	 * @param Widget $body
	 * @return Modal
	 */
	protected function buildConfirm( $name, Widget $body ) {
		$modal = new Modal($name, new Title('Confirmar?', 3));
		$modal->setBody($body);
		$modal->addButton(new Button('Ok', new TgModalConfirm(), Button::Primary));
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($modal);
		return $modal;
	}
	
	/**
	 * Constroi uma barra de ferramentas
	 * 
	 * @param array $buttons
	 * @param ...
	 * @return ButtonToolbar
	 */
	protected function buildToolbar( $buttons ) {
		$toolbar = new ButtonToolbar('toolbar');
		foreach ( func_get_args() as $group ) {
			if ( $group instanceof ButtonGroup) {
				$toolbar->addButtonGroup($group);
				continue;
			}
			if ( ! is_array($group) ) {
				$group = array($group);
			}
			$btnGroup = new ButtonGroup();
			$toolbar->addButtonGroup($btnGroup);
			foreach( $group as $button ) {
				$btnGroup->addButton($button);
			}
		}
		$this->panel->append($toolbar);
		return $toolbar;
	}
	
	/**
	 * Constroi uma tabela
	 * 
	 * @param string $name
	 * @param DataSource $ds
	 * @param Action $pager
	 */
	protected function buildTable($name, Action $pager ) {
		if ( !isset($this->component) ) {
			$table = new Table($name, new EmptyDatasource());
			$table->setStyle(Table::Striped);
			$table->setStyle(Table::Hover);
			$table->setStyle(Table::Condensed);
			$table->setAlertNoRecords('Nenhum registro encontrado');
			$table->setFooter(new Panel(null));
			$this->pagination = new Pagination(new TgLink($pager), new Paginator(0, 0), new Sliding(10));
			$this->pagination->setAlign(Pagination::Right);
			$this->component = $table;
			$this->panel->append($table);
		}
		return $this->component;
	}
	
	/**
	 * Constroi uma coluna de texto
	 * 
	 * @param string $name
	 * @param string $label
	 * @param Action $sort
	 * @param integer $span
	 * @param string $align
	 * @param callback $filter
	 * @return ColumnText
	 */
	protected function buildColumnText( $name, $label, Action $sort = null, $span = null, $align = null, $filter = null ) {
		$column = new ColumnText($name, $label);
		if ( $sort !== null ) {
			$sort->setParameter('sort', $name);
			$column->setToggle(new TgLink($sort));
		}
		$column->setSpan($span);
		$column->setAlign($align);
		$column->setFilter($filter);
		$this->component->addColumn($column);
		return $column;
	}
	
	/**
	 * Constroi uma coluna de ação
	 * 
	 * @param string $name
	 * @param mixed $labels
	 * @param Action|Toggle $action
	 * @param Modal $confirm
	 * @param \Closure $context
	 * @return ColumnAction
	 */
	protected function buildColumnAction( $name, $labels, $toggle, Modal $confirm = null, \Closure $context = null ) {
		if ( $toggle instanceof Action ) {
			$toggle = new TgLink($toggle);
		}
		if ( $confirm !== null ) {
			$toggle = new TgModalOpen($confirm, $toggle);
		}
		$column = new ColumnAction($name, $labels, $toggle);
		$column->setContext($context);
		$this->component->addColumn($column);
		return $column;
	}
	
	/**
	 * Atribui um datasource
	 * 
	 * @param EntityDatasource $datasource
	 */
	public function setDatasource( EntityDatasource $datasource ) {
		$this->component->setDataSource($datasource);
		$this->pagination->setPaginator($datasource);
		if ( $datasource->getLimit() > 0 && $datasource->getTotal() > $datasource->getLimit() ) {
			$this->component->setPagination($this->pagination);
		}
		$this->update($datasource);
	}
	
	/**
	 * Atualiza a interface de acordo com datasource
	 * 
	 * @param EntityDatasource $datasource
	 */
	protected function update( EntityDatasource $datasource ) {
		
	}
	
}
?>