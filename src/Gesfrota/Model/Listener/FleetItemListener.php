<?php
namespace Gesfrota\Model\Listener;

use Gesfrota\Model\Domain\FleetItem;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class FleetItemListener {
    
    public function preUpdate(FleetItem $item, PreUpdateEventArgs $event)  {
        $item->setUpdated();
    }
}
?>