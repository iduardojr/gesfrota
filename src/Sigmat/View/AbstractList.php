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
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Pagination\Scrolling\Sliding;


abstract class AbstractList extends Component {
	
	/**
	 * @var Table
	 */
	protected $component;
	
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
		$modal->setBody($form);
		$reset->setParameter('reset-filter', 1);
		$modal->addButton(new Button('Pesquisar', new TgFormSubmit($submit, $form), Button::Primary));
		$modal->addButton(new Button('Limpar Filtro', new TgLink($reset)));
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
	protected function buildTable($name, DataSource $ds, Action $pager ) {
		if ( !isset($this->component) ) {
			$table = new Table($name, $ds);
			$table->setStyle(Table::Striped);
			$table->setStyle(Table::Hover);
			$table->setStyle(Table::Condensed);
			$table->setAlertNoRecords('Nenhum registro encontrado');
			
			if ( $ds->getTotal() > $ds->getLimit() ) {
				$pagination = new Pagination(new TgLink($pager), $ds, new Sliding(10));
				$pagination->setAlign(Pagination::Right);
				$table->setPagination($pagination);
			}
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
	 * @param string $icon
	 * @param Action $action
	 * @param Modal $confirm
	 * @return ColumnAction
	 */
	protected function buildColumnAction( $name, $icon, Action $action, Modal $confirm = null ) {
		$action->setMethodName($name);
		$toggle = new TgLink($action);
		if ($confirm !== null ) {
			$toggle = new TgModalOpen($confirm, $toggle);
		}
		$column = new ColumnAction($name, new Icon($icon), $toggle);
		$this->component->addColumn($column);
		return $column;
	}
	
	public function setPage( $page ) {
		
	}
	
	public function setSort( $sort ) {
		
	}
	
	public function setFilter( $filter ) {
		
	}
}
?>