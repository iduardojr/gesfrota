<?php
namespace Sigmat\Common;

/**
 * Entidade
 * @MappedSuperclass
 */
abstract class Entity {
	
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 * @var integer
	 */
	protected $id;
	
	/**
	 * Construtor
	 */
	public function __construct() {
		
	}
	
	/**
	 * Obtem identificador
	 * 
	 * @return integer
	 */
	public function getId() {
		return ( int ) $this->id;
	}
	
	/**
	 * Atribui uma propriedade
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @throws \RuntimeException
	 */
	public function __set( $name, $value ) {
		$method = 'set' . ucfirst($name);
		if ( ! method_exists( $this, $method ) ) {
			throw new \BadMethodCallException('unsupported method "' . $method . '" in ' . get_class($this));
		}
		call_user_func(array(&$this, $method), $value);
	}
	
	/**
	 * Obtem uma propriedade
	 * 
	 * @param string $name
	 * @throws \RuntimeException
	 */
	public function __get( $name ) {
		$method = 'get' . ucfirst($name);
		if ( ! method_exists( $this, $method ) ) {
			throw new \BadMethodCallException('unsupported method "' . $method . '" in ' . get_class($this));
		}
		return call_user_func(array(&$this, $method));
	}
	
}
?>