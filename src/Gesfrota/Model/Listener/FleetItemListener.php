<?php
namespace Gesfrota\Model\Listener;

use Gesfrota\Model\Domain\FleetItem;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

class FleetItemListener {
    
    public function preUpdate(FleetItem $item, PreUpdateEventArgs $event)  {
        $item->setUpdated();
    }
    
    public function prePersist(FleetItem $item, LifecycleEventArgs $event) {
    	if ($item->getResponsibleUnit()->isGovernment()) {
    		throw new \DomainException('Not allowed to persist Fleet Item because the responsible unit is Government.');
    	}
    }
}
?>