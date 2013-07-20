<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Table\DataSource;

/**
 * Datasource array
 */
class ArrayDatasource implements DataSource {
	
	const IDENTIFY = 'id';
	
	/**
	 * @var array
	 */
	protected $data;
	
	/**
	 * @var boolean
	 */
	protected $reset;
	
	/**
	 * Construtor
	 * 
	 * @param array $data
	 */
	public function __construct( array $data = array() ) {
		$this->data = $data;
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