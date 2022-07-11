<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\NoticeRead;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\Manager;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\TrafficController;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\ArrayDatasource;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Misc\Badge;
use PHPBootstrap\Widget\Table\ColumnText;

class NoticeReadTable extends BuilderTable {
	
	public function __construct(array $readByUsers) {
	    parent::__construct('notice-read-table');
		$this->setDataSource(new ArrayDatasource($readByUsers));
		
		$this->buildColumnTextId(null, null, null, function ( $value, NoticeRead $object ) {
		    return $object->getCode();
		});
		$this->buildColumnText('name', 'Usuário', null, null, ColumnText::Left);
		$this->buildColumnText('user', null, null, 120, null, function(User $user) {
		    $label = new Badge($user->getUserType());
		    $value = get_class($user);
		    switch ($value) {
		        case Manager::getClass():
		            $label->setStyle(Badge::Inverse);
		            break;
		            
		        case FleetManager::getClass():
		            $label->setStyle(Badge::Important);
		            break;
		            
		        case TrafficController::getClass():
		            $label->setStyle(Badge::Success);
		            break;
		            
		        case Driver::getClass():
		            $label->setStyle(Badge::Warning);
		            break;
		            
		        case Requester::getClass():
		            $label->setStyle(Badge::Info);
		            break;
		    }
		    return $label;
		});
	    $this->buildColumnText('nif', 'CPF', null, 120);
	    $this->buildColumnText('lotation', 'Lotação', null, 100);
	    $this->buildColumnText('readAt', 'Lido em', null, 120, null, function (\DateTime $value) {
	        return $value->format('d/m/Y H:i');
	    });
	}
	
}
?>