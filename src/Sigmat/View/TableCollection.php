<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Table\DataSource;
use PHPBootstrap\Widget\Form\Inputable;
use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Mvc\Session\Session;
use PHPBootstrap\Common\ArrayIterator;
use PHPBootstrap\Widget\Action\Action;

class TableCollection extends BuilderTable implements Inputable {
	
	/**
	 * @var Session
	 */
	protected $session;
	
	/**
	 * Construtor
	 * 
	 * @param string $name
	 * @param Session $session
	 */
	public function __construct( $name, Session $session ) {
		$this->session = $session;
		parent::__construct($name);
		$this->setValue($this->getValue());
	}
	
	/**
	 * @see Inputable::setValue()
	 */
	public function setValue( $value ) {
		if ( ! ( is_array($value) || $value === null ) ) {
			throw new \InvalidArgumentException('value not is valid');
		}
		if ( $value === null ) {
			$value = array();
		}
		$this->session->{$this->getName()} = $value;
		$this->dataSource = new ArrayDatasource($value);
	}

	/**
	 * @see Inputable::getValue()
	 */
	public function getValue() {
		return is_array($this->session->{$this->getName()}) ? $this->session->{$this->getName()} : array();
	}

	/**
	 * @see Inputable::prepare()
	 */
	public function prepare( Form $form ) {
		
	}

	/**
	 * @see Inputable::valid()
	 */
	public function valid() {
		return true;
	}

	/**
	 * @see Inputable::getFailMessages()
	 */
	public function getFailMessages() {
		return new ArrayIterator();
	}
	
	/**
	 * @see Table::setDataSource()
	 */
	public function setDataSource( DataSource $ds ) {
		if ( $this->dataSource !== null ) { 
			throw new \BadMethodCallException('unssuported method');
		}
		parent::setDataSource($ds);
	}
	
	/**
	 * @see BuilderTable::buildPagination()
	 */
	public function buildPagination( EntityDatasource $datasource, Action $pager) {
		throw new \BadMethodCallException('unssuported method');
	}
	
}
?>