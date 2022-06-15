<?php
namespace Gesfrota\Model\Domain;

/**
 * Motorista
 * @Entity
 */
class Driver extends User {
	
	/**
	 * @var string
	 */
	const USER_TYPE = 'Motorista';
	
	public function __construct() {
		$this->driverLicense = new DriverLicense();
		parent::__construct();
	}
	
	/**
	 * @param AdministrativeUnit $unit
	 */
	public function setLotation(AdministrativeUnit $unit) {
	    if ($this->lotation && $unit->getAgency() !== $this->lotation->getAgency()) {
	        $this->removeAllResultCenters();
	    }
	    $this->lotation = $unit;
	}
}
?>