<?php
namespace Gesfrota\View\Widget;

use PHPBootstrap\Widget\Table\Table;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Table\ColumnAction;
use PHPBootstrap\Widget\Table\DataSource;
use PHPBootstrap\Widget\Pagination\Pageable;
use PHPBootstrap\Widget\Pagination\Pagination;
use PHPBootstrap\Widget\Pagination\Paginator;
use PHPBootstrap\Widget\Pagination\Scrolling\Sliding;
use Gesfrota\Model\Entity;
use PHPBootstrap\Widget\Toggle\Togglable;

class BuilderTable extends Table {
	
	/**
	 * Construtor
	 * 
	 * @param string $name
	 */
	public function __construct( $name ) {
		parent::__construct($name, new ArrayDatasource());
		$this->setStyle(Table::Striped);
		$this->setStyle(Table::Hover);
		$this->setStyle(Table::Condensed);
		$this->setAlertNoRecords('Nenhum registro encontrado');
		$this->setFooter(new Panel(null));
	}
	
	/**
	 * Constroi uma paginação
	 *
	 * @param Action|TgLink $action
	 */
	public function buildPagination( $action ) {
		if ( $action instanceof Action ) {
			$action = new TgLink($action);
		}
		$pagination = new Pagination($action, $this->buildPaginator(), new Sliding(10));
		$pagination->setAlign(Pagination::Right);
		$this->setPagination($pagination);
		return $pagination;
	}
	
	/**
	 * Constroi um coluna de texto para id
	 * 
	 * @param integer $span
	 * @param Action $sort
	 * @param string $align
	 * @param callback $filter
	 * @return ColumnText
	 */
	public function buildColumnTextId( $span = null, Action $sort = null, $align = null, $filter = null ){
		if ( $span ===  null ) {
			$span = 80;
		}
		if ( ! $filter ) {
			$filter = function ( $value, Entity $object ) {
				return $object->getCode();
			};
		}
		return $this->buildColumnText('id', '#', $sort, $span, $align, $filter);
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
	public function buildColumnText( $name, $label, Action $sort = null, $span = null, $align = null, $filter = null ) {
		$column = new ColumnText($name, $label);
		if ( $sort !== null ) {
			$sort->setParameter('sort', $name);
			$column->setToggle(new TgLink($sort));
		}
		$column->setSpan($span);
		$column->setAlign($align);
		$column->setFilter($filter);
		$this->addColumn($column);
		return $column;
	}
	
	/**
	 * Constroi uma coluna de ação
	 *
	 * @param string $name
	 * @param mixed $labels
	 * @param Action|Togglable $action
	 * @param Modal $confirm
	 * @param \Closure $context
	 * @return ColumnAction
	 */
	public function buildColumnAction( $name, $labels, $toggle = null, Modal $confirm = null, \Closure $context = null ) {
		if ( $toggle instanceof Action ) {
			$toggle = new TgLink($toggle);
		}
		if ( $confirm !== null ) {
			$toggle = new TgModalOpen($confirm, $toggle);
		}
		$column = new ColumnAction($name, $labels, $toggle);
		$column->setContext($context);
		$this->addColumn($column);
		return $column;
	}
	
	/**
	 * Atribui fonte de dados
	 *
	 * @param DataSource $ds
	 */
	public function setDataSource( DataSource $ds ) {
		parent::setDataSource($ds);
		if ( $this->pagination ) {
			$this->pagination->setPaginator($this->buildPaginator());
		}
	}
	
	/**
	 * Obtem Paginação
	 *
	 * @return Pageable
	 */
	public function getPagination() {
		$ds = $this->getDataSource();
		if ( $ds->getLimit() > 0 && $ds->getTotal() > $ds->getLimit() ) { 
			return parent::getPagination();
		}
		return null;
	}
	
	/**
	 * Constroi um paginador a partir do datasource
	 * 
	 * @return Paginator
	 */
	public function buildPaginator() {
		$ds = $this->getDataSource();
		if ( ! $ds instanceof Paginator ) {
			$paginator = new Paginator($ds->getTotal(), $ds->getLimit());
			if ( $ds->getTotal() > 0 ) { 
				$paginator->setPage(ceil($ds->getOffset()/$ds->getTotal()));
			}
			return $paginator;
		} else {
			return $ds;
		}
	}
}
?>