<?php
namespace Gesfrota\Model\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Gesfrota\Model\Domain\ImportItem;

class ImportItemListener {
    
    public function postRemove(ImportItem $item, LifecycleEventArgs $event) {
        
    }
}
?>