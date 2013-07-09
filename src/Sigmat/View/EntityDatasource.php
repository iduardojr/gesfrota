<?php
namespace Sigmat\View;

use PHPBootstrap\Mvc\Session\Session;
use PHPBootstrap\Widget\Pagination\Paginator;
use PHPBootstrap\Widget\Table\DataSource;
use Doctrine\ORM\QueryBuilder;

/**
 * Datasource
 */
class EntityDatasource extends Paginator implements DataSource {

	const IDENTIFY = 'id';
	
	/**
	 * @var array
	 */
	protected $data;
	
	/**
	 * Atual
	 * 
	 * @var array|object
	 */
	protected $current;
	
	/**
	 * Resetado
	 * 
	 * @var boolean
	 */
	protected $reset;
	
	/**
	 * @var boolean
	 */
	protected $loaded;
	
	/**
	 * @var Session
	 */
	protected $session;
	
	/**
	 * @var integer
	 */
	protected $count;
	
	/**
	 * @var array
	 */
	protected $defaults;
	
	/**
	 * Consulta
	 * 
	 * @var QueryBuilder
	 */
	protected $query;
	
	/**
	 * Construtor
	 * 
	 * @param QueryBuilder $query
	 * @param Session $session
	 * @param array $defaults
	 */
	public function __construct( QueryBuilder $query, Session $session, array $defaults = array() ) {
		$this->loaded = false;
		$this->query = $query;
		$this->session = $session;
		$this->data = array();
		$this->page = $session->page ? $session->page : 1;
		$this->defaults = array_merge(array('sort'=>$this->getIdentify(), 'order'=>self::Asc, 'limit'=>10), $defaults);
	}
	
	/**
	 * Obtem chave de identificação dos registros
	 *
	 * @return string
	 */
	public function getIdentify() {
		return constant(get_class($this) . '::IDENTIFY');
	}

	/**
	 * Obtem o rowset
	 *
	 * @return array|object
	 */
	public function fetch() {
		if ( ! $this->loaded ) {
			throw new \RuntimeException('data not found');
		}
		if ( current($this->data) === false ) {
			throw new \RuntimeException('end of the datasource');
		}
		return current($this->data);
	}

	/**
	 * Verifica e avança para o proximo rowset 
	 * 
	 * @return boolean
	 */
	public function next() {
		if ( ! $this->loaded ) {
			throw new \RuntimeException('data not found');
		}
		$current = $this->reset ? reset($this->data) : next($this->data);
		$this->reset = false;
		return $current !== false;
	}

	/**
	 * Atribui ordenação dos registros
	 * 
	 * @param string $sort
	 * @param string $order
	 */
	public function setOrderBy( $sort, $order ) {
		$this->loaded = false;
		$sort = $sort == $this->defaults['sort'] ? null : $sort;
		$order = $order == self::Desc ? self::Desc : self::Asc;
		$order = $order == $this->defaults['order'] ? null : $order; 
		$this->session->sort = $sort;
		$this->session->order = $order;
	}
	
	/**
	 * Alternar a ordenação dos registros
	 * 
	 * @param string $sort
	 */
	public function toggleOrder( $sort ) {
		$order = $this->getSort() == $sort && $this->getOrder() == self::Asc ? self::Desc : self::Asc;
		$this->setOrderBy($sort, $order);
	}

	/**
	 * Obtem o campo ordenado dos registros
	 *
	 * @return string
	 */
	public function getSort() {
		return isset($this->session->sort) ? $this->session->sort : $this->defaults['sort'];
	}
	
	/**
	 * Obtem a ordenação dos registros
	 *
	 * @return string
	 */
	public function getOrder() {
		return isset($this->session->order) ? $this->session->order : $this->defaults['order'];
	}
	
	/**
	 * Atribui o limite de registros por pagina
	 *  
	 * @param integer $limit
	 */
	public function setLimit( $limit ) {
		$this->loaded = false;
		parent::setLimit($limit);
		$limit = $limit == $this->defaults['limit'] ? null : $limit;
		$this->session->limit = $limit;
	}
	
	/**
	 * Atribui a pagina
	 * 
	 * @param integer $page
	 */
	public function setPage( $page ) {
		$this->loaded = false;
		parent::setPage($page);
		$this->session->page = $this->page == 1 ? null : $this->page;
	}
	
	/**
	 * Atribui um conjunto de dados para filtro
	 * 
	 * @param array $data
	 */
	public function setFilter( array $data ) {
		$this->loaded = false;
		$this->total = null;
		$this->session->filter = empty($data) ? null : $data;
	}
	
	/**
	 * Obtem os conjunto de dados para filtro
	 * 
	 * @return array
	 */
	public function getFilter() {
		return isset($this->session->filter) ? $this->session->filter : array();
	}
	
	/**
	 * Atribui o total de registros filtrados
	 * #unssuported
	 * 
	 * @param integer $total
	 * @throws \BadMethodCallException
	 */
	public function setTotal( $total ) {
		throw new \BadMethodCallException('unssuported method');
	}
	
	/**
	 * Obtem o total de registros filtrados
	 *
	 * @return integer
	 */
	public function getTotal() {
		if ( $this->total === null ) {
			$query = clone $this->query;
			$query->setFirstResult(null)
				  ->setMaxResults(null);
			if ( isset($this->defaults['processQuery']) ) {
				call_user_func($this->defaults['processQuery'], $query, $this->getFilter());
			}
			$query->select('COUNT(' .$query->getRootAlias() . '.' . $this->getIdentify() . ')');
			$this->total = (int) $query->getQuery()->getSingleScalarResult();
		}
		return $this->total;
	}
	
	/**
	 * Obtem o quantidade de registros
	 * 
	 * @return number
	 */
	public function getAmount() {
		if ( $this->count === null ) {
			$query = clone $this->query;
			$query->setFirstResult(null)
				  ->setMaxResults(null);
			$query->select('COUNT(' .$query->getRootAlias() . '.' . $this->getIdentify() . ')');
			$this->count = (int) $query->getQuery()->getSingleScalarResult();
		}
		return $this->count;
	}
	
	/**
	 * Verifica se existe dado para filtrar
	 * 
	 * @return boolean
	 */
	public function hasFilter() {
		foreach ( $this->getFilter() as $data ) {
			if ( !empty($data) ) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Obtem uma propriedade da linha atual
	 * 
	 * @param string $name
	 * @return scalar
	 */
	public function __get( $name ) {
		$current = $this->fetch();
		if ( is_array($current) ) {
			return isset($current[$name]) ? $current[$name] : null;
		}
		if ( is_callable(array(&$current, '__get')) ) {
			return $current->$name;
		}
		return null;
	}
	
	/**
	 * Atribui uma função para processar a query
	 * 
	 * @param \Closure $handler
	 */
	public function setProcessQuery( \Closure $handler = null ) {
		$this->defaults['processQuery'] = $handler;
	}
	
	/**
	 * Restabelece o conjunto de dados e aponta para o primeiro resultado
	 */
	public function reset() {
		if (! $this->loaded ) {
			$query = clone $this->query;
			$offset = $this->getOffset();
			$limit = $this->getLimit();
			if ( $limit == 0 ) {
				$offset = null;
				$limit = null;
			}
			$query->setFirstResult($offset)
				   ->setMaxResults($limit)
				   ->orderBy($query->getRootAlias() . '.' . $this->getSort(), $this->getOrder());
			if ( isset($this->defaults['processQuery']) ) {
				call_user_func($this->defaults['processQuery'], $query, $this->getFilter());
			}
			$this->data = $query->getQuery()->getResult();
			$this->loaded = true;
		}
		$this->reset = true; 
	}
	
}
?>