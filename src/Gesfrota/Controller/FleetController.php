<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Controller\Helper\SearchAgency;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Disposal;
use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Owner;
use Gesfrota\Model\Domain\OwnerCompany;
use Gesfrota\Model\Domain\OwnerPerson;
use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\Model\Domain\ServiceCard;
use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\VehicleModel;
use Gesfrota\View\FleetEquipmentForm;
use Gesfrota\View\FleetList;
use Gesfrota\View\FleetOwnerForm;
use Gesfrota\View\FleetVehicleForm;
use Gesfrota\View\Layout;
use Gesfrota\View\OwnerTable;
use Gesfrota\View\ServiceCardForm;
use Gesfrota\View\VehicleModelTable;
use Gesfrota\View\Widget\BuilderForm;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\Widget\PanelQuery;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class FleetController extends AbstractController {
	
	use SearchAgency;
	
	public function indexAction() {
		try {
			$this->session->cards = null;
			$this->setAgencySelected(null);
			
			$filter = new Action($this);
			$new1 	= new Action($this, 'newVehicle');
			$new2 	= new Action($this, 'newEquipment');
			$edit 	= new Action($this, 'edit');
			$active = new Action($this, 'active');
			$search = new Action($this, 'searchVehiclePlate');
			$import = new Action(ImportController::getClass());
			$transfer = new Action($this, 'transferVehicle');
			
			$showAgencies = $this->getShowAgencies();
			
			$helper = $this->createHelperCrud();
			
			$storage = $helper->getStorage();
			if ($this->request->getPost('agency')) {
				$agency = $this->getEntityManager()->find(Agency::getClass(), $this->request->getPost('agency'));
			} elseif ( isset($storage['data']['filter']['agency']) && ! empty($storage['data']['filter']['agency']) ) {
				$agency = $this->getEntityManager()->find(Agency::getClass(), $storage['data']['filter']['agency']);
			} else {
				$agency = $this->getAgencyActive();
			}
			$optResultCenter = $agency->getResultCentersActived();
			
			$query = $this->getEntityManager()->getRepository(FleetItem::getClass())->createQueryBuilder('u');
			
			if (! $showAgencies ) {
				$query->join('u.responsibleUnit', 'r');
				$query->andWhere('r.id = :unit');
				$query->setParameter('unit', $this->getAgencyActive()->getId());
			}
			
			$q1 = $this->getEntityManager()
				->getRepository(DisposalItem::getClass())
				->createQueryBuilder('di');
			$q1->select('IDENTITY(di.asset)');
			$q1->join('di.disposal', 'd');
			$q1->where('d.status NOT IN (:disposal)');
			$query->andWhere('u.id NOT IN (' . $q1->getDQL() . ')');
			$query->setParameter('disposal', [Disposal::DRAFTED, Disposal::DECLINED]);
			
			$list = new FleetList($filter, $new1, $new2, $edit, $active, $search, $import, $transfer, $optResultCenter, $showAgencies);
		
			$helper->read($list, $query, array('limit' => 20, 'processQuery' => function( QueryBuilder $query, array $data ) {
			    if ( !empty($data['type']) ) {
			        $query->andWhere('u INSTANCE OF ' . ( $data['type'] == 'V' ? Vehicle::getClass() : Equipment::getClass()));
			    }
				if (!empty($data['agency'])) {
					$query->andWhere('u.responsibleUnit = :agency');
					$query->setParameter('agency', $data['agency']);
				}
				if (!empty($data['results-center'])) {
					$query->andWhere(':rc MEMBER OF u.resultCenters');
					$query->setParameter('rc', $data['results-center']);
				}
				if ( !empty($data['description']) ) {
						$q1 = $this->getEntityManager()->getRepository(Vehicle::getClass())->createQueryBuilder('v');
						$q1->select('v.id');
						$q1->join('v.model', 'm1');
						$q1->join('m1.maker', 'm2');
						$q1->where('m1.name LIKE :query');
						$q1->orWhere('m2.name LIKE :query');
						$q1->orWhere("CONCAT(m2.name, ' ', m1.name) LIKE :query");
						$q1->orWhere("v.plate LIKE :query");
						
						$q2 = $this->getEntityManager()->getRepository(Equipment::getClass())->createQueryBuilder('e');
						$q2->select('e.id');
						$q2->where('e.description LIKE :query');
						$q2->orWhere('e.serialNumber LIKE :query');
						
						$query->andWhere('u.id IN (' . $q1->getDQL() . ') OR u.id IN (' . $q2->getDQL(). ')');
						$query->setParameter('query', '%' . $data['description'] . '%');
				}
				
				if ( !empty($data['engine']) ) {
					$query->andWhere('u.engine IN (:engine)');
					$query->setParameter('engine', $data['engine']);
				}
				if ( !empty($data['fleet']) ) {
					$query->andWhere('u.fleet IN (:fleet)');
					$query->setParameter('fleet', $data['fleet']);
				}
				if ( !empty($data['status']) ) {
					$query->andWhere('u.active = :status');
					$query->setParameter('status', $data['status'] > 0);
				}
			}));
			$list->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
			$list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}
	
	public function newVehicleAction() {
		try {
	    	$form = $this->createForm(Vehicle::getClass(), new Action($this, 'newVehicle'));
		
			$helper = $this->createHelperCrud();
			if ($this->request->isPost()) {
				$criteria = ['plate' => $this->request->getPost('plate')];
				$entity = $this->getEntityManager()->getRepository(Vehicle::getClass())->findOneBy($criteria);
				if ( $entity instanceof Vehicle ) {
					throw new \DomainException('Veículo <em>' . $entity->getPlate() . ' ' . $entity->getDescription() . '</em> já está registrado em '. $entity->getResponsibleUnit()->getAcronym());
				}
			}
			$agency = null;
			if (! $this->getAgencyActive()->isGovernment()) {
				$agency = $this->getAgencyActive();
			}
			if ( $helper->create($form, new Vehicle($agency)) ) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>' . $entity->fleetType . ' <em>#' . $entity->code . ' ' . $entity->description . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
		    $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		    $this->forward('/');
		}
		return new Layout($form);
	}
	
	public function newEquipmentAction() {
		try {
		    $form = $this->createForm(Equipment::getClass(), new Action($this, 'newEquipment'));
			$helper = $this->createHelperCrud();
			
			$agency = null;
			if (! $this->getAgencyActive()->isGovernment()) {
			    $agency = $this->getAgencyActive();
			}
			
			if ($this->request->isPost()) {
			    $criteria = ['serialNumber' => $this->request->getPost('serial-number')];
			    $entity = $this->getEntityManager()->getRepository(Equipment::getClass())->findOneBy($criteria);
			    if ( $entity instanceof Equipment ) {
			        throw new \DomainException('Equipamento <em>' . $entity->getAssetCode() . ' ' . $entity->getDescription() . '</em> já está registrado em '. $entity->getResponsibleUnit()->getAcronym());
			    }
		    }
		 
			if ( $helper->create($form, new Equipment($agency)) ) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>' . $entity->fleetType . ' <em>#' . $entity->code . ' ' . $entity->description . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
		    $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		    $this->forward('/');
		}
		return new Layout($form);
	}
	
	
	public function editAction() {
		try {
			$id = (int) $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(FleetItem::getClass(), $id);
			if ( ! $entity instanceof FleetItem ) {
			    throw new NotFoundEntityException('Não foi possível editar o Item da Frota. Item da Frota <em>#' . $id . '</em> não encontrado.');
			}
			$this->setAgencySelected($entity->getResponsibleUnit());
			$form = $this->createForm($entity, new Action($this, 'edit', array('key' => $id)));
			
			$helper = $this->createHelperCrud();
			if ( $helper->update($form, $entity) ) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>' . $entity->fleetType . ' <em>#' . $entity->code . ' ' . $entity->description .  '</em> alterado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( InvalidRequestDataException $e ) {
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function activeAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar o Item da Frota. Item da Frota <em>#' . $id . '</em> não encontrado.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Item da Frota <em>#' . $entity->code . ' ' . $entity->description . '</em> ' . ( $entity->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function seekOwnerAction() {
		try {
			$data['owner-id'] = '';
			$data['owner-name'] = '';
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(Owner::getClass())->findOneBy(['id' => $id, 'active' => true]);
			if ( ! $entity instanceof Owner ) {
				throw new NotFoundEntityException('Proprietário <em>#' . $id . '</em> não encontrado.');
			}
			$data['owner-id'] = $entity->getCode();
			$data['owner-name'] = $entity->getName();
			$data['flash-message'] = null;
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function searchOwnerAction() {
		try {
			$query = $this->getEntityManager()->getRepository(Owner::getClass())->createQueryBuilder('u');
			$query->distinct(true);
			$params = $this->request->getQuery();
			$query->andWhere('u.active = true');
			if ( $params['query'] ) {
				$query->andWhere('u.name LIKE :query');
				$query->orWhere('u.nif LIKE :query');
				$query->setParameter('query', '%' . $params['query'] . '%');
			}
			
			$datasource = new EntityDatasource($query);
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new OwnerTable(new Action($this,'searchOwner', $params));
			$table->setDataSource($datasource);
			$modal = $this->createForm(Vehicle::getClass(), new Action($this))->getModalOwner();
			$widget = new PanelQuery($table, new Action($this,'searchOwner', $params), $params['query'], $modal);
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function newOwnerPersonAction() {
		$form = new FleetOwnerForm(OwnerPerson::getClass(), new Action($this, 'newOwnerPerson'));
		if ( $this->request->isPost() ) {
			try {
				$form->bind($this->request->getPost());
				if ( ! $form->valid() ) {
					throw new InvalidRequestDataException();
				}
				$data = $form->getData();
				$owner = new OwnerPerson();
				$owner->setName($data['name']);
				$owner->setNif($data['nif']);
				$this->em->persist($owner);
				$this->em->flush();
				$data['owner-id'] = $owner->getCode();
				$data['owner-name'] = $owner->getName();
				$data['alert-message'] = null;
			} catch ( \Exception $e ) {
				$data['alert-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
			}
			return new JsonView($data, false);
		}
		return new Layout($form, null);
	}
	
	public function newOwnerCompanyAction() {
		$form = new FleetOwnerForm(OwnerCompany::getClass(), new Action($this, 'newOwnerCompany'));
		if ( $this->request->isPost() ) {
			try {
				$form->bind($this->request->getPost());
				if ( ! $form->valid() ) {
					throw new InvalidRequestDataException();
				}
				$data = $form->getData();
				$owner = new OwnerCompany();
				$owner->setName($data['name']);
				$owner->setNif($data['nif']);
				$this->em->persist($owner);
				$this->em->flush();
				$data['owner-id'] = $owner->getCode();
				$data['owner-name'] = $owner->getName();
				$data['alert-message'] = null;
			} catch ( \Exception $e ) {
				$data['alert-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
			}
			return new JsonView($data, false);
		}
		return new Layout($form, null);
	}
	
	public function seekVehiclePlateAction() {
		try {
			$plate = strtoupper($this->request->getQuery('query'));
			$data['plate'] = $plate;
			$entity = $this->getEntityManager()->getRepository(Vehicle::getClass())->findOneBy(['plate' => $plate]);
			$data['flash-message'] = null;
			if ( $entity instanceof Vehicle ) {
				throw new \DomainException('Veículo <em>' . $entity->getPlate() . ' ' . $entity->getDescription() . '</em> já está registrado em '. $entity->getResponsibleUnit()->getAcronym());
			}
		} catch ( \DomainException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . get_class($e).$e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function searchVehiclePlateAction() {
		try {
			$plate = strtoupper($this->request->getQuery('query'));
			$entity = $this->getEntityManager()->getRepository(Vehicle::getClass())->findOneBy(['plate' => $plate]);
			$data['vehicle-plate'] = $entity->getPlate();
			$data['vehicle-description'] = $entity->getDescription();
			$data['responsible-unit-description'] = $entity->getResponsibleUnit()->getName();
			$data['flash-message'] = null;
		} catch ( \DomainException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . get_class($e).$e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function seekVehicleAction() {
		try {
			$data['vehicle-model-id'] = '';
			$data['vehicle-model-fipe'] = '';
			$data['vehicle-model-name'] = '';
			$data['vehicle-maker-id'] = '';
			$data['vehicle-maker-name'] = '';
			$data['vehicle-family-id'] = '';
			$data['vehicle-family-name'] = '';
			$code = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(VehicleModel::getClass())->findOneBy(['fipe' => $code]);
			if ( ! $entity instanceof VehicleModel ) {
				throw new NotFoundEntityException('Modelo de Veículo <em>#' . $code . '</em> não encontrado.');
			}
			$data['vehicle-model-id'] = $entity->getId();
			$data['vehicle-model-fipe'] = $entity->getCode();
			$data['vehicle-model-name'] = $entity->getName();
			$data['vehicle-maker-id'] = $entity->getMaker()->getCode();
			$data['vehicle-maker-name'] = $entity->getMaker()->getName();
			$data['vehicle-family-id'] = $entity->getFamily()->getCode();
			$data['vehicle-family-name'] = $entity->getFamily()->getName();
			$data['flash-message'] = null;
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function searchVehicleAction() {
		try {
			$query = $this->getEntityManager()->getRepository(VehicleModel::getClass())->createQueryBuilder('u');
			$query->distinct(true);
			$params = $this->request->getQuery();
			$ds = new EntityDatasource($query);
			if ( !empty($params['query']) ) {
				$words = explode(' ', $params['query']);
				foreach($words as $key => $word) {
					$query->andWhere('u.fullName LIKE :query1'.$key . ' OR u.fullName LIKE :query2' . $key);
					$query->setParameter('query1'.$key, $word . '%');
					$query->setParameter('query2'.$key, '% '. $word. '%');
				}
			}
			$ds->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new VehicleModelTable(new Action($this,'searchVehicle', $params));
			$table->setDataSource($ds);
			$modal = $this->createForm(Vehicle::getClass(), new Action($this))->getModalVehicleModel();
			$widget = new PanelQuery($table, new Action($this,'searchVehicle', $params), $params['query'], $modal);
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function transferVehicleAction() {
		try {
			$plate = strtoupper($this->request->getPost('vehicle-plate'));
			$to = $this->request->getPost('agency-to');
			$entity = $this->getEntityManager()->getRepository(Vehicle::getClass())->findOneBy(['plate' => $plate]);
			$agency = $to ? $this->getEntityManager()->find(Agency::getClass(), $to) : $this->getAgencyActive();
			if ( ! $entity instanceof Vehicle ) {
				throw new \DomainException('Veículo <em>' . $plate . '</em> não encontrado.');
			}
			$entity->setResponsibleUnit($agency);
			$this->getEntityManager()->flush();
			$this->setAlert(new Alert('<strong>Ok! </strong>Veículo <em>#' . $entity->code . ' ' . $entity->description .  '</em> transferido com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		}
		$this->forward('/');
	}
	
	public function addCardAction() {
		try {
			$form = $this->createSubform();
			$form->bind($this->request->getPost());
			$table = $form->getTableCollection();
			$data = $form->getData();
			$provider = $this->getEntityManager()->find(ServiceProvider::getClass(), ( int ) $data['service-provider-id']);
			if ( ! $provider ) {
				throw new NotFoundEntityException('Não foi possível adicionar Cartão de Serviço. Provedor de Serviço <em>#' . $data['service-provider-id'] . '</em> não encontrado.');
			}
			$table->addItem(new ServiceCard($data['service-card-number'], $provider));
			$form->setData([]);
			$json = array($form->getName() => $form, 'flash-message' => null);
		} catch ( NotFoundEntityException $e ) {
			$json = array('flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$json = array('flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new JsonView($json, false);
	}
	
	public function removeCardAction() {
		try {
			$form = $this->createSubform();
			$table = $form->getTableCollection();
			$key = (int) $this->request->getQuery('key');
			if (! $table->removeItem($key)) {
				throw new NotFoundEntityException('Não foi possível remover Cartão de Serviço. Cartão <em>#' . $key . '</em> não encontrado.');
			}
			$json = array($form->getName() => $form, 'flash-message' => null);
		} catch ( NotFoundEntityException $e ) {
			$json = array('flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$json = array('flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new JsonView($json, false);
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), FleetItem::getClass(), $this);
	}
	
	/**
	 * @param string|FleetItem $item
	 * @param Action $submit
	 * @return BuilderForm
	 */
	private function createForm ( $item, Action $submit ) {
	    $subform = null;
	    $cancel = new Action($this);
	    $seek['agency'] =  new Action($this, 'seekAgency');
	    $find['agency'] = new Action($this, 'searchAgency');
	    $showAgencies = $this->getAgencyActive()->isGovernment();
	    
	    if ($item instanceof  FleetItem) {
	    	$item = get_class($item);
	    } 
	    $optResultCenter = [];
	    $criteria = ['active' => true, 'agency' => $this->getAgencySelected()->getId()];
	    $rs = $this->getEntityManager()->getRepository(ResultCenter::getClass())->findBy($criteria);
	    foreach ($rs as $result) {
	    	$optResultCenter[$result->id] = $result->description;
	    }
	    switch ($item) {
	    	case Vehicle::getClass():
	    		$seek['vehicle-plate'] =  new Action($this, 'seekVehiclePlate');
	    		$seek['vehicle'] = new Action($this, 'seekVehicle');
	    		$seek['owner'] = new Action($this, 'seekOwner');
	    		$find['vehicle'] = new Action($this, 'searchVehicle');
	    		$find['owner'] = new Action($this, 'searchOwner');
	    		$newOwnerPerson = new Action($this, 'newOwnerPerson');
	    		$newOwnerCompany = new Action($this, 'newOwnerCompany');
	    		return new FleetVehicleForm($submit, $seek['vehicle-plate'], $seek['vehicle'], $find['vehicle'], $seek['agency'], $find['agency'], $seek['owner'], $find['owner'], $newOwnerPerson, $newOwnerCompany, $cancel, $optResultCenter, $showAgencies, $subform);
	    		break;
	    		
	    	case Equipment::getClass():
	    		return new FleetEquipmentForm($submit, $cancel, $seek['agency'], $find['agency'], $optResultCenter, $showAgencies, $subform);
	    		break;
	    		
	    	default:
	    		throw new \InvalidArgumentException('Form not implements for '.$item);
	    		break;
	    }
	    
	}
	
	/**
	 * @return ServiceCardForm
	 */
	private function createSubform() {
		$options = [];
		$query = $this->getEntityManager()->getRepository(ServiceProvider::getClass())->createQueryBuilder('u');
		$query->andWhere('u.active = true');
		$result = $query->getQuery()->getResult();
		
		foreach ($result as $item ) {
			$options[$item->getId()] = $item->getName();
		}
		return new ServiceCardForm(new Action($this, 'addCard'), new Action($this, 'removeCard'), $options, $this->session);
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