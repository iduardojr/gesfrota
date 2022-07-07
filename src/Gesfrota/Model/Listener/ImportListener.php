<?php
namespace Gesfrota\Model\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Gesfrota\Model\Domain\Import;

class ImportListener {
    
    public function postRemove(Import $import, LifecycleEventArgs $event) { 
        $filename = DIR_ROOT . str_replace('/', DIRECTORY_SEPARATOR, Import::DIR) . $import->getFileName();
        unlink($filename);
    }
    
}
?>