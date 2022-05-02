<?php
namespace Gesfrota\Model\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Gesfrota\Model\Entity;
use Gesfrota\Services\Logger;

class LoggerListener {
	
	public function postPersist(Entity $object, LifecycleEventArgs $event) {
		Logger::getInstance()->create($object);
	}
	
	public function preUpdate(Entity $object, LifecycleEventArgs $event) {
		Logger::getInstance()->update($object);
	}
	
	public function preRemove(Entity $object, LifecycleEventArgs $event) {
		Logger::getInstance()->remove($object);
	}
}
?>