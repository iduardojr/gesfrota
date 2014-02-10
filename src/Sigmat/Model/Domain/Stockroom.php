<?php
namespace Sigmat\Model\Domain;

use Sigmat\Model\AbstractActivable;

/**
 * Almoxarifado
 * @Entity
 * @Table(name="stockrooms")
 */
class Stockroom extends AbstractActivable {
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @OneToOne(targetEntity="Sigmat\Model\Domain\Agency", fetch="EAGER")
	 * @JoinColumn(name="agency_id", referencedColumnName="id")
	 * @var Agency
	 */
	protected $agency;
	
	/**
	 * Construtor
	 * 
	 * @param Agency $agency
	 */
	public function __construct( Agency $agency ) {
		parent::__construct();
		$this->setAgency($agency);
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