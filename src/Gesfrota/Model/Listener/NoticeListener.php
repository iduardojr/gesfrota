<?php
namespace Gesfrota\Model\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Gesfrota\Model\Notice;

class NoticeListener {
    
    public function preRemove(Notice $notice, LifecycleEventArgs $event) { 
        if ( ! $notice->canDelete() ) {
            throw new \DomainException('It is not allowed to delete the Notice.');
        }
    }
    
}
?>