<?php
namespace Gesfrota\View\Widget;

use PHPBootstrap\Widget\Table\DataSource;
use PHPBootstrap\Widget\Form\Inputable;
use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Common\ArrayIterator;
use PHPBootstrap\Mvc\Session\Session;
use PHPBootstrap\Widget\Table\Table;

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
		if (! ( is_array($value) || $value === null ) ) {
			throw new \InvalidArgumentException('value not is valid');
		}
		if ( $value === null ) {
			$value = [];
		}
		$this->session->{$this->getName()} = $value;
		$this->dataSource = new ArrayDatasource($value);
	}

	/**
	 * @see Inputable::getValue()
	 */
	public function getValue() {
		return is_array($this->session->{$this->getName()}) ? $this->session->{$this->getName()} : [];
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
	public function buildPagination( EntityDatasource $datasource, Action $pager ) {
		throw new \BadMethodCallException('unssuported method');
	}
	
	/**
	 * @param mixed $item
	 * @param mixed $key
	 */
	public function addItem( $item, $key = null ) {
		$items = $this->getValue();
		if ( $key === null ) {
			$items[] = $item;
		} else {
			$items[$key] = $item;
		}
		$this->setValue($items);
	}
	
	/**
	 * @param mixed $key
	 * @return boolean
	 */
	public function removeItem( $key ) {
		$items = $this->getValue();
		if ( ! isset($items[$key]) ) {
			return false;
		}
		unset($items[$key]);
		$this->setValue($items);
		return true;
	}
	
}
?>