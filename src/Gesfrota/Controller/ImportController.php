<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Sys\Import;
use Gesfrota\Model\Sys\ImportItem;
use Gesfrota\View\ImportList;
use Gesfrota\View\ImportPreProcessForm;
use Gesfrota\View\ImportUploadForm;
use Gesfrota\View\Layout;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class ImportController extends AbstractController {
	
	public function indexAction() {
		try {
		    $filter = new Action($this);
		    $upload = new Action($this, 'upload');
		    $process = new Action($this, 'pre-process');
		    $transfom = new Action($this, 'transfom');
		    $download = new Action($this, 'download');
		    $remove = new Action($this, 'remove');
		    $list = new ImportList($filter, $upload, $process, $transfom, $download, $remove);
		    
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
	
	public function uploadAction() {
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
	
	public function preProcessAction() {
	    try {
	        $key = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(Import::getClass(), $key);
	        if (! $entity instanceof Import) {
	            throw new NotFoundEntityException('Não foi possível processar a Importação. Importação <em>#' . $key . '</em> não encontrada.');
	        }
	        $qb = $this->getEntityManager()->createQueryBuilder();
	        $qb->select('DISTINCT u.groupBy AS term');
	        $qb->from(ImportItem::getClass(), 'u');
	        $qb->where('u.reference IS NULL AND u.import = :key');
	        $qb->setParameter('key', $entity);
	        $data = $qb->getQuery()->getArrayResult();
	        $qb = $this->getEntityManager()->createQueryBuilder();
	        $qb->select('u.id');
	        $qb->from(Agency::getClass(), 'u');
	        $qb->setMaxResults(1);
	        foreach ($data as $i => $item) {
	            $qb->where('MATCH(u.acronym, u.name)  AGAINST(\'' . $item['term']  . '\') > 0');
	            $qb->addOrderBy('MATCH(u.acronym, u.name)  AGAINST(\'' . $item['term']  . '\')', 'DESC');
	            $data[$i]['suggest'] = $qb->getQuery()->getSingleScalarResult();
	        }
	        
	        $qb = $this->getEntityManager()->getRepository(Agency::getClass())->createQueryBuilder('u');
	        $qb->where('u.active = true AND u.id > 0');
	        $qb->orderBy('u.acronym', 'ASC');
	        $result = $qb->getQuery()->getResult();
	        $options = ['' => 'Selecione um Órgão'];
	        foreach ($result as $item) {
	            $options[$item->id] = $item . ' (' . $item->id . ')';
	        }
	        
	        $form = new ImportPreProcessForm($data, $options, new Action($this, 'pre-process', ['key' => $key]), new Action($this));
	        $form->extract($entity);
	        if ( $this->request->isPost() ) {
	            $form->bind($this->request->getPost());
	            if ( ! $form->valid() ) {
	                throw new InvalidRequestDataException();
	            }
	            $qb = $this->getEntityManager()->createQueryBuilder();
	            $qb->update(ImportItem::getClass(), 'u');
	            $data = $form->getData();
	            foreach ($data as $item) {
	                $qb->set('u.agency', $item['suggest']);
	                $qb->where('u.groupBy = :term AND u.id = :key');
	                $qb->setParameter('key', $entity);
	                $qb->setParameter('term', $item['term']);
	                $qb->getQuery()->execute();
	            }
	            
	            $this->setAlert(new Alert('<strong>Ok! </strong>Pré-processamento de <em>#' . $entity->code . ' ' . $entity->description .  '</em> realizado com sucesso!', Alert::Success));
	            $this->forward('/');
	        } 
	    } catch ( InvalidRequestDataException $e ) {
	        $form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch ( \Exception $e ) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error));
	        $this->forward('/');
	    }
	    return new Layout($form);
	}
	
	public function transformAction() {
	    try {
	        $key = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(Import::getClass(), $key);
	        if (! $entity instanceof Import) {
	            throw new NotFoundEntityException('Não foi possível processar a Importação. Importação <em>#' . $key . '</em> não encontrada.');
	        }
	        $qb = $this->getEntityManager()->createQueryBuilder();
	        $qb->select('DISTINCT u.groupBy AS term');
	        $qb->from(ImportItem::getClass(), 'u');
	        $qb->where('u.reference IS NULL AND u.import = :key');
	        $qb->setParameter('key', $entity);
	        $data = $qb->getQuery()->getArrayResult();
	        $qb = $this->getEntityManager()->createQueryBuilder();
	        $qb->select('u.id');
	        $qb->from(Agency::getClass(), 'u');
	        $qb->setMaxResults(1);
	        foreach ($data as $i => $item) {
	            $qb->where('MATCH(u.acronym, u.name)  AGAINST(\'' . $item['term']  . '\') > 0');
	            $qb->addOrderBy('MATCH(u.acronym, u.name)  AGAINST(\'' . $item['term']  . '\')', 'DESC');
	            $data[$i]['suggest'] = $qb->getQuery()->getSingleScalarResult();
	        }
	        
	        $qb = $this->getEntityManager()->getRepository(Agency::getClass())->createQueryBuilder('u');
	        $qb->where('u.active = true AND u.id > 0');
	        $qb->orderBy('u.acronym', 'ASC');
	        $result = $qb->getQuery()->getResult();
	        $options = ['' => 'Selecione um Órgão'];
	        foreach ($result as $item) {
	            $options[$item->id] = $item . ' (' . $item->id . ')';
	        }
	        
	        $form = new ImportProcessForm($data, $options, new Action($this, 'edit', ['key' => $key]), new Action($this));
	        $helper = $this->createHelperCrud();
	        if ( $helper->update($form, $entity) ) {
	            $entity = $helper->getEntity();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Orgão <em>#' . $entity->code . ' ' . $entity->acronym .  '</em> alterado com sucesso!', Alert::Success));
	            $this->forward('/');
	        }
	    } catch ( InvalidRequestDataException $e ) {
	        $form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch ( \Exception $e ) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error));
	        $this->forward('/');
	    }
	    return new Layout($form);
	}
	
	public function downloadAction() {
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