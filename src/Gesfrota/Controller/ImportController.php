<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Model\Sys\Import;
use Gesfrota\View\ImportList;
use Gesfrota\View\Layout;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Gesfrota\View\ImportUploadForm;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Sys\ImportItem;
use Gesfrota\Model\Domain\Agency;
use Doctrine\ORM\Query\Expr\Join;

class ImportController extends AbstractController {
	
	public function indexAction() {
		try {
		    $filter = new Action($this);
		    $new = new Action($this, 'new');
		    $edit = new Action($this, 'edit');
		    $down = new Action($this, 'down');
		    $remove = new Action($this, 'remove');
		    $list = new ImportList($filter, $new, $edit, $down, $remove);
		    
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
	
	public function newAction() {
	    try {
	        $form = new ImportUploadForm(new Action($this, 'new'), new Action($this));
	        $helper = $this->createHelperCrud();
	        if ( $helper->create($form) ){
	            $entity = $helper->getEntity();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Importação <em>#' . $entity->code . ' ' . $entity->description . '</em> realizada com sucesso!', Alert::Success));
	            $this->forward('/');
	        }
	    } catch ( InvalidRequestDataException $e ){
	        $form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	        throw $e;
	    } catch ( \Exception $e ) {
	        $form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	        throw $e;
	    }
	    return new Layout($form);
	}
	
	public function editAction() {
	    try {
	        $key = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(Import::getClass(), $key);
	        if (! $entity instanceof Import) {
	            throw new NotFoundEntityException('Não foi possível editar a Importação. Importação <em>#' . $key . '</em> não encontrada.');
	        }
	        $qb = $this->getEntityManager()->createQueryBuilder();
	        $qb->select('DISTINCT u.groupBy, a.id');
	        $qb->from(ImportItem::getClass(), 'u');
	        $qb->leftJoin(Agency::getClass(), 'a', Join::WITH, 'a.acronym LIKE CONCAT(CONCAT(\'%\', u.groupBy), \'%\') OR a.name LIKE CONCAT(CONCAT(\'%\', u.groupBy), \'%\')');
	        $qb->where('u.reference IS NULL AND u.import = :key');
	        $qb->setParameter('key', $entity);
	        var_dump($qb->getQuery()->getArrayResult());
	        /*
	        $form = $this->createForm(new Action($this, 'edit', array('key' => $key)));
	        $helper = $this->createHelperCrud();
	        $helper->setException(new NotFoundEntityException('Não foi possível editar o Orgão. Orgão <em>#' . $id . '</em> não encontrado.'));
	        if ( $helper->update($form, $id) ) {
	            $entity = $helper->getEntity();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Orgão <em>#' . $entity->code . ' ' . $entity->acronym .  '</em> alterado com sucesso!', Alert::Success));
	            $this->forward('/');
	        }
	        */
	    } catch ( NotFoundEntityException $e ) {
	        $this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	        $this->forward('/');
	    } catch ( InvalidRequestDataException $e ) {
	        //$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch ( \Exception $e ) {
	        throw $e;
	        $this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	        $this->forward('/');
	        //$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	    }
	    return new Layout();
	}
	
	public function downAction() {
	    try {
    	    $key = $this->request->getQuery('key');
    	    $entity = $this->getEntityManager()->find(Import::getClass(), $key);
    	    if (! $entity instanceof Import) {
    	        throw new NotFoundEntityException('Não foi possível baixar o Arquivo Importado. Importação <em>#' . $key . '</em> não encontrada.');
    	    }
    	    $this->redirect(Import::DIR . $entity->getFileName());
	    } catch ( NotFoundEntityException $e ) {
	        $this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	        $this->forward('/');
	    }
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
	    return new Crud($this->getEntityManager(), Import::getClass(), $this);
	}
	
}
?>