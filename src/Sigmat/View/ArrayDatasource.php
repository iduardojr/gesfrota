<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Table\DataSource;

/**
 * Datasource vazio
 */
class ArrayDatasource implements DataSource {
	
	const IDENTIFY = 'id';
	
	/**
	 * @var array
	 */
	protected $data;
	
	/**
	 * Construtor
	 * 
	 * @param array $data
	 */
	public function __construct( array $data = array() ) {
		$this->setData($data);
	}
	
	/**
	 * Atribui os dados
	 * 
	 * @param array $data
	 */
	public function setData( array $data ) {
		$this->data = $data;
	}
	
	/**
	 * Obtem os dados
	 * 
	 * @return array
	 */
	public function getData() {
		return $this->data;
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
	 * @see DataSource::fetch()
	 */
	public function fetch() {
		return current($this->data);
	}

	/**
	 * @see DataSource::next()
	 */
	public function next() {
		return next($this->data) !== false;
	}

	/**
	 * @see DataSource::getSort()
	 */
	public function getSort() {
		return null;
	}

	/**
	 * @see DataSource::getOrder()
	 */
	public function getOrder() {
		return self::Asc;
	}

	/**
	 * @see DataSource::getLimit()
	 */
	public function getLimit() {
		return 0;
	}

	/**
	 * @see DataSource::getOffset()
	 */
	public function getOffset() {
		return 0;
	}

	/**
	 * @see DataSource::getTotal()
	 */
	public function getTotal() {
		return count($this->data);
	}

	/**
	 * @see DataSource::reset()
	 */
	public function reset() {
		return reset($this->data);
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