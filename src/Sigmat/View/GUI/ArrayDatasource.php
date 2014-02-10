<?php
namespace Sigmat\View\GUI;

use PHPBootstrap\Widget\Table\DataSource;

/**
 * Datasource array
 */
class ArrayDatasource implements DataSource {
	
	/**
	 * @var array
	 */
	protected $data;
	
	/**
	 * @var boolean
	 */
	protected $reset;
	
	/**
	 * Identificação
	 * 
	 * @var string
	 */
	protected $identify;
	
	/**
	 * Construtor
	 * 
	 * @param array $data
	 * @param string $identify
	 */
	public function __construct( array $data = array(), $identify = null ) {
		$this->data = $data;
		$this->identify = $identify;
	}
	
	/**
	 * Obtem a identificação do registro
	 *
	 * @return integer
	 */
	public function getIdentify() {
		return empty($this->identify) ? key($this->data) : $this->__get($this->identify);
	}

	/**
	 * Obtem o rowset
	 *
	 * @return array|object
	 */
	public function fetch() {
		if ( ! is_array($this->data) ) {
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
		if ( ! is_array($this->data) ) {
			throw new \RuntimeException('data not found');
		}
		$current = $this->reset ? reset($this->data) : next($this->data);
		$this->reset = false;
		return $current !== false;
	}

	/**
	 * Obtem o campo ordenado dos registros
	 *
	 * @return string
	 */
	public function getSort() {
		return null;
	}

	/**
	 * Obtem a ordenação dos registros
	 *
	 * @return string
	 */
	public function getOrder() {
		return self::Asc;
	}

	/**
	 * Obtem quantidade de registros a retornar
	 *
	 * @return integer
	 */
	public function getLimit() {
		return 0;
	}

	/**
	 * Obtem o indice do primeiro registro
	 *
	 * @return integer
	 */
	public function getOffset() {
		return 0;
	}

	/**
	 * Obtem o total de registros
	 *
	 * @return integer
	 */
	public function getTotal() {
		return count($this->data);
	}

	/**
	 * Restabelece o conjunto de dados
	 */
	public function reset() {
		$this->reset = true;
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
}
?>