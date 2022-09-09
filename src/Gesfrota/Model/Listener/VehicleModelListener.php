<?php
namespace Gesfrota\Model\Listener;

use Gesfrota\Model\Domain\VehicleModel;

class VehicleModelListener {
	
	/**
	 * @param VehicleModel $obj
	 * @preUpdate
	 * @prePersist
	 */
    public function setFullName(VehicleModel $obj) { 
        $ref = new \ReflectionProperty($obj, 'fullName');
        $ref->setAccessible(true);
        $ref->setValue($obj, $obj->getMaker() . ' ' . $obj->getName());
	}
	
}
