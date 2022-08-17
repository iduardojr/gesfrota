<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\ImportFleet;
use Gesfrota\Model\Domain\ImportSupply;
use Gesfrota\Model\Domain\ImportTransaction;
use Gesfrota\Model\Domain\ImportTransactionFuel;
use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Util\Format;
use Gesfrota\View\FleetEquipmentForm;
use Gesfrota\View\FleetVehicleForm;
use Gesfrota\View\ImportTransactionFuelPreProcessForm;
use Gesfrota\View\ImportTransactionFuelUploadForm;
use Gesfrota\View\ImportTransactionItemsList;
use Gesfrota\View\ImportTransactionList;
use Gesfrota\View\Layout;
use Gesfrota\View\Widget\BuilderForm;
use Gesfrota\View\Widget\EntityDatasource;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class ImportTransactionController extends AbstractController {
	
	public function indexAction() {
		try {
		    $filter = new Action($this);
		    $upload = new Action($this, 'new');
		    $preProcess = new Action($this, 'pre-process');
		    $listItems = new Action($this, 'list-items');
		    $download = new Action($this, 'download');
		    $remove = new Action($this, 'remove');
		    
		    $providers = $this->getOptionsProviders();
		    
		    $list = new ImportTransactionList($filter, $upload, $preProcess, $listItems, $download, $remove, $providers);
		    
		    $helper = $this->createHelperCrud();
		    $query = $this->getEntityManager()->getRepository(ImportTransaction::getClass())->createQueryBuilder('u');
		    
		    $helper->read($list, $query, ['limit' => 20, 'order' => 'DESC', 'processQuery' => function( QueryBuilder $query, array $data ) {
		        if (!empty($data['provider'])) {
		            $query->andWhere('u.serviceProvider = :provider');
		            $query->setParameter('provider', $data['provider']);
		        }
		        if ( !empty($data['desc']) ) {
		            $query->andWhere('u.description LIKE :desc');
		            $query->setParameter('desc', '%' . $data['desc'] . '%');
		        }
		        if ( !empty($data['date-initial']) ) {
		            $query->andWhere('u.dateInitial >= :initial');
		            $query->setParameter('initial', $data['date-initial']);
		        }
		        if ( !empty($data['date-final']) ) {
		            $query->andWhere('u.dateFinal <= :final');
		            $query->setParameter('final', $data['date-final']);
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
	        set_time_limit(0);
	        ini_set('memory_limit', -1);
	        $submit = new Action($this, 'new');
	        $cancel = new Action($this);
	        $providers = $this->getOptionsProviders(false);
	        
	        $entity = new ImportSupply();
	        $form = new ImportTransactionFuelUploadForm($submit, $cancel,  $providers);
	        $form->extract($entity);
	        $this->getEntityManager()->beginTransaction();
	        if ( $this->request->isPost() ) {
	            $form->bind($this->request->getPost());
	            if ( ! $form->valid() ) {
	                throw new InvalidRequestDataException();
	            }
	            $form->hydrate($entity, $this->getEntityManager());
	            $this->getEntityManager()->commit();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Importação <em>#' . $entity->code . ' ' . $entity->description . '</em> realizada com sucesso!', Alert::Success));
	            $this->forward('/pre-process/' . $entity->id);
	        }
	    } catch ( InvalidRequestDataException $e ){
	        $form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch ( \Exception $e ) {
	        $this->getEntityManager()->rollback();
	        $form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	    }
	    return new Layout($form);
	}
	
	public function preProcessAction() {
	    try {
	        $key = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(ImportTransaction::getClass(), $key);
	        if (! $entity instanceof ImportTransaction) {
	            throw new NotFoundEntityException('Não é possível transformar a Importação. Importação <em>#' . $key . '</em> não encontrada.');
	        }
	        $submit = new Action($this, 'pre-process', ['key' => $key]);
	        $remove = new Action($this, 'remove', ['key' => $key]);
	        $download = new Action($this,'download', ['key' => $key]);
	        $cancel =  new Action($this);
	        $costCenters = $this->getOptionsCostCenters($entity);
	        $optAgencies = $this->getOptionsAgencies();
	        
	        $form = new ImportTransactionFuelPreProcessForm($submit, $remove, $download, $cancel, $entity, $costCenters, $optAgencies);
	        
	        if ( $this->request->isPost() ) {
	            if ( $entity->getFinished() ) {
	                throw new \ErrorException('Não é possível finalizar a Importação. Importação <em>#' . $key . '</em> já foi encerrada.');
	            }
	            $form->bind($this->request->getPost());
	            if ( ! $form->valid() ) {
	                throw new InvalidRequestDataException();
	            }
	            foreach ($this->request->getPost() as $key => $value) {
	                $query = $this->getEntityManager()->createQueryBuilder();
	                $query->update(ImportTransactionFuel::class, 'u');
	                $query->set('u.transactionAgency', $value);
	                $query->where('u.transactionCostCenter = :costCenter AND u.transactionImport = :import');
	                $query->setParameter('costCenter', $costCenters[$key]);
	                $query->setParameter('import', $entity);
	                $query->getQuery()->execute();
	            }
	            $entity->toFinish();
	            $this->getEntityManager()->flush();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Importação <em>#' . $entity->code . ' ' . $entity->description .  '</em> finalizada com sucesso!', Alert::Success));
	            $this->forward('/');
	        } elseif ( $entity->getFinished() ){
	            $this->setAlert(new Alert('<strong>Ops! </strong>Importação finalizada em <em>' . $entity->getFinishedAt()->format('d/m/Y H:i:s') . '</em>'));
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
	
	public function listItemsAction() {
	    try {
	        $key = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(ImportTransaction::getClass(), $key);
	        if (! $entity instanceof ImportTransaction) {
	            throw new NotFoundEntityException('Não é possível listar a Importação. Importação <em>#' . $key . '</em> não encontrada.');
	        }
	        $submit = new Action($this, 'list-items', ['key' => $key]);
	        $remove = new Action($this, 'remove', ['key' => $key]);
	        $download = new Action($this,'download', ['key' => $key]);
	        $cancel =  new Action($this);
	        
	        $form = new ImportTransactionItemsList($submit, $remove, $download, $cancel, $entity);
	        
	        $query = $this->getEntityManager()->getRepository(ImportTransactionFuel::class)->createQueryBuilder('u');
	        $query->where('u.transactionImport = :key ');
	        $query->setParameter('key', $entity);
	        
	        $ds = new EntityDatasource($query, ['limit' => 15, 'identify' => 'transactionId']);
	        $ds->setPage($this->request->getQuery('page'));
	        $form->setDatasource($ds);
	        if ( $entity->getFinished() ){
	            $this->setAlert(new Alert('<strong>Ops! </strong>Importação finalizada em <em>' . $entity->getFinishedAt()->format('d/m/Y H:i:s') . '</em>'));
	        }
	        $form->setAlert($this->getAlert());
	    } catch ( \Exception $e ) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error));
	        $this->forward('/');
	    }
	    return new Layout($form);
	}
	
	
	public function downloadAction() {
	    try {
	        $key = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(ImportTransaction::getClass(), $key);
	        if (! $entity instanceof ImportTransaction) {
	            throw new NotFoundEntityException('Não foi possível baixar o Arquivo Importado. Importação <em>#' . $key . '</em> não encontrada.');
	        }
	        $this->redirect(ImportFleet::DIR . $entity->getFileName());
	    } catch ( NotFoundEntityException $e ) {
	        $this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	        $this->forward('/');
	    }
	}
	
	public function removeAction() {
	    try {
	        set_time_limit(0);
	        $id = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(ImportTransaction::getClass(), $id);
	        if (! $entity instanceof ImportTransaction) {
	            throw new NotFoundEntityException('Não foi possível excluir Importação. Importação <em>#' . $id . '</em> não encontrada.');
	        }
	        $helper = $this->createHelperCrud();
	        $helper->delete($entity);
	        $this->setAlert(new Alert('<strong>Ok! </strong>Importação <em>#' . Format::code($id, 3) . ' ' . $entity->description . '</em> excluída com sucesso!', Alert::Success));
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
	    return new Crud($this->getEntityManager(), ImportTransaction::getClass(), $this);
	}
	
	/**
	 * 
	 * @param boolean $includeAll
	 * @return string[]
	 */
	private function getOptionsProviders($includeAll = true) {
	    $query = $this->getEntityManager()->getRepository(ServiceProvider::getClass())->createQueryBuilder('u');
	    $result = $query->getQuery()->getResult();
	    $options = $includeAll ? ['' => 'Todos'] : [];
	    foreach($result as $item) {
	        $options[$item->id] = (string) $item;
	    }
	    return $options;
	}
	
	/**
	 *
	 * @param boolean $includeAll
	 * @return string[]
	 */
	private function getOptionsAgencies($includeAll = true) {
	    $query = $this->getEntityManager()->getRepository(Agency::getClass())->createQueryBuilder('u');
	    $query->where('u.id > 0');
	    $query->orderBy('u.acronym');
	    $result = $query->getQuery()->getResult();
	    $options = $includeAll ? ['' => 'Escolha um órgão'] : [];
	    foreach($result as $item) {
	        $item instanceof Agency;
	        $options[$item->id] = $item->getAcronym() . ' (' . $item->getCode() . ')';
	    }
	    return $options;
	}
	
	/**
	 * @param ImportTransaction $import
	 * @return string[]
	 */
	private function getOptionsCostCenters(ImportTransaction $import) {
	    $query = $this->getEntityManager()->getRepository(ImportTransactionFuel::class)->createQueryBuilder('u');
	    $query->select('DISTINCT u.transactionCostCenter');
	    $query->where('u.transactionImport = :import');
	    $query->setParameter('import', $import);
	    $result = $query->getQuery()->getSingleColumnResult();
	    $options = [];
	    foreach($result as $item) {
	        $options[md5($item)] = $item;
	    }
	    return $options;
	}
}
?>