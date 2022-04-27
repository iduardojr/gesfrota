<?php
namespace Gesfrota\Controller;

use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Entity;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Manager;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\AdministrativeUnitTable;
use Gesfrota\View\AgencyTable;
use Gesfrota\View\Layout;
use Gesfrota\View\TransferFleetForm;
use Gesfrota\View\TransferUsersForm;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\Widget\PanelQuery;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;

class TransferUsersController extends AbstractController {
	
	/**
	 * @var TransferFleetForm
	 */
	protected $form;
	
	public function __construct() {
		$submit = new Action($this);
		$seek1 = new Action($this, 'seek-from');
		$seek2 = new Action($this, 'seek-to');
		$search1 = new Action($this, 'search-agency');
		$search2 = new Action($this, 'search-unit');
		$cancel = new Action(AgencyController::getClass());
		
		$this->form = new TransferUsersForm($submit, $seek1, $seek2, $search1, $search2, $cancel);
		parent::__construct();
	}
	
	public function indexAction() {
		try {
			if ($this->request->isPost()) {
				$post = $this->request->getPost();
				$agencyFrom = $this->getEntityManager()->find(Agency::getClass(), (int) $post['from-agency-id']);
				$unitFrom = $this->getEntityManager()->find(AdministrativeUnit::getClass(), (int) $post['from-administrative-unit-id']);
				if ( ($agencyFrom instanceof Agency && ! $agencyFrom->isGovernment() ) || $unitFrom instanceof AdministrativeUnit ) {
					$ds = $this->createDataSource($unitFrom ? $unitFrom : $agencyFrom);
					$ds->setPage($this->session->from_page);
					$this->form->getTableFrom()->setDataSource($ds);
				} 
				$agencyTo = $this->getEntityManager()->find(Agency::getClass(), (int) $post['to-agency-id']);
				$unitTo = $this->getEntityManager()->find(AdministrativeUnit::getClass(), (int) $post['to-administrative-unit-id']);
				if ( ($agencyTo instanceof Agency && ! $agencyTo->isGovernment() ) || $unitTo instanceof AdministrativeUnit ) {
					$ds = $this->createDataSource($unitTo ? $unitTo : $agencyTo);
					$this->form->getTableTo()->setDataSource($ds);
				}
				$this->form->bind($post);
				if ( ! $this->form->valid() ) {
					throw new InvalidRequestDataException();
				}
				if (! $agencyFrom instanceof Agency) {
					throw new \DomainException('Não foi possivel Transferir Usuários: Órgão de Origem <em>#' . $key . '</em> não encontrado.');
				}
				if (! $agencyTo instanceof Agency) {
					throw new \DomainException('Não foi possível Transferir Usuários: Órgão de Destino <em>#' . $key . '</em> não encontrado.');
				}
				if ( ! isset($post['from-users']) ) {
					throw new InvalidRequestDataException('Por favor, selecione pelo menos um usuário para ser transferido.');
				}
				if ( empty($unitTo) ) {
					$query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
					$query->andWhere('u.agency = :agency');
					$query->orderBy('u.lft');
					$query->setMaxResults(1);
					$query->setParameter('agency', $agencyTo->getId());
					$result = $query->getQuery()->getResult();
					$unitTo = isset($result[0]) ? $result[0] : null;
				}
				if (! $unitTo instanceof AdministrativeUnit ) {
					throw new \DomainException('Não foi possível Transferir Usuários: Órgão de Destino não possui uma unidade administrativa.');
				}
				foreach($post['from-users'] as $key) {
					$user = $this->getEntityManager()->find(User::getClass(), (int) $key);
					if (! $user instanceof User) {
						throw new \DomainException('Não foi possivel Transferir Usuários: Usuário <em>#' . $key . '</em> não encontrado.');
					}
					$user->setLotation($unitTo);
				}
				$this->getEntityManager()->flush();
				
				$success = '<span class="badge badge-success"> '. count($post['from-users']) . '</span> Usuários ';
				$success.= 'transferidos com sucesso de <em>#' . $agencyFrom->code . ' ' . $agencyFrom->acronym . '</em> ';
				$success.= 'para <em>#' . $agencyTo->code . ' ' . $agencyTo->acronym . '</em>.';
				$this->form->setAlert(new Alert($success, Alert::Success));
			}
		} catch ( InvalidRequestDataException $e ){
			$this->form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($this->form);
	}
	
	public function seekFromAction() {
		try {
			$data['from-agency-id'] = null;
			$data['from-agency-name'] = null;
			$data['from-administrative-unit-id'] = null;
			$data['from-administrative-unit-name'] = null;
			$data['flash-message'] = null;
			$data[$this->form->getTableFrom()->getName()] = $this->form->getTableFrom();
			$params = $this->request->getQuery();
			
			if ($params['type'] == 'A') {
				$entity = $this->getEntityManager()->find(Agency::getClass(), (int) $params['query']);
				if ( ! $entity instanceof Agency || $entity->isGovernment() ) {
					throw new NotFoundEntityException('Órgão <em>#' . $id . '</em> não encontrado.');
				}
				$this->session->from = $entity->getId();
				$data['from-agency-id'] = $entity->getCode();
				$data['from-agency-name'] = $entity->getName();
			} else {
				$entity = $this->getEntityManager()->find(AdministrativeUnit::getClass(), (int) $params['query']);
				if ( ! $entity instanceof AdministrativeUnit ) {
					throw new NotFoundEntityException('Unidade Adminstrativa <em>#' . $id . '</em> não encontrada.');
				}
				$this->session->from = $entity->getAgency()->getId();
				$data['from-agency-id'] = $entity->getAgency()->getCode();
				$data['from-agency-name'] = $entity->getAgency()->getName();
				$data['from-administrative-unit-id'] = $entity->getCode();
				$data['from-administrative-unit-name'] = $entity->getPartialDescription();
			}
			
			$this->session->from_page = isset($params['page']) ? $params['page'] : 1;
			$ds = $this->createDataSource($entity);
			$ds->setPage($this->session->from_page);
			
			$toggle = new TgAjax(new Action($this, 'seekFrom', $params), $this->form->getTableFrom(), TgAjax::Json);
			
			$this->form->getTableFrom()->setDataSource($ds);
			$this->form->getTableFrom()->buildPagination($toggle);
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function seekToAction() {
		try {
			$data['to-agency-id'] = null;
			$data['to-agency-name'] = null;
			$data['to-administrative-unit-id'] = null;
			$data['to-administrative-unit-name'] = null;
			$data['flash-message'] = null;
			$data[$this->form->getTableTo()->getName()] = $this->form->getTableTo();
			$params = $this->request->getQuery();
			
			if ($params['type'] == 'A') {
				$entity = $this->getEntityManager()->find(Agency::getClass(), (int) $params['query']);
				if ( ! $entity instanceof Agency || $entity->isGovernment() ) {
					throw new NotFoundEntityException('Órgão <em>#' . $id . '</em> não encontrado.');
				}
				$this->session->from = $entity->getId();
				$data['to-agency-id'] = $entity->getCode();
				$data['to-agency-name'] = $entity->getName();
			} else {
				$entity = $this->getEntityManager()->find(AdministrativeUnit::getClass(), (int) $params['query']);
				if ( ! $entity instanceof AdministrativeUnit ) {
					throw new NotFoundEntityException('Unidade Adminstrativa <em>#' . $id . '</em> não encontrada.');
				}
				$this->session->from = $entity->getAgency()->getId();
				$data['to-agency-id'] = $entity->getAgency()->getCode();
				$data['to-agency-name'] = $entity->getAgency()->getName();
				$data['to-administrative-unit-id'] = $entity->getCode();
				$data['to-administrative-unit-name'] = $entity->getPartialDescription();
			}
			
			$ds = $this->createDataSource($entity);
			$ds->setPage(isset($params['page']) ? $params['page'] : 1);
			
			$toggle = new TgAjax(new Action($this, 'seekTo', $params), $this->form->getTableTo(), TgAjax::Json);
			
			$this->form->getTableTo()->setDataSource($ds);
			$this->form->getTableTo()->buildPagination($toggle);
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function searchAgencyAction() {
		try {
			$query = $this->getEntityManager()->getRepository(Agency::getClass())->createQueryBuilder('u');
			$query->andWhere('u.id > 0');
			$params = $this->request->getQuery();
			if ( $params['query'] ) {
				$query->andWhere('u.name LIKE :name');
				$query->orWhere('u.acronym LIKE :name');
				$query->setParameter('name', '%' . $params['query'] . '%');
			}
			$datasource = new EntityDatasource($query);
			$datasource->setOrderBy('name', 'ASC');
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new AgencyTable(new Action($this,'searchAgency', $params));
			$table->setDataSource($datasource);
			$widget = new PanelQuery($table, new Action($this,'searchAgency', $params), $params['query'], new Modal('agency-search', new Title('Unidades Administrativas', 3)));
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function searchUnitAction() {
		try {
			$query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
			$query->distinct(true);
			if ($this->session->from <= 0) {
				throw new \DomainException('Órgão <em>#' . $this->session->from . '</em> não encontrado.');
			}
			$query->andWhere('u.agency = :agency');
			$query->setParameter('agency', $this->session->from);
			$query->orderBy('u.lft');
			$params = $this->request->getQuery();
			if ( isset($params['query']) ) {
				$query->from(AdministrativeUnit::getClass(), 'p0');
				$query->andWhere('u.lft BETWEEN p0.lft AND p0.rgt');
				$query->andWhere('p0.name LIKE :name OR p0.acronym LIKE :name');
				$query->setParameter('name', '%' . $params['query'] . '%');
			}
			$datasource = new EntityDatasource($query);
			$datasource->setOrderBy('lft', 'ASC');
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new AdministrativeUnitTable(new Action($this,'searchUnit', $params));
			$table->setDataSource($datasource);
			$widget = new PanelQuery($table, new Action($this,'searchUnit', $params), $params['query'], new Modal('administrative-unit-search', new Title('Unidades Administrativas', 3)));
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	/**
	 * 
	 * @param Agency|AdministrativeUnit $entity
	 * @return EntityDatasource
	 */
	private function createDataSource(Entity $entity) {
		$query = $this->getEntityManager()->getRepository(User::getClass())->createQueryBuilder('u');
		$query->where('u NOT INSTANCE OF '. Manager::getClass());
		if ($entity instanceof Agency) {
			$query->join('u.lotation', 'l');
			$query->andWhere('l.agency = :unit');
		} else {
			$query->andWhere('u.lotation = :unit');
		}
		$query->setParameter('unit', $entity->getId());
		return new EntityDatasource($query, ['limit' => 25]);
	}
	
}
?>