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
use Gesfrota\View\ImportTransformForm;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\View\FleetEquipmentForm;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\View\FleetVehicleForm;
use Gesfrota\View\Widget\BuilderForm;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\ResultCenter;

class ImportController extends AbstractController {
	
	public function indexAction() {
		try {
		    $filter = new Action($this);
		    $upload = new Action($this, 'upload');
		    $process = new Action($this, 'pre-process');
		    $transfom = new Action($this, 'transform');
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
	        $form = new ImportUploadForm(new Action($this, 'upload'), new Action($this));
	        $helper = $this->createHelperCrud();
	        if ( $helper->create($form) ){
	            $entity = $helper->getEntity();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Importação <em>#' . $entity->code . ' ' . $entity->description . '</em> realizada com sucesso!', Alert::Success));
	            $this->forward('/pre-process/' . $entity->id);
	        }
	    } catch ( InvalidRequestDataException $e ){
	        $form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch ( \Exception $e ) {
	        $form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
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
	        
	        $form = new ImportPreProcessForm(new Action($this, 'pre-process', ['key' => $key]), new Action($this), $entity, $data, $options);
	        $form->extract($entity);
	        if ( $this->request->isPost() ) {
	            $form->bind($this->request->getPost());
	            if ( ! $form->valid() ) {
	                throw new InvalidRequestDataException();
	            }
	            $form->hydrate($entity, $this->getEntityManager());
	            $this->getEntityManager()->flush();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Pré-processamento de <em>#' . $entity->code . ' ' . $entity->description .  '</em> realizado com sucesso!', Alert::Success));
	            $this->forward('/transform/' . $entity->id);
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
	            throw new NotFoundEntityException('Não é possível transformar a Importação. Importação <em>#' . $key . '</em> não encontrada.');
	        }
	        if ($entity->getStatus() == Import::FINISHED) {
	            throw new NotFoundEntityException('Não é possível transformar a Importação. Importação <em>#' . $key . '</em> já foi encerrada.');
	        }
	        
	        $submit = new Action($this, 'transform', ['key' => $key]);
	        $cancel = new Action($this);
	        $transform = new Action($this, 'transform-item');
	        $dismiss = new Action($this, 'dismiss-item');
	        
	        $form = new ImportTransformForm($submit, $cancel, $transform, $dismiss, $entity, $this->getAgencyActive());
	        
	        $query = $this->getEntityManager()->getRepository(ImportItem::getClass())->createQueryBuilder('u');
	        $query->where('u.import = :key ');
	        $query->setParameter('key', $entity);
	        $query->orderBy('u.status');
	        
	        $ds = new EntityDatasource($query, ['limit' => 20]);
	        $ds->setPage($this->request->getQuery('page'));
	        $form->setDatasource($ds);
	        
	        $form->setAlert($this->getAlert());
	    } catch ( InvalidRequestDataException $e ) {
	        $form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch ( \Exception $e ) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error));
	        $this->forward('/');
	    }
	    return new Layout($form);
	}
	
	public function transformItemAction() {
	    try {
    	    $key = $this->request->getQuery('key');
    	    $entity = $this->getEntityManager()->find(ImportItem::getClass(), $key);
    	    if (! $entity instanceof ImportItem) {
    	        throw new NotFoundEntityException('Não foi possível transformar Item de Importação. Item de Importação <em>#' . $key . '</em> não encontrado.');
    	    }
    	    $helper = new Crud($this->getEntityManager(), FleetItem::getClass(), $this);
    	    $item = $entity->getStatus() ? $entity->getReference() : $entity->toTransform();
    	    $form = $this->createForm($item, new Action($this, 'transform-item', ['key' => $entity->id]), new Action($this, 'transform', ['key' => $entity->getImport()->id]));
    	    
    	    if ( $item->getId() > 0 ) {
    	        if ( $helper->update($form, $item) ) {
    	            $this->setAlert(new Alert('<strong>Ok! </strong>' . $item->fleetType . ' <em>#' . $item->code . ' ' . $item->description .  '</em> alterado com sucesso!', Alert::Success));
    	            $this->forward('/transform/' . $entity->getImport()->id);
    	        }
    	    } else {
    	        if ( $helper->create($form, $item) ) {
    	            $this->setAlert(new Alert('<strong>Ok! </strong>' . $item->fleetType . ' <em>#' . $item->code . ' ' . $item->description . '</em> criado com sucesso!', Alert::Success));
    	            $this->forward('/transform/' . $entity->getImport()->id);
    	        }
    	    }
	    } catch (NotFoundEntityException $e) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error));
	        $this->redirect($this->request->getHeader('Referer'));
	    }
	    return new Layout($form);
	}
	
	public function dismissItemAction() {
	    try {
	        $key = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(ImportItem::getClass(), $key);
	        if (! $entity instanceof ImportItem) {
	            throw new NotFoundEntityException('Não foi possível rejeitar Item de Importação. Item de Importação <em>#' . $key . '</em> não encontrado.');
	        }
	        $entity->setReference(null);
	        $this->getEntityManager()->flush();
	        $this->setAlert(new Alert('<strong>Ok! </strong>Item de Importação <em>#' . $entity->code . ' ' . $entity->alias . '</em> rejeitado com sucesso!', Alert::Success));
            $this->forward('/transform/' . $entity->getImport()->id);
	    } catch (NotFoundEntityException $e) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error));
	        $this->redirect($this->request->getHeader('Referer'));
	    }
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
	
	public function removeAction() {
	    try {
	        $id = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(Import::getClass(), $id);
	        if (! $entity instanceof Import) {
	            throw new NotFoundEntityException('Não foi possível excluir Importação. Importação <em>#' . $id . '</em> não encontrada.');
	        }
	        $helper = $this->createHelperCrud();
	        $helper->delete($entity);
	        $this->setAlert(new Alert('<strong>Ok! </strong>Importação <em>#' . $id . ' ' . $entity->description . '</em> excluída com sucesso!', Alert::Success));
	    } catch ( NotFoundEntityException $e ) {
	        $this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch ( \Exception $e ) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	    }
	    $this->forward('/');
	}
	
	/**
	 * @param FleetItem $item
	 * @param Action $submit
	 * @return BuilderForm
	 */
	private function createForm ( FleetItem $item, Action $submit, Action $cancel ) {
	    $seek['agency'] =  new Action(FleetController::getClass(), 'seekAgency');
	    $find['agency'] = new Action(FleetController::getClass(), 'searchAgency');
	    $showAgencies = $this->getAgencyActive()->isGovernment();
	    
	    $optResultCenter = [];
	    $criteria = ['active' => true, 'agency' => $item->getResponsibleUnit()];
	    $rs = $this->getEntityManager()->getRepository(ResultCenter::getClass())->findBy($criteria);
	    foreach ($rs as $result) {
	        $optResultCenter[$result->id] = $result->description;
	    }
	    
	    switch (get_class($item)) {
	        case Vehicle::getClass():
	            $seek['vehicle-plate'] =  new Action(FleetController::getClass(), 'seekVehiclePlate');
	            $seek['vehicle'] = new Action(FleetController::getClass(), 'seekVehicle');
	            $seek['owner'] = new Action(FleetController::getClass(), 'seekOwner');
	            $find['vehicle'] = new Action(FleetController::getClass(), 'searchVehicle');
	            $find['owner'] = new Action(FleetController::getClass(), 'searchOwner');
	            $newOwnerPerson = new Action(FleetController::getClass(), 'newOwnerPerson');
	            $newOwnerCompany = new Action(FleetController::getClass(), 'newOwnerCompany');
	            return new FleetVehicleForm($submit, $seek['vehicle-plate'], $seek['vehicle'], $find['vehicle'], $seek['agency'], $find['agency'], $seek['owner'], $find['owner'], $newOwnerPerson, $newOwnerCompany, $cancel, $optResultCenter, $showAgencies);
	            break;
	            
	        case Equipment::getClass():
	            return new FleetEquipmentForm($submit, $cancel, $seek['agency'], $find['agency'], $optResultCenter, $showAgencies);
	            break;
	            
	        default:
	            throw new \InvalidArgumentException('Form not implements for '.$item);
	            break;
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