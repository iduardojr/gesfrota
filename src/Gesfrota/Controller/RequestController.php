<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Controller\Helper\SearchAgency;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\Place;
use Gesfrota\Model\Domain\Request;
use Gesfrota\Model\Domain\RequestFreight;
use Gesfrota\Model\Domain\RequestTrip;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Services\AclResource;
use Gesfrota\View\DriverTable;
use Gesfrota\View\FleetVehicleTable;
use Gesfrota\View\Layout;
use Gesfrota\View\RequestFieldSetCancel;
use Gesfrota\View\RequestFieldSetDecline;
use Gesfrota\View\RequestFieldsetConfirm;
use Gesfrota\View\RequestFieldsetFinish;
use Gesfrota\View\RequestFieldsetInitiate;
use Gesfrota\View\RequestForm;
use Gesfrota\View\RequestFreightForm;
use Gesfrota\View\RequestList;
use Gesfrota\View\RequestTripForm;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\Widget\PanelQuery;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Tooltip\Tooltip;
use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\Model\Domain\Manager;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\TrafficController;
use Gesfrota\Model\Domain\User;

class RequestController extends AbstractController {
	
	use SearchAgency;
	
	public function indexAction() {
		try {
			$this->setAgencySelected(null);
			$this->setResultCenterSelected(null);
			$filter = new Action($this);
			$newTrip = new Action($this, 'newTrip');
			$newFreight = new Action($this, 'newFreight');
			$cancel = new Action($this, 'cancel');
			$print = new Action($this,'print');
			$do = $closure = null;
			$user = $this->getUserActive();
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
			
			if ($user instanceof Manager || $user instanceof FleetManager) {
				$optResultCenter = $agency->getResultCentersActived();
			} else {
				$optResultCenter = $user->getResultCentersActived();
			}
			
			$isUserAllowed = $user instanceof Manager || $user instanceof FleetManager || $user instanceof TrafficController;
			$isDriver = $user->getDriverLicense() && $user->getDriverLicense()->getActive();
			$isConfirm = AclResource::getInstance()->isAllowed($user, 'RequestController', 'confirm');
			if ( $isUserAllowed || $isDriver ) {
				$do = new Action($this);
				$closure = function( Button $button, Request $obj ) use ($user, $isConfirm, $isUserAllowed) {
				    $isInitiateRun = $obj->getDriver() == $user || $isUserAllowed;
					$allowed = $obj->getStateAllowed();
					$allow = array_keys($allowed);
					$allow = array_shift($allow);
					if ( !empty($allowed) ) {
						$button->setIcon(new Icon($allowed[$allow][0]));
						$button->setTooltip(new Tooltip($allowed[$allow][1]));
						switch ($allow) {
							case Request::CONFIRMED:
								$for = 'confirm';
								$button->setDisabled(!$isConfirm);
								break;
								
							case Request::INITIATED:
								$for = 'initiate';
								$button->setDisabled(!$isInitiateRun);
								break;
								
							case Request::FINISHED:
								$for = 'finish';
								$button->setDisabled(!$isInitiateRun);
								break;
								
							case Request::CANCELED:
								$for = 'cancel';
								break;
						}
						$button->getToggle()->getAction()->setMethodName($for);
					} else {
						$button->setDisabled(true);
						$button->setIcon(new Icon('icon-stop'));
						$button->setTooltip(new Tooltip($obj->getRequestType() . ' Encerrada'));
					}
				};
			}
			$list = new RequestList($filter, $newTrip, $newFreight, $cancel, $print, $optResultCenter, $do, $closure, $showAgencies);
		
			$query = $this->getEntityManager()->createQueryBuilder();
			$query->select('u');
			$query->from(Request::getClass(), 'u');
			if (! $showAgencies) {
			    $query->addSelect('l');
				$query->join('u.requesterUnit', 'l');
				$query->where('l.agency = :unit');
				$query->setParameter('unit', $this->getAgencyActive()->getId());
			}
			
			$query->addSelect('d');
			$query->leftJoin('u.driverLicense', 'd');
			$where = 'u.openedBy = :by OR d.user = :by';
			if ($user instanceof Requester) {
				$query->andWhere($where . ' OR u.requesterUnit = :r_unit');
				$query->setParameter('by', $user->getId());
				$query->setParameter('r_unit', $user->getLotation()->getId());
			} elseif ($user instanceof Driver) {
			    $query->andWhere($where);
				$query->setParameter('by', $user->getId());
			} elseif ($user instanceof TrafficController && $agency->isResultCenterRequired() ) {
			    $query->andWhere($where . ' OR u.resultCenter IN (:rc_user)');
				$query->setParameter('by', $user->getId());
				$query->setParameter('rc_user', $user->getAllResultCenters());
			}
			
			$helper->read($list, $query, array('limit' => 12, 'order' => 'DESC', 'processQuery' => function( QueryBuilder $query, array $data ) use ($user){
				
				if ( !empty($data['type']) ) {
					$query->andWhere('u INSTANCE OF ' . ( $data['type'] == 'T' ? RequestTrip::getClass() : RequestFreight::getClass()));
				}
				
				if (!empty($data['agency'])) {
					$query->join('u.requesterUnit', 'l');
					$query->andWhere('l.agency = :agency');
					$query->setParameter('agency', $data['agency']);
				}
				if (!empty($data['results-center'])) {
					$query->andWhere('u.resultCenter IN (:rc)');
					$query->setParameter('rc', $data['results-center']);
				}
				if ( !empty($data['from']) ) {
					$query->andWhere('u.from.description LIKE :from');
					$query->setParameter('from', '%' . $data['from'] . '%');
				}
				if ( !empty($data['to']) ) {
					$query->andWhere('u.to.description LIKE :to');
					$query->setParameter('to', '%' . $data['to'] . '%');
				}
				if ( !empty($data['date-initial']) ) {
					$query->andWhere('u.openedAt >= :initial');
					$query->setParameter('initial', $data['date-initial']);
				}
				if ( !empty($data['date-final']) ) {
					$query->andWhere('u.openedAt <= :final');
					$query->setParameter('final', $data['date-final'] . ' 23:59:59');
				}
				if ( !empty($data['status']) ) {
					$query->andWhere('u.status IN (:status)');
					$query->setParameter('status', $data['status']);
				}
			}));
			
			$list->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
			$list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}
	
	public function newTripAction() {
		try {
			$entity = new RequestTrip($this->getUserActive(), $this->request->getQuery('round-trip'));
			$submit = new Action($this, 'newTrip');
			$cancel = new Action($this);
			$location = new Action($this, 'location');
			$seekUnit = new Action($this, 'seekUnit');
			$searchUnit = new Action($this, 'searchUnit');
			$seekAgency = new Action($this, 'seekAgency');
			$searchAgency = new Action($this, 'searchAgency');
			$optMaps = $this->getApplication()->config['google']['maps'];
			$showLevelUnit = $this->getAgencyActive()->isGovernment() ? 2 : ($this->getUserActive() instanceof FleetManager || $this->getUserActive() instanceof Manager ? 1 : 0);
			if ( $this->getUserActive() instanceof Manager ) {
				if ($this->getAgencyActive()->isGovernment() || $this->getAgencyActive() == $this->getUserActive()->getLotation()->getAgency()) {
					$this->setAgencySelected($this->getUserActive()->getLotation()->getAgency());
					$entity->setRequesterUnit($this->getUserActive()->getLotation());
					$optResultCenter = $this->getUserActive()->getResultCentersActived();
				} else {
					$optResultCenter = $this->getAgencyActive()->getResultCentersActived();
				}
			} else {
				$optResultCenter = $this->getUserActive()->getResultCentersActived();
			}
			
			$isResultCenterRequired = $this->getAgencySelected()->isResultCenterRequired();
			
			$form = new RequestTripForm($submit, $cancel, $location, $seekUnit, $searchUnit, $seekAgency, $searchAgency, $optMaps, $optResultCenter, $isResultCenterRequired, $showLevelUnit);
		
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, $entity) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Viagem <em>#' . $entity->code . ' </em> solicitada com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function newFreightAction() {
		try {
			$to = $this->getRequest()->getQuery('to') == 'send' ? RequestFreight::TO_SEND : RequestFreight::TO_RECEIVE;
			$entity = new RequestFreight($this->getUserActive(), $to);
			$submit = new Action($this, 'newFreight');
			$cancel = new Action($this);
			$location = new Action($this, 'location');
			$seekUnit = new Action($this, 'seekUnit');
			$searchUnit = new Action($this, 'searchUnit');
			$seekAgency = new Action($this, 'seekAgency');
			$searchAgency = new Action($this, 'searchAgency');
			$optMaps = $this->getApplication()->config['google']['maps'];
			$showLevelUnit = $this->getAgencyActive()->isGovernment() ? 2 : ($this->getUserActive() instanceof FleetManager || $this->getUserActive() instanceof Manager ? 1 : 0);
			if ( $this->getUserActive() instanceof Manager ) {
				if ($this->getAgencyActive()->isGovernment() || $this->getAgencyActive() == $this->getUserActive()->getLotation()->getAgency()) {
					$this->setAgencySelected($this->getUserActive()->getLotation()->getAgency());
					$entity->setRequesterUnit($this->getUserActive()->getLotation());
					$optResultCenter = $this->getUserActive()->getResultCentersActived();
				} else {
					$optResultCenter = $this->getAgencyActive()->getResultCentersActived();
				}
			} else {
				$optResultCenter = $this->getUserActive()->getResultCentersActived();
			}
			
			$isResultCenterRequired = $this->getAgencySelected()->isResultCenterRequired();
			
			
			$form = new RequestFreightForm($submit, $cancel, $location, $seekUnit, $searchUnit, $seekAgency, $searchAgency, $optMaps, $optResultCenter, $isResultCenterRequired, $showLevelUnit);
	    
	        $helper = $this->createHelperCrud();
	        if ( $helper->create($form, $entity) ){
	            $entity = $helper->getEntity();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Entrega <em>#' . $entity->code . '</em> solicitada com sucesso!', Alert::Success));
	            $this->forward('/');
	        }
	    } catch ( InvalidRequestDataException $e ){
	        $form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch ( \Exception $e ) {
	        $form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	    }
	    return new Layout($form);
	}
	
	public function confirmAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Request::getClass(), $id);
			if (! $entity instanceof Request ) {
				throw new NotFoundEntityException('Não foi possível confirmar a requisição. Requisição <em>#' . $id . '</em> não encontrada.');
			}
			$this->setResultCenterSelected($entity->getResultCenter());
			$this->setAgencySelected($entity->getRequesterUnit()->getAgency());
			$form = new RequestForm($entity, new Action($this,'confirm', ['key' => $id]), new Action($this), new Action($this, 'decline', ['key' => $id]), $this->createFildesetConfirm());
			$form->initialize($this->getUserActive());
			$helper = $this->createHelperCrud();
			if ( $helper->update($form, $entity) ){
				$this->setAlert(new Alert('<strong>Ok! </strong>' . $entity->getRequestType() . ' <em>#' . $entity->getCode() . ' </em> confirmada com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function declineAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Request::getClass(), $id);
			if (! $entity instanceof Request ) {
				throw new NotFoundEntityException('Não foi possível recusar a requisição. Requisição <em>#' . $id . '</em> não encontrada.');
			}
			$this->setAgencySelected($entity->getRequesterUnit()->getAgency());
			$form = new RequestForm($entity, new Action($this,'confirm', ['key' => $id]), new Action($this), new Action($this,'decline', ['key' => $id]), new RequestFieldSetDecline());
			$form->initialize($this->getUserActive());
			$helper = $this->createHelperCrud();
			if ( $helper->update($form, $entity) ){
				$this->setAlert(new Alert('<strong>Ok! </strong>' . $entity->getRequestType() . ' <em>#' . $entity->getCode() . ' </em> recusada com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function initiateAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Request::getClass(), $id);
			if (! $entity instanceof Request ) {
				throw new NotFoundEntityException('Não foi possível iniciar a requisição. Requisição <em>#' . $id . '</em> não encontrada.');
			}
			$isUserAllowed = $this->getUserActive() instanceof Manager || $this->getUserActive() instanceof FleetManager || $this->getUserActive() instanceof TrafficController;
			$isDriver = $entity->getDriver() == $this->getUserActive();
			if (! ( $isUserAllowed || $isDriver ) ) {
			    throw new NotFoundEntityException('Não foi possível iniciar a requisição. Usuário não tem permissão para realizar operação.');
			}
			$this->setAgencySelected($entity->getRequesterUnit()->getAgency());
			$form = new RequestForm($entity, new Action($this,'initiate', ['key' => $id]), new Action($this), null, new RequestFieldsetInitiate());
			$form->initialize($this->getUserActive());
			$helper = $this->createHelperCrud();
			if ( $helper->update($form, $entity) ){
				$this->setAlert(new Alert('<strong>Ok! </strong>' . $entity->getRequestType() . ' <em>#' . $entity->getCode() . ' </em> iniciada com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function finishAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Request::getClass(), $id);
			if (! $entity instanceof Request ) {
				throw new NotFoundEntityException('Não foi possível finilizar a requisição. Requisição <em>#' . $id . '</em> não encontrada.');
			}
			$isUserAllowed = $this->getUserActive() instanceof Manager || $this->getUserActive() instanceof FleetManager || $this->getUserActive() instanceof TrafficController;
			$isDriver = $entity->getDriver() == $this->getUserActive();
			if (! ( $isUserAllowed || $isDriver ) ) {
			    throw new NotFoundEntityException('Não foi possível finalizar a requisição. Usuário não tem permissão para realizar operação.');
			}
			$this->setAgencySelected($entity->getRequesterUnit()->getAgency());
			$form = new RequestForm($entity, new Action($this,'finish', ['key' => $id]), new Action($this), null, new RequestFieldsetFinish());
			$form->initialize($this->getUserActive());
			$helper = $this->createHelperCrud();
			if ( $helper->update($form, $entity) ){
				$this->setAlert(new Alert('<strong>Ok! </strong>' . $entity->getRequestType() . ' <em>#' . $entity->getCode() . ' </em> iniciada com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function cancelAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Request::getClass(), $id);
			if (! $entity instanceof Request ) {
				throw new NotFoundEntityException('Não foi possível cancelar a requisição. Requisição <em>#' . $id . '</em> não encontrada.');
			}
			$this->setAgencySelected($entity->getRequesterUnit()->getAgency());
			$form = new RequestForm($entity, new Action($this,'cancel', ['key' => $id]), new Action($this), null, new RequestFieldSetCancel());
			$form->initialize($this->getUserActive());
			$helper = $this->createHelperCrud();
			if ( $helper->update($form, $entity) ){
				$this->setAlert(new Alert('<strong>Ok! </strong>' . $entity->getRequestType() . ' <em>#' . $entity->getCode() . ' </em> cancelada com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function printAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Request::getClass(), $id);
			if (! $entity instanceof Request ) {
				throw new NotFoundEntityException('Não foi possível cancelar a requisição. Requisição <em>#' . $id . '</em> não encontrada.');
			}
			$form = new RequestForm($entity, new Action($this,'cancel', ['key' => $id]), new Action($this));
			
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
			$this->forward('/');
		}
		return new Layout($form, 'layout/print.phtml');
	}
	
	public function seekVehicleAction() {
		try {
			$plate = strtoupper($this->request->getQuery('query'));
			$query = $this->getEntityManager()->getRepository(Vehicle::getClass())->createQueryBuilder('u');
			$query->join('u.responsibleUnit', 'r');
			$query->andWhere('u.active = true');
			$query->andWhere('r.id = :unit');
			$query->setParameter('unit', $this->getAgencySelected()->getId());
			$query->andWhere('u.plate = :plate');
			$query->setParameter('plate', $plate);
			if ($this->getAgencySelected()->isResultCenterRequired()) {
				$query->andWhere(':rs MEMBER OF u.resultCenters');
				$query->setParameter('rs', $this->getResultCenterSelected()->getId());
			}
			
			$entity = $query->getQuery()->getSingleResult();
			
			$data['vehicle-id'] = $entity->getId();
			$data['vehicle-plate'] = $entity->getPlate();
			$data['vehicle-description'] = $entity->getDescription();
			$data['alert-message'] = null;
		} catch ( NoResultException  $e ){
			$data['alert-message'] = new Alert('<strong>Ops! </strong>Veículo #' . $plate . ' não encontrado.');
			$data['vehicle-description'] = null;
			$data['vehicle-id'] = null;
		} catch ( \Exception $e ) {
			$data['alert-message'] = new Alert('<strong>Error: </strong>' . get_class($e).$e->getMessage(), Alert::Error);
			$data['vehicle-description'] = null;
			$data['vehicle-id'] = null;
		}
		return new JsonView($data, false);
	}
	
	public function seekDriverAction() {
		try {
			$id = $this->request->getQuery('query');
			
			$query = $this->getEntityManager()->getRepository(Driver::getClass())->createQueryBuilder('u');
			$query->distinct(true);
			$query->join('u.lotation', 'u1');
			$query->andWhere('u.active = true');
			$query->andWhere('u.id = :id');
			$query->andWhere('u1.agency = :agency');
			$query->setParameter('agency', $this->getAgencySelected()->getId());
			$query->setParameter('id', (int) $id);
			if ($this->getAgencySelected()->isResultCenterRequired()) {
				$query->andWhere(':rs MEMBER OF u.resultCenters');
				$query->setParameter('rs', $this->getResultCenterSelected()->getId());
			}
			
			$entity = $query->getQuery()->getSingleResult();
			if ( ! $entity instanceof Driver ) {
				throw new NotFoundEntityException('Motorista <em>#' . $id . '</em> não encontrado.');
			}
			return new JsonView(array('driver-id' => $entity->code, 'driver-name' => $entity->name, 'alert-message' => null), false);
		} catch ( NotFoundEntityException $e ){
			return new JsonView(array('driver-name' => '', 'alert-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())), false);
		} catch ( \Exception $e ) {
			return new JsonView(array('driver-name' => '', 'alert-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error)), false);
		}
	}
	
	public function searchDriverAction() {
		try {
			$query = $this->getEntityManager()->getRepository(User::getClass())->createQueryBuilder('u');
			$query->distinct(true);
			$query->join('u.driverLicense', 'd');
			$query->andWhere('d.active = true AND u.driverLicense IS NOT NULL');
			$query->join('u.lotation', 'u1');
			$query->andWhere('u1.agency = :agency');
			$query->setParameter('agency', $this->getAgencySelected()->getId());
			
			if ($this->getAgencySelected()->isResultCenterRequired()) {
				$query->andWhere(':rs MEMBER OF u.resultCenters');
				$query->setParameter('rs', $this->getResultCenterSelected()->getId());
			}
			
			$params = $this->request->getQuery();
			
			if ( $params['query'] ) {
				$query->andWhere('u.name LIKE :name');
				$query->setParameter('name', '%' . $params['query'] . '%');
			}
	
			$datasource = new EntityDatasource($query);
			$datasource->setOrderBy('name', 'ASC');
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new DriverTable(new Action($this,'searchDriver', $params));
			$table->setDataSource($datasource);
			$widget = new PanelQuery($table, new Action($this,'searchDriver'), $params['query'], $this->createFildesetConfirm()->getModalDriver());
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function searchVehicleAction() {
		try {
			$query = $this->getEntityManager()->getRepository(Vehicle::getClass())->createQueryBuilder('u');
			$query->join('u.responsibleUnit', 'r');
			$query->andWhere('u.active = true');
			$query->andWhere('r.id = :unit');
			$query->setParameter('unit', $this->getAgencySelected()->getId());
			
			if ($this->getAgencySelected()->isResultCenterRequired()) {
				$query->andWhere(':rs MEMBER OF u.resultCenters');
				$query->setParameter('rs', $this->getResultCenterSelected()->getId());
			}
			
			$params = $this->request->getQuery();
			
			$params = $this->request->getQuery();
			if ( $params['query'] ) {
				$q1 = $this->getEntityManager()->getRepository(Vehicle::getClass())->createQueryBuilder('v');
				$q1->select('v.id');
				$q1->join('v.model', 'm1');
				$q1->join('m1.maker', 'm2');
				$q1->where('m1.name LIKE :query');
				$q1->orWhere('m2.name LIKE :query');
				$q1->orWhere("CONCAT(m2.name, ' ', m1.name) LIKE :query");
				
				$query->andWhere('u.id IN (' . $q1->getDQL() . ')');
				$query->setParameter('query', '%' . $params['query'] . '%');
			}
			
			$datasource = new EntityDatasource($query);
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new FleetVehicleTable(new Action($this,'searchVehicle', $params));
			$table->setDataSource($datasource);
			$widget = new PanelQuery($table, new Action($this,'searchVehicle', $params), $params['query'], $this->createFildesetConfirm()->getModalVehicle());
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function locationAction() {
		try {
			$places = Place::autocomplete($this->request->getQuery('query'));
			$options = [];
			foreach( $places as $obj ) {
				$options[] = ['label' => $obj->getDescription(), 
							  'value' => $obj->getPlace()
							 ];
			}
			return new JsonView($options, false);
		} catch (\ErrorException $e) {
			return new JsonView(['error' => $e->getMessage()], false);
		}
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), Request::getClass(), $this);
	}
	
	/**
	 * @return RequestFieldsetConfirm
	 */
	private function createFildesetConfirm() {
		return new RequestFieldsetConfirm(new Action($this, 'seekVehicle'), new Action($this, 'searchVehicle'), new Action($this,'seekDriver'), new Action($this, 'searchDriver'));
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
	
	/**
	 * @param ResultCenter $unit
	 */
	protected function setResultCenterSelected(ResultCenter $unit = null) {
		$this->session->result_center_selected = $unit ? $unit->getId() : null;
	}
	
	/**
	 * @return ResultCenter|NULL
	 */
	protected function getResultCenterSelected() {
		if ($this->session->result_center_selected > 0) {
			return $this->getEntityManager()->find(ResultCenter::getClass(), $this->session->result_center_selected);
		}
		return null;
	}
	
}
?>