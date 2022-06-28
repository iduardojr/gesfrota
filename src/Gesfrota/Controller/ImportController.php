<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Model\Sys\Import;
use Gesfrota\View\ImportList;
use Gesfrota\View\Layout;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;


class ImportController extends AbstractController {
	
	public function indexAction() {
		try {
		    $filter = new Action($this);
		    $new = new Action($this, 'new');
		    $edit = new Action($this, 'edit');
		    $remove = new Action($this, 'remove');
		    $list = new ImportList($filter, $new, $edit, $remove);
		    
		    $helper = $this->createHelperCrud();
		    $query = $this->getEntityManager()->getRepository(Import::getClass())->createQueryBuilder('u');
		    
		    $helper->read($list, $query, array('limit' => 12, 'processQuery' => function( QueryBuilder $query, array $data ) {
		        if ( !empty($data['desc']) ) {
		            $query->andWhere('u.description LIKE :desc');
		            $query->setParameter('desc', '%' . $data['desc'] . '%');
		        }
		        if ( !empty($data['date-initial']) ) {
		            $query->andWhere('u.openedAt >= :initial');
		            $query->setParameter('initial', $data['date-initial']);
		        }
		        if ( !empty($data['date-final']) ) {
		            $query->andWhere('u.openedAt <= :final');
		            $query->setParameter('final', $data['date-final'] . ' 23:59:59');
		        }
		    }));
		    
		    $list->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
		    $list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
	    return new Crud($this->getEntityManager(), Import::getClass(), $this);
	}
}
?>