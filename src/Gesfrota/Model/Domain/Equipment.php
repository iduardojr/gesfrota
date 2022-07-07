<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\Entity;

/**
 * Equipamento
 * @Entity
 * @Table(name="equipments")
 */
class Equipment extends FleetItem {
	
	/**
	 * @var string
	 */
	const FLEET_TYPE = 'Equipamento';
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $description;
	
	/**
	 * @Column(name="serial_number", type="string")
	 * @var string
	 */
	protected $serialNumber;
	
	/**
	 * {@inheritDoc}
	 * @see Entity::getCode()
	 */
	public function getCode() {
	    return $this->getSerialNumber();
	}
	
	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
     * @return string
     */
    public function getSerialNumber() {
        return $this->serialNumber;
    }
    
    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }
    
    /**
     * @param string $serialNumber
     */
    public function setSerialNumber($serialNumber) {
        $this->serialNumber = $serialNumber;
    }

    


}
?>