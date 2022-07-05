<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Controller\Helper\SearchAgency;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\DriverForm;
use Gesfrota\View\DriverList;
use Gesfrota\View\Layout;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\Model\Domain\DriverLicense;
use Doctrine\ORM\Query\Expr\Join;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Format\DateTimeParser;

class DriverController extends AbstractController {
	
	use SearchAgency;
	
	public function indexAction() {
		try {
			$this->setAgencySelected(null);
			$filter = new Action($this);
			$new = new Action($this, 'new');
			$edit = new Action($this, 'edit');
			$active = new Action($this, 'active');
			$search = new Action($this, 'search');
			$transfer = new Action($this, 'transfer');
			$reset = new Action($this, 'resetPassword');
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
			
			$list = new DriverList($filter, $new, $edit, $active, $search, $transfer, $reset, $optResultCenter, $showAgencies);
		
			$query = $this->getEntityManager()->createQueryBuilder();
			$query->select('u');
			$query->from(User::getClass(), 'u');
			$query->join('u.driverLicense', 'd');
			$query->where('u.driverLicense IS NOT NULL');
			if (! $showAgencies) {
				$query->join('u.lotation', 'l', Join::WITH, 'l.agency = :agency');
				$query->setParameter('agency', $this->getAgencyActive()->getId());
			}
			$helper->read($list, $query, array('limit' => 20, 'processQuery' => function( QueryBuilder $query, array $data ) {
				if (!empty($data['agency'])) {
					$query->join('u.lotation', 'l', Join::WITH, 'l.agency = :agency');
					$query->setParameter('agency', $data['agency']);
				}
				if (!empty($data['results-center'])) {
					$query->andWhere(':rc MEMBER OF u.resultCenters');
					$query->setParameter('rc', $data['results-center']);
				}
				if ( !empty($data['name']) ) {
					$query->andWhere('u.name LIKE :name');
					$query->setParameter('name', '%' . $data['name'] . '%');
				}
				
				if ( !empty($data['nif']) ) {
					$query->andWhere('u.nif = :nif');
					$query->setParameter('nif', $data['nif']);
				}
				
				if ( !empty($data['categories']) ) {
					$subquery = $this->getEntityManager()->createQueryBuilder('d2');
					$subquery->from(DriverLicense::getClass(), 'd2');
					$subquery->select('d2');
					foreach($data['categories'] as $key => $val) {
						$subquery->orWhere('d2.category LIKE :cat' . $key);
						$query->setParameter('cat' . $key, '%' . $val . '%');
					}
					$query->andWhere('EXISTS ( ' . $subquery->getDQL() . ' )');
				}
				
				if ( !empty($data['only-active']) ) {
					$query->andWhere('u.active = true');
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
			$form = $this->createForm(new Action($this, 'new'));
			$helper = $this->createHelperCrud();
			$nif = $this->getRequest()->getPost('nif');
			$entity = $this->getEntityManager()->getRepository(User::getClass())->findOneBy(['nif' => $nif]);
			if ( $entity instanceof User ) {
				$isGovernment = $this->getAgencyActive()->isGovernment();
				$isUserAssignedToAgencyActive = $entity->getLotation()->getAgency() == $this->getAgencyActive();
				$isDriver = $entity->getDriverLicense() !== null;
				if (! ( $isGovernment || $isUserAssignedToAgencyActive ) || $isDriver ) {
					throw new \DomainException($entity->getUserType() .' <em>' . $entity->getName() . ' (CPF' . $entity->getNif() . ')</em> já está registrado em '. $entity->getLotation()->getAgency()->getAcronym());
				}
			} else {
				$entity = new Driver();
			}
			if ( $helper->create($form, $entity) ){
				$this->setAlert(new Alert('<strong>Ok! </strong>Motorista <em>#' . $entity->code . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function editAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$entity = $this->getEntityManager()->find(User::getClass(), (int) $id);
			if ( ! $entity instanceof User || $entity->getDriverLicense() === null ) {
				throw new NotFoundEntityException('Não foi possível editar o Motorista. Motorista <em>#' . $id . '</em> não encontrado.');
			}
			$this->setAgencySelected($entity->getLotation()->getAgency());
			$form = $this->createForm(new Action($this, 'edit', array('key' => $id)));
			if ( $helper->update($form, $entity) ) {
				$this->setAlert(new Alert('<strong>Ok! </strong>Motorista <em>#' . $entity->code . ' ' . $entity->name .  '</em> alterado com sucesso!', Alert::Success));
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
			$entity = $this->getEntityManager()->find(User::getClass(), (int) $id);
			if ( ! $entity instanceof User || $entity->getDriverLicense() === null ) {
				throw new NotFoundEntityException('Não foi possível ativar/desativar o Motorista. Motorista <em>#' . $id . '</em> não encontrado.');
			}
			$helper = $this->createHelperCrud();
			$helper->active($entity->getDriverLicense());
			$this->setAlert(new Alert('<strong>Ok! </strong>Motorista <em>#' . $entity->code . ' ' . $entity->name . '</em> ' . ( $entity->driverLicense->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function transferAction() {
		try {
			$nif = $this->request->getPost('driver-nif');
			$to = $this->request->getPost('agency-to');
			$entity = $this->getEntityManager()->getRepository(User::getClass())->findOneBy(['nif' => $nif]);
			$agency = $to ? $this->getEntityManager()->find(Agency::getClass(), $to) : $this->getAgencyActive();
			if ( $entity === null ) {
				throw new \DomainException('Motorista <em>CPF ' . $nif . '</em> não encontrado.');
			}
			if (! $entity instanceof Driver) {
				throw new \DomainException($entity->getUserType() .' <em>' . $entity->getName() . ' (CPF ' . $entity->getNif() . ')</em> não pode ser transferido.');
			}
			if ($agency) {
				$query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
				$query->andWhere('u.agency = :agency');
				$query->orderBy('u.lft');
				$query->setMaxResults(1);
				$query->setParameter('agency', $agency->getId());
				$unitTo = $query->getQuery()->getOneOrNullResult();
				if (! $unitTo instanceof AdministrativeUnit ) {
					throw new \DomainException('Não foi possível Transferir Usuários: Órgão de Destino não possui uma unidade administrativa.');
				}
			} else {
				$unitTo = $this->getUserActive()->getLotation();
			}
			$entity->setLotation($unitTo);
			$this->getEntityManager()->flush();
			$this->setAlert(new Alert('<strong>Ok! </strong>Motorista <em>#' . $entity->code . ' ' . $entity->name .  '</em> transferido com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		}
		$this->forward('/');
	}
	
	public function resetPasswordAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(User::getClass(), $id);
			if (! $entity instanceof User || $entity->getDriverLicense() === null ) {
				throw new NotFoundEntityException('Não foi possível redefinir a senha do Motorista. Motorista <em>#' . $id . '</em> não encontrado.');
			}
			$entity->getUser()->setPassword(null);
			$this->getEntityManager()->flush();
			$this->setAlert(new Alert('<strong>Ok! </strong>Senha do Motorista <em>#' . $entity->code . ' ' . $entity->name . '</em> redefinida com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function seekAction() {
		try {
			$nif = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(User::getClass())->findOneBy(['nif' => $nif]);
			$data['flash-message'] = null;
			$data['user'] = 'Motorista';
			$data['user-id'] = 0;
			$data['name'] = null;
			$data['email'] = null;
			$data['cell'] = null;
			$data['gender'] = null;
			$data['birthday'] = null;
			$data['active'] = null;
			$data['agency-id'] = null;
			$data['agency-name'] = null;
			if (! $this->getAgencyActive()->isGovernment()) {
				$data['agency-id'] = $this->getAgencyActive()->getCode();
				$data['agency-name'] = $this->getAgencyActive()->getName();
			}
			$data['administrative-unit-id'] = null;
			$data['administrative-unit-name'] = null;
			$data['results-center'] = [];
			$data['driver-license-number'] = null;
			$data['driver-license-categories'] = null;
			$data['driver-license-expires'] = null;
			if ( $entity instanceof User ) {
				$isEdit = stripos($this->getRequest()->getHeader('referer'), '/driver/edit/') !== false;
				$isGovernment = $this->getAgencyActive()->isGovernment();
				$isUserAssignedToAgencyActive = $entity->getLotation()->getAgency() == $this->getAgencyActive();
				$isDriver = $entity->getDriverLicense() !== null;
				if ( ! $isEdit && ( $isGovernment || $isUserAssignedToAgencyActive ) && ! $isDriver ) {
					$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $entity->getUserType() .' <em>' . $entity->getName() . ' (CPF ' . $entity->getNif() . ')</em> já está registrado em '. $entity->getLotation()->getAgency()->getAcronym(), Alert::Success);
					
					$data['user'] = $entity->getUserType() . ' #' . $entity->getCode();
					$data['user-id'] = $entity->getId();
					$data['name'] = $entity->getName();
					$data['email'] = $entity->getEmail();
					$data['cell'] = $entity->getCell();
					$data['gender'] = $entity->getGender();
					
					$parser = new DateFormat('dd/mm/yyyy', DateTimeParser::getInstance());
					$data['birthday'] = $parser->format($entity->getBirthday());
					$data['active'] = $entity->getActive();
					
					$data['agency-id'] = $entity->getLotation()->getAgency()->getCode();
					$data['agency-name'] = $entity->getLotation()->getAgency()->getName();
					
					$data['administrative-unit-id'] = $entity->getLotation()->getCode();
					$data['administrative-unit-name'] = $entity->getLotation()->getName();
						
					$data['results-center'] = array_keys($entity->getAllResultCenters());
					
					if ($entity->getDriverLicense()) {
						$data['driver-license-number'] = $entity->getDriverLicense()->getNumber();
						$data['driver-license-categories'] = $entity->getDriverLicense()->getCategories();
						$data['driver-license-expires'] = $parser->format($entity->getDriverLicense()->getExpires());
					}
					
					$this->setAgencySelected($entity->getLotation()->getAgency());
				} else {
					throw new \DomainException('Motorista <em>' . $entity->getName() . ' (CPF ' . $entity->getNif() . ')</em> já está registrado em '. $entity->getLotation()->getAgency()->getAcronym());
				}
			}
		} catch ( \DomainException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . get_class($e).$e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function searchAction() {
		try {
			$nif = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(User::getClass())->findOneBy(['nif' => $nif]);
			$data['driver-name'] = null;
			$data['lotation-description'] = null;
			if ( $entity === null ) {
				throw new \DomainException('Motorista <em>CPF ' . $nif . '</em> não encontrado.');
			}
			if (! $entity instanceof Driver) {
				throw new \DomainException($entity->getUserType() .' <em>' . $entity->getName() . ' (CPF ' . $entity->getNif() . ')</em> não pode ser transferido.');
			}
			$data['driver-name'] = $entity->getName();
			$data['lotation-description'] = $entity->getLotation()->getAgency()->getName();
			$data['flash-message-driver'] = null;
		} catch ( \DomainException $e ){
			$data['flash-message-driver'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message-driver'] = new Alert('<strong>Error: </strong>' . get_class($e).$e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), DriverLicense::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return DriverForm
	 */
	private function createForm ( Action $submit ) {
		$seek = new Action($this, 'seek');
		$seekUnit = new Action($this, 'seekUnit');
		$searchUnit = new Action($this, 'searchUnit');
		$seekAgency = new Action($this, 'seekAgency');
		$seachAgency = new Action($this, 'searchAgency');
		$cancel = new Action($this);
		$showAgency = null;
		if (! $this->getAgencyActive()->isGovernment()) {
			$showAgency = $this->getAgencyActive();
		}
		
		$optResultCenter = [];
		$criteria = ['active' => true, 'agency' => $this->getAgencySelected()->getId()];
		$rs = $this->getEntityManager()->getRepository(ResultCenter::getClass())->findBy($criteria);
		foreach ($rs as $result) {
			$optResultCenter[$result->id] = $result->description;
		}
		
		return new DriverForm($submit, $seek, $seekUnit, $searchUnit, $seekAgency, $seachAgency, $cancel, $optResultCenter, $showAgency);
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