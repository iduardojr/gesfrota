<?php
namespace Sigmat\Model\Stockroom;

use Sigmat\Model\Entity;
use Sigmat\Model\AdministrativeUnit\Agency;
use Doctrine\Common\Collections\ArrayCollection;
use Sigmat\Model\AdministrativeUnit\AdministrativeUnit;

/**
 * Almoxarifado
 * @Entity
 * @Table(name="stockrooms")
 */
class Stockroom extends Entity {
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @Column(type="boolean")
	 * @var boolean
	 */
	protected $status;
	
	/**
	 * @OneToOne(targetEntity="Sigmat\Model\AdministrativeUnit\Agency")
	 * @JoinColumn(name="agency_id", referencedColumnName="id")
	 * @var Agency
	 */
	protected $agency;
	
	/**
	 * @ManyToMany(targetEntity="Sigmat\Model\AdministrativeUnit\AdministrativeUnit")
	 * @JoinTable(name="stockrooms_has_administrative_units",
	 *      joinColumns={@JoinColumn(name="stockroom_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@JoinColumn(name="administrative_unit_id", referencedColumnName="id")}
	 * )
	 * @var ArrayCollection
	 */
	protected $units;
	
	/**
	 * Construtor
	 * 
	 * @param Agency $agency
	 */
	public function __construct( Agency $agency ) {
		$this->units = new ArrayCollection();
		$this->setAgency($agency);
		$this->setStatus(true);
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
	 * Obtem $status
	 *
	 * @return boolean
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * Atribui $status
	 *
	 * @param boolean $status
	 */
	public function setStatus( $status ) {
		$this->status = $status;
	}
	
	/**
	 * Obtem $agency
	 *
	 * @return Agency
	 */
	public function getAgency() {
		return $this->agency;
	}
	
	/**
	 * Atribui $agency
	 *
	 * @param Agency $agency
	 */
	public function setAgency( Agency $agency ) {
		$this->agency = $agency;
	}
	
	/**
	 * Adiciona uma unidade administrativa
	 * 
	 * @param AdministrativeUnit $unit
	 */
	public function addUnit( AdministrativeUnit $unit ) {
		$this->units[$unit->getId()] = $unit;
	}
	
	/**
	 * Remove uma unidade administrativa
	 * 
	 * @param AdministrativeUnit $unit
	 */
	public function removeUnit( AdministrativeUnit $unit ) {
		unset($this->units[$unit->getId()]);
	}
	
	/**
	 * Obtem a unidades administrativas
	 * 
	 * @return array
	 */
	public function getUnits() {
		return $this->units->toArray();
	}
}
?>