<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\AbstractActivable;
use Gesfrota\Model\Entity;

/**
 * Modelo de Veículo
 * @Entity
 * @Table(name="vehicle_models")
 */
class VehicleModel extends AbstractActivable {

	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @Column(type="string", name="full_name" )
	 * @var string
	 */
	protected $fullName;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $fipe;
	
	/**
	 * @ManyToOne(targetEntity="VehicleFamily")
	 * @JoinColumn(name="vehicle_family_id", referencedColumnName="id")
	 * @var VehicleFamily
	 */
	protected $family;
	
	/**
	 * @ManyToOne(targetEntity="VehicleMaker")
	 * @JoinColumn(name="vehicle_maker_id", referencedColumnName="id")
	 * @var VehicleMaker
	 */
	protected $maker;
	
	/**
	 * @param string $name
	 */
	public function __construct($name = null) {
		parent::__construct();
		$this->setName($name);
	}
	
	/**
	 * @see Entity::getCode()
	 */
	public function getCode() {
	    return $this->getFipe();
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
	 * @return string
	 */
	public function getDescription() {
	    if ($this->fullName) {
	       return (string) $this->fullName;
	    }
	    return $this->maker . ' ' . $this->name;
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
	 * Atribui $fipe
	 *
	 * @param string $fipe
	 */
	public function setFipe( $fipe ) {
	    $this->fipe = $fipe;
	}
	
	/**
	 * Obtem $fipe
	 *
	 * @return string
	 */
	public function getFipe() {
	    return $this->fipe;
	}
	
	/**
	 * Atribui $family
	 *
	 * @param VehicleFamily $family
	 */
	public function setFamily( VehicleFamily $family ) {
	    $this->family = $family;
	}
	
	/**
	 * Obtem $family
	 *
	 * @return VehicleFamily
	 */
	public function getFamily() {
	    return $this->family;
	}
	
	/**
	 * Atribui $maker
	 *
	 * @param VehicleMaker $maker
	 */
	public function setMaker( VehicleMaker $maker ) {
	    $this->maker = $maker;
	}
	
	/**
	 * Obtem $maker
	 *
	 * @return VehicleMaker
	 */
	public function getMaker() {
	    return $this->maker;
	}
			
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getDescription();
	}
}
?>