<?php
namespace Gesfrota\Model;

use Gesfrota\Util\Format;



/**
 * Entidade
 * @MappedSuperclass
 * @EntityListeners({"Gesfrota\Model\Listener\LoggerListener"})
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
	 * Obtem o identificador formatado
	 * 
	 * @return string
	 */
	public function getCode() {
		return Format::code($this->getId(), 3);
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
	
	/**
	 * Obtem o nome da classe
	 * 
	 * @return string
	 */
	public static function getClass() {
		return get_called_class();
	}
	
	/**
	 * @return array
	 */
	public function toArray() {
		return array_reverse(get_object_vars($this));
	}
	
}
?>