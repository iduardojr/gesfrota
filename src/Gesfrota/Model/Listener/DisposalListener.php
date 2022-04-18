<?php
namespace Gesfrota\Model\Listener;

use Gesfrota\Model\Domain\Disposal;
use Doctrine\ORM\Event\LifecycleEventArgs;

class DisposalListener {
    
    
    public function preRemove(Disposal $disposal, LifecycleEventArgs $event) { 
        if ( ! $disposal->getStatus() == Disposal::DRAFTED ) {
            throw new \DomainException('It is not allowed to delete the Disposal.');
        }
    }
}

