<?php
namespace Gesfrota\View\Widget;

use Doctrine\ORM\QueryBuilder;
use PHPBootstrap\Widget\Pagination\Paginator;
use PHPBootstrap\Widget\Table\DataSource;
use Doctrine\ORM\Query\Expr\Select;


/**
 * Datasource
 */
class EntityDatasource extends Paginator implements DataSource {

	/**
	 * @var array
	 */
	protected $data;
	
	/**
	 * @var array|object
	 */
	protected $current;
	
	/**
	 * @var boolean
	 */
	protected $reset;
	
	/**
	 * @var boolean
	 */
	protected $loaded;
	
	/**
	 * @var integer
	 */
	protected $count;
	
	/**
	 * @var array
	 */
	protected $defaults;
	
	/**
	 * @var QueryBuilder
	 */
	protected $query;

	
	/**
	 * Construtor
	 * 
	 * @param QueryBuilder $query
	 * @param array $defaults
	 */
	public function __construct( QueryBuilder $query, array $defaults = array() ) {
		$this->loaded = false;
		$this->query = $query;
		$this->data = array();
		if ( ! isset($defaults['identify']) ) {
			$defaults['identify'] = 'id';
		}
		$this->defaults = array_merge(array('sort' => $defaults['identify'], 
											'order' => self::Asc, 
											'limit' => 10 ), $defaults);
		unset($this->defaults['page']);
		$this->setLimit($this->defaults['limit']);
		$this->setPage(( int ) isset($defaults['page']) && $defaults['page'] > 0 ? $defaults['page'] : 1);
	}
	
	/**
	 * Obtem a identificação do registro
	 *
	 * @return integer
	 */
	public function getIdentify() {
		return $this->__get($this->defaults['identify']);
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
		$this->defaults['sort'] = $this->formatAttribute($sort);
		$this->defaults['order'] = $order == self::Desc ? self::Desc : self::Asc;
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
		return $this->defaults['sort'];
	}
	
	/**
	 * Obtem a ordenação dos registros
	 *
	 * @return string
	 */
	public function getOrder() {
		return $this->defaults['order'];
	}
	
	/**
	 * Atribui o limite de registros por pagina
	 *  
	 * @param integer $limit
	 */
	public function setLimit( $limit ) {
		$this->loaded = false;
		parent::setLimit($limit);
		$this->defaults['limit'] = $limit;
	}
	
	/**
	 * Atribui a pagina
	 * 
	 * @param integer $page
	 */
	public function setPage( $page ) {
		$this->loaded = false;
		parent::setPage($page);
	}
	
	/**
	 * Atribui um conjunto de dados para filtro
	 * 
	 * @param array $data
	 */
	public function setFilter( array $data ) {
		$this->loaded = false;
		$this->total = null;
		$this->defaults['filter'] = empty($data) ? null : $data;
	}
	
	/**
	 * Obtem os conjunto de dados para filtro
	 * 
	 * @return array
	 */
	public function getFilter() {
		return isset($this->defaults['filter']) ? $this->defaults['filter'] : array();
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
			$query->select('COUNT( DISTINCT ' . $this->formatAttribute($this->defaults['identify']) . ')');
			
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
			
			$query->select('COUNT( DISTINCT ' . $this->formatAttribute($this->defaults['identify']) . ')');
			
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
	 * @return mixed
	 */
	public function __get( $name ) {
		$start = stripos($name, '.');
		if ( $start !== false) {
			$name = substr($name, $start+1);
		}
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
	 * @param callback $handler
	 * @throws \InvalidArgumentException
	 */
	public function setProcessQuery( $handler ) {
		if ( ! ( is_callable($handler) || $handler == null ) ){
			throw new \InvalidArgumentException('handler not is callable');
		}
		$this->defaults['processQuery'] = $handler;
	}
	
	/**
	 * Restabelece o conjunto de dados e aponta para o primeiro resultado
	 */
	public function reset() {
		if ( ! $this->loaded ) {
			$query = clone $this->query;
			$offset = $this->getOffset();
			$limit = $this->getLimit();
			if ( $limit == 0 ) {
				$offset = null;
				$limit = null;
			}
			
			$query->setFirstResult($offset)
				   ->setMaxResults($limit);
			
			if ( isset($this->defaults['processQuery']) ) {
				call_user_func($this->defaults['processQuery'], $query, $this->getFilter());
			}
				   
			if ($this->getSort()) {
				$query->addOrderBy($this->formatAttribute($this->getSort()), $this->getOrder());
			}
			
			$this->data = $query->getQuery()->getResult();
			$this->loaded = true;
		}
		$this->reset = true; 
	}
	
	private function formatAttribute($attr) {
		if ( strpos($attr, '.') === false ) {
			return $this->query->getRootAlias() . '.' . $attr;
		}
		return $attr;
	}
	
}
?>