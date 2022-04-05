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
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	
	/**
	 * {@inheritDoc}
	 * @see Entity::getCode()
	 */
	public function getCode() {
		return $this->getAsset()->getCode();
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


}
?>