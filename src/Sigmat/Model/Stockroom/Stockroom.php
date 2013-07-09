<?php
namespace Sigmat\Model\Stockroom;

use Sigmat\Model\Entity;
use Sigmat\Model\AdministrativeUnit\Agency;

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
	 * Construtor
	 */
	public function __construct() {
	
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
}
?>