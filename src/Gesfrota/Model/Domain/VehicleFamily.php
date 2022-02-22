<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\AbstractActivable;

/**
 * Família de Veículos
 * @Entity
 * @Table(name="vehicle_families")
 */
class VehicleFamily extends AbstractActivable {

	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * Construtor
	 *
	 * @param string $name
	 */
	public function __construct( $name = null ) {
	    $this->setName($name);
	    parent::__construct();
	}
	
	/**
	 * Obtem $name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
		
	/**
	 * Atribui $name
	 *
	 * @param string $name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}
	
		
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getName();
	}
}
?>