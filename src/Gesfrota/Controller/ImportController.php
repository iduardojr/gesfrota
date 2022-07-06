<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Controller\Helper\SearchAgency;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Import;
use Gesfrota\Model\Domain\ImportItem;
use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\View\FleetEquipmentForm;
use Gesfrota\View\FleetVehicleForm;
use Gesfrota\View\ImportList;
use Gesfrota\View\ImportPreProcessForm;
use Gesfrota\View\ImportUploadForm;
use Gesfrota\View\Layout;
use Gesfrota\View\Widget\BuilderForm;
use Gesfrota\View\Widget\EntityDatasource;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class ImportController extends AbstractController {
	
    use SearchAgency;
    
	public function indexAction() {
		try {
		    $filter = new Action($this);
		    $upload = new Action($this, 'new');
		    $process = new Action($this, 'pre-process');
		    $download = new Action($this, 'download');
		    $remove = new Action($this, 'remove');
		    
		    $showAgencies = $this->getShowAgencies();
		    
		    $list = new ImportList($filter, $upload, $process, $download, $remove, $showAgencies);
		    
		    
		    $helper = $this->createHelperCrud();
		    $query = $this->getEntityManager()->getRepository(Import::getClass())->createQueryBuilder('u');
		    
		    $helper->read($list, $query, ['limit' => 12, 'order' => 'DESC', 'processQuery' => function( QueryBuilder $query, array $data ) {
		        if (!empty($data['agency'])) {
		            $query->andWhere('u.agency = :agency');
		            $query->setParameter('agency', $data['agency']);
		        }
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
		    }]);
		    
		    $list->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
		    $list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}
	
	public function newAction() {
	    try {
	        $agency = $this->getAgencySelected();
	        $submit = new Action($this, 'new');
	        $cancel = new Action($this);
	        $seek 	= new Action($this, 'seek-agency');
	        $search = new Action($this, 'search-agency');
	        $showAgencies = $this->getAgencyActive()->isGovernment();
	        
	        $form = new ImportUploadForm($submit, $cancel, $seek, $search, $showAgencies);
	        $helper = $this->createHelperCrud();
	        
	        if ( $helper->create($form, new Import($agency)) ){
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
	            throw new NotFoundEntityException('Não é possível transformar a Importação. Importação <em>#' . $key . '</em> não encontrada.');
	        }
	        if ($entity->getStatus() < Import::PREPROCESSED) {
	            throw new \ErrorException('Não é possível transformar a Importação. Importação <em>#' . $key . '</em> não foi processada.');
	        }
	        $submit = new Action($this, 'pre-process', ['key' => $key]);
	        $cancel = new Action($this);
	        $transform = new Action($this, 'transform-item');
	        $dismiss = new Action($this, 'dismiss-item');
	        
	        $form = new ImportPreProcessForm($submit, $cancel, $transform, $dismiss, $entity, $this->getAgencyActive());
	        
	        $query = $this->getEntityManager()->getRepository(ImportItem::getClass())->createQueryBuilder('u');
	        $query->where('u.import = :key ');
	        $query->setParameter('key', $entity);
	        $query->orderBy('u.status', $entity->getStatus() == Import::FINISHED ? 'DESC' : 'ASC');
	        
	        $ds = new EntityDatasource($query, ['limit' => 20]);
	        $ds->setPage($this->request->getQuery('page'));
	        $form->setDatasource($ds);
	        
	        $form->extract($entity);
	        if ( $this->request->isPost() ) {
	            if ($entity->getStatus() == Import::FINISHED) {
	                throw new \ErrorException('Não é possível finalizar a Importação. Importação <em>#' . $key . '</em> já foi encerrada.');
	            }
	            $form->bind($this->request->getPost());
	            if ( ! $form->valid() ) {
	                throw new InvalidRequestDataException();
	            }
	            $form->hydrate($entity, $this->getEntityManager());
	            $this->getEntityManager()->flush();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Importação <em>#' . $entity->code . ' ' . $entity->description .  '</em> finalizada com sucesso!', Alert::Success));
	            $this->forward('/');
	        }
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
    	    if (! $entity->getStatus() ) {
        	    if ( $entity->isVehicle() ) {
        	        $rep = $this->getEntityManager()->getRepository(Vehicle::getClass());
        	        $criteria = ['plate' => $entity->getData()[1]];
        	    } else {
        	        $rep = $this->getEntityManager()->getRepository(Equipment::getClass());
        	        $criteria = ['assetCode' => $entity->getData()[6], 'responsibleUnit' => $entity->getImport()->getAgency()];
        	    }
        	    if ($item = $rep->findOneBy($criteria) ) {
        	        $entity->setReference($item);
        	        $this->getEntityManager()->flush();
        	        $this->setAlert(new Alert('<strong>Ops! </strong>Item de Importação <em>#' . $entity->alias . ' </em> já foi transformado!'));
        	    } else {
        	        $item = $entity->toTransform();
        	    }
    	    } else {
    	        $item = $entity->getReference();
    	    }
    	    $form = $this->createForm($item, new Action($this, 'transform-item', ['key' => $entity->id]), new Action($this, 'pre-process', ['key' => $entity->getImport()->id]));
    	    $form->setAlert($this->getAlert());
    	    if ( $item->getId() > 0 ) {
    	        if ( $helper->update($form, $item) ) {
    	            $this->setAlert(new Alert('<strong>Ok! </strong>' . $item->fleetType . ' <em>#' . $item->code . ' ' . $item->description .  '</em> alterado com sucesso!', Alert::Success));
    	            $this->forward('/pre-process/' . $entity->getImport()->id);
    	        }
    	    } else {
    	        if ( $helper->create($form, $item) ) {
    	            $this->setAlert(new Alert('<strong>Ok! </strong>' . $item->fleetType . ' <em>#' . $item->code . ' ' . $item->description . '</em> criado com sucesso!', Alert::Success));
    	            $this->forward('/pre-process/' . $entity->getImport()->id);
    	        }
    	    }
	    } catch (NotFoundEntityException $e) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error));
	        $this->redirect($this->request->getHeader('Referer'));
	    } catch ( \Exception $e) {
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
	        if ( $entity->getStatus() === null ) {
	            if ( $entity->isVehicle() ) {
	                $rep = $this->getEntityManager()->getRepository(Vehicle::getClass());
	                $criteria = ['plate' => $entity->getData()[1]];
	            } else {
	                $rep = $this->getEntityManager()->getRepository(Equipment::getClass());
	                $criteria = ['assetCode' => $entity->getData()[6], 'responsibleUnit' => $entity->getImport()->getAgency()];
	            }
	            if ($item = $rep->findOneBy($criteria) ) {
	                $entity->setReference($item);
	                $this->getEntityManager()->flush();
	            } 
	            
	        } 
	        if ( $entity->getReference() ) {
	            throw new \DomainException('Não foi possível rejeitar Item de Importação. Item de Importação <em>#' . $entity->alias . '</em> já foi importado.');
	        }
	        $entity->setReference(null);
	        $this->getEntityManager()->flush();
	        $this->setAlert(new Alert('<strong>Ok! </strong>Item de Importação <em>#' . $entity->alias . '</em> rejeitado com sucesso!', Alert::Success));
            $this->forward('/pre-process/' . $entity->getImport()->id);
	    } catch (\Exception $e) {
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
	
	/**
	 * @return Agency
	 */
	protected function getAgencySelected() {
	    if ($this->session->agency_selected > 0) {
	        $selected = $this->getEntityManager()->find(Agency::getClass(), $this->session->agency_selected);
	        if ($selected) {
	            return $selected;
	        }
	    }
	    return $this->getAgencyActive();
	}
	
	/**
	 * @param Agency $agency
	 */
	protected function setAgencySelected(Agency $agency = null) {
	    $this->session->agency_selected = $agency ? $agency->getId() : null;
	}
	
}
?>