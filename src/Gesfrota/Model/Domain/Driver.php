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
}
?>