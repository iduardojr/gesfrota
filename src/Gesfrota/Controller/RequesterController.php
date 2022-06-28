<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Controller\Helper\SearchAgency;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\Manager;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Layout;
use Gesfrota\View\RequesterForm;
use Gesfrota\View\RequesterList;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class RequesterController extends AbstractController { 
	
	use SearchAgency;
	
	public function indexAction() {
		try {
			$this->setAgencySelected(null);
			$filter = new Action($this);
			$new = new Action($this, 'new');
			$lotation = new Action($this, 'lotation');
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
			
			$list = new RequesterList($filter, $lotation, $new, $edit, $active, $search, $transfer, $reset, $optResultCenter, $showAgencies);
		
			$query = $this->getEntityManager()->createQueryBuilder();
			$query->select('u');
			$query->from(Requester::getClass(), 'u');
			if (!$showAgencies) {
				$query->join('u.lotation', 'l');
				$query->where('l.agency = :unit');
				$query->setParameter('unit', $this->getAgencyActive()->getId());
			}
			$helper->read($list, $query, array('limit' => 12, 'processQuery' => function( QueryBuilder $query, array $data ) {
				if (!empty($data['agency'])) {
					$query->join('u.lotation', 'l');
					$query->where('l.agency = :agency');
					$query->setParameter('agency', $data['agency']);
				}
				if (!empty($data['results-center'])) {
					$query->andWhere(':rc MEMBER OF u.resultCenters');
					$query->setParameter('rc', $data['results-center']);
				}
				if ( !empty($data['type']) ) {
					foreach($data['type'] as $type) {
						switch ($type) {
							case 'M':
								$query->andWhere('u INSTANCE OF ' . Manager::getClass());
								break;
								
							case 'F':
								$query->andWhere('u INSTANCE OF ' . FleetManager::getClass());
								break;
								
							case 'D':
								$query->andWhere('u INSTANCE OF ' . Driver::getClass());
								break;
								
							case 'R':
								$query->andWhere('u INSTANCE OF ' . Requester::getClass());
								break;
						}
					}
				}
				
				if ( !empty($data['name']) ) {
					$query->andWhere('u.name LIKE :name');
					$query->setParameter('name', '%' . $data['name'] . '%');
				}
				
				if ( !empty($data['lotation-id']) ) {
					$query->andWhere('u.lotation = :unit');
					$query->setParameter('unit', $data['lotation-id']);
				}
				
				if ( !empty($data['nif']) ) {
					$query->andWhere('u.nif = :nif');
					$query->setParameter('nif', $data['nif']);
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
			if ( $entity instanceof User) {
				throw new \DomainException($entity->getUserType() .' <em>' . $entity->getName() . ' (CPF' . $entity->getNif() . ')</em> já está registrado em '. $entity->getLotation()->getAgency()->getAcronym());
			}
			if ( $helper->create($form) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Requisitante <em>#' . $entity->code . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage(), Alert::Danger));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function editAction() {
		try {
			$id = (int) $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$entity = $this->getEntityManager()->find(Requester::getClass(), (int) $id);
			if ( ! $entity instanceof Requester ) {
				throw new NotFoundEntityException('Não foi possível editar o Requisitante. Requisitante <em>#' . $id . '</em> não encontrado.');
			}
			$this->setAgencySelected($entity->getLotation()->getAgency());
			$form = $this->createForm(new Action($this, 'edit', array('key' => $id)));
			if ( $helper->update($form, $entity) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Requisitante <em>#' . $entity->code . ' ' . $entity->name .  '</em> alterado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong> ' . $e->getMessage() . nl2br($e->getTraceAsString()), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function activeAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar o Requisitante. Requisitante <em>#' . $id . '</em> não encontrado.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Requisitante <em>#' . $entity->code . ' ' . $entity->name . '</em> ' . ( $entity->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function transferAction() {
		try {
			$nif = $this->request->getPost('requester-nif');
			$to = $this->request->getPost('agency-to');
			$entity = $this->getEntityManager()->getRepository(Requester::getClass())->findOneBy(['nif' => $nif]);
			$agency = $to ? $this->getEntityManager()->find(Agency::getClass(), $to) : $this->getAgencyActive();
			if (! $entity instanceof Requester ) {
				throw new \DomainException('Requisitante <em>CPF ' . $nif . '</em> não encontrado.');
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
			$this->setAlert(new Alert('<strong>Ok! </strong>Requisitante <em>#' . $entity->code . ' ' . $entity->name .  '</em> transferido com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		}
		$this->forward('/');
	}
	
	public function seekAction() {
		try {
			$nif = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(User::getClass())->findOneBy(['nif' => $nif]);
			$data['flash-message'] = null;
			if ( $entity instanceof User) {
				throw new \DomainException($entity->getUserType() .' <em>' . $entity->getName() . ' (CPF' . $entity->getNif() . ')</em> já está registrado em '. $entity->getLotation()->getAgency()->getAcronym());
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
			$entity = $this->getEntityManager()->getRepository(Requester::getClass())->findOneBy(['nif' => $nif]);
			if (! $entity instanceof User) {
				throw new \DomainException('Requisitante <em>CPF ' . $nif . '</em> não encontrado.');
			}
			$data['requester-name'] = $entity->getName();
			$data['lotation-description'] = $entity->getLotation()->getAgency()->getName();
			$data['flash-message-driver'] = null;
		} catch ( \DomainException $e ){
			$data['flash-message-driver'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message-driver'] = new Alert('<strong>Error: </strong>' . get_class($e).$e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function lotationAction() {
		try {
			$query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
			$query->distinct(true);
			$query->andWhere('u.agency = :agency');
			$query->setParameter('agency', $this->getAgencyActive()->getId());
			$query->orderBy('u.lft');
			$params = $this->request->getQuery();
			if ( isset($params['query']) ) {
				$query->from(AdministrativeUnit::getClass(), 'p0');
				$query->andWhere('u.lft BETWEEN p0.lft AND p0.rgt');
				$query->andWhere('p0.name LIKE :name OR p0.acronym LIKE :name');
				$query->setParameter('name', '%' . $params['query'] . '%');
			}
			$lotations = $query->getQuery()->getResult();
			$options = [];
			foreach( $lotations as $obj ) {
				$options[] = ['label' => $obj->getPartialDescription(),
							  'value' => ['lotation-id' => $obj->getId()]
				];
			}
			return new JsonView($options, false);
		} catch (\ErrorException $e) {
			return new JsonView(['error' => $e->getMessage()], false);
		}
	}
	
	public function resetPasswordAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Requester::getClass(), $id);
			if (! $entity instanceof Requester) {
				throw new NotFoundEntityException('Não foi possível redefinir a senha do Requisitante. Requisitante <em>#' . $id . '</em> não encontrado.');
			}
			$entity->setPassword(null);
			$this->getEntityManager()->flush();
			$this->setAlert(new Alert('<strong>Ok! </strong>Senha do Requisitante <em>#' . $entity->code . ' ' . $entity->name . '</em> redefinida com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), Requester::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return RequesterForm
	 */
	private function createForm (Action $submit ) {
		$seek = new Action($this, 'seek');
		$seekUnit = new Action($this, 'seekUnit');
		$searchUnit = new Action($this, 'searchUnit');
		$seekAgency = new Action($this, 'seekAgency');
		$searchAgency = new Action($this, 'searchAgency');
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
		
		return new RequesterForm($submit, $seek, $seekUnit, $searchUnit, $seekAgency, $searchAgency, $cancel, $optResultCenter, $showAgency);
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