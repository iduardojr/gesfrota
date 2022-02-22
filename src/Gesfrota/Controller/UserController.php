<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Layout;
use Gesfrota\View\UserForm;
use Gesfrota\View\UserList;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\Manager;
use PHPBootstrap\Mvc\View\JsonView;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\AgencyTable;
use Gesfrota\View\Widget\PanelQuery;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\View\AdministrativeUnitTable;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Misc\Title;
use Doctrine\ORM\Query\ResultSetMapping;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Dropdown\Dropdown;
use PHPBootstrap\Widget\Dropdown\DropdownLink;
use PHPBootstrap\Widget\Dropdown\TgDropdown;
use PHPBootstrap\Widget\Action\TgLink;
use Gesfrota\Services\Logger;

class UserController extends AbstractController { 
	
	public function indexAction() {
		$filter = new Action($this);
		
		$newManager = new Action($this, 'newManager');
		$newFleetManager = new Action($this, 'newFleetManager');
		$newDriver = new Action($this, 'newDriver');
		$newRequester = new Action($this, 'newRequester');
		$edit = new Action($this, 'edit');
		$active = new Action($this, 'active');
		$reset = new Action($this, 'resetPassword');
		
		$profile = function(Button $btn, User $user) {
			$profiles = [Requester::getClass(), Driver::getClass(), FleetManager::getClass(), Manager::getClass()];
			$drop = new Dropdown();
			foreach ($profiles as $type) {
				$item = new DropdownLink(constant($type . '::USER_TYPE'));
				$item->setToggle(new TgLink(new Action($this, 'change' . str_replace('Gesfrota\\Model\\Domain\\', '', $type), ['key' => $user->getId()])));
				if ($type == get_class($user)) {
					$item->setDisabled(true);
				}
				$drop->addItem($item);
			}
			$btn->setToggle(new TgDropdown($drop));
		};
		
		$query = $this->getEntityManager()->getRepository(Agency::getClass())->createQueryBuilder('u');
		$agencies = $query->getQuery()->getResult();
		
		$list = new UserList($filter, $newManager, $newFleetManager, $newDriver, $newRequester, $edit, $active, $reset, $profile, $agencies);
		$this->session->selected = $this->getAgencyActive()->getId();
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, null, array('limit' => 12, 'processQuery' => function( QueryBuilder $query, array $data ) {
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
				
				if ( !empty($data['lotation']) ) {
					$query->join('u.lotation', 'l');
					$query->andWhere('l.agency = :agency');
					$query->setParameter('agency', $data['lotation']);
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
	
	public function newManagerAction() {
		$user = new Manager();
		$form = $this->createForm($user, new Action($this, 'newManager'));
		try {
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, $user) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Administrador <em>#' . $entity->code . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage(), Alert::Danger));	
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function newFleetManagerAction() {
		$user = new FleetManager();
		$form = $this->createForm($user, new Action($this, 'newFleetManager'));
		try {
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, $user) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Gestor de Frota <em>#' . $entity->code . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage(), Alert::Danger));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function newDriverAction() {
		$user = new Driver();
		$form = $this->createForm($user, new Action($this, 'newDriver'));
		try {
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, $user) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Motorista <em>#' . $entity->code . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage(), Alert::Danger));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function newRequesterAction() {
		$user = new Requester();
		$form = $this->createForm($user, new Action($this, 'newRequester'));
		try {
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, $user) ){
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
			$user = $this->getEntityManager()->find(User::getClass(), $id);
			if ( ! $user ) {
				throw new NotFoundEntityException('Não foi possível editar o Usuário. Usuário <em>#' . $id . '</em> não encontrado.');
			}
			$this->session->selected = $user->getLotation()->getAgency()->getId();
			$form = $this->createForm($user, new Action($this, 'edit', array('key' => $id)));
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar o Usuário. Usuário <em>#' . $id . '</em> não encontrado.'));
			if ( $helper->update($form, $user) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>' . $entity->userType. ' <em>#' . $entity->code . ' ' . $entity->name .  '</em> alterado com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar o Usuário. Usuário <em>#' . $id . '</em> não encontrado.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Usuário <em>#' . $entity->code . ' ' . $entity->name . '</em> ' . ( $entity->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function resetPasswordAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(User::getClass(), $id);
			if (! $entity instanceof User) {
				throw new NotFoundEntityException('Não foi possível redefinir a senha do Usuário. Usuário <em>#' . $id . '</em> não encontrado.');
			}
			$entity->setPassword(null);
			$this->getEntityManager()->flush();
			$this->setAlert(new Alert('<strong>Ok! </strong>Senha do ' . $entity->userType . ' <em>#' . $entity->code . ' ' . $entity->name . '</em> redefinida com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function changeRequesterAction() {
		$this->changeProfile($this->request->getQuery('key'), Requester::getClass());
		$this->forward('/');
	}
	
	public function changeManagerAction() {
		$this->changeProfile($this->request->getQuery('key'), Manager::getClass());
		$this->forward('/');
	}
	
	public function changeFleetManagerAction() {
		$this->changeProfile($this->request->getQuery('key'), FleetManager::getClass());
		$this->forward('/');
	}
	
	public function changeDriverAction() {
		$this->changeProfile($this->request->getQuery('key'), Driver::getClass());
		$this->forward('/');
	}
	
	private function changeProfile($id, $user) {
		try {
			$entity = $this->getEntityManager()->find(User::getClass(), $id);
			$this->getEntityManager()->detach($entity);
			if (! $entity instanceof User) {
				throw new NotFoundEntityException('Não foi possível alterar o Perfil do Usuário. Usuário <em>#' . $id . '</em> não encontrado.');
			}
			$type = substr(str_replace('Gesfrota\\Model\\Domain\\', '', $user), 0, 1);
			
			$sql = 'UPDATE users u SET u.type = :type ';
			$sql.= 'WHERE (u.id = :id)';
			
			$result = $this->getEntityManager()->getConnection()->executeStatement($sql, ['type' => $type, 'id' => $id]);
			if ( ! $result ) {
				throw new \ErrorException('Não foi possível alterar o Perfil do Usuário <em>#' . $id . '</em> para '. constant($user.'::USER_TYPE'));
			}
			$this->getEntityManager()->detach($entity);
			Logger::getInstance()->register($this->getEntityManager()->find(User::getClass(), $id), $entity);
			$this->setAlert(new Alert('<strong>Ok! </strong>Perfil do Usuário <em>#' . $entity->code . ' ' . $entity->name . '</em> alterado para ' . constant($user.'::USER_TYPE') . ' com sucesso!', Alert::Success));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
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
	
	public function seekAgencyAction() {
		try {
			$data['agency-id'] = null;
			$data['agency-name'] = null;
			$data['administrative-unit-id'] = null;
			$data['administrative-unit-name'] = null;
			$data['flash-message'] = null;
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(Agency::getClass())->findOneBy(['id' => $id, 'active' => true]);
			if ( ! $entity instanceof Agency ) {
				throw new NotFoundEntityException('Órgão <em>#' . $id . '</em> não encontrado.');
			}
			$this->session->selected = $entity->getId();
			$data['agency-id'] = $entity->getCode();
			$data['agency-name'] = $entity->getName();
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
	
	public function seekUnitAction() {
		try {
			$data['administrative-unit-id'] = null;
			$data['administrative-unit-name'] = null;
			$data['flash-message'] = null;
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->findOneBy(['id' => $id, 'active' => true, 'agency' => $this->session->selected]);
			if ( (! $entity instanceof AdministrativeUnit) ) {
				throw new NotFoundEntityException('Unidade Administrativa <em>#' . $id . '</em> não encontrada.');
			}
			$data['administrative-unit-id'] = $entity->getCode();
			$data['administrative-unit-name'] = $entity->getName();
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function searchUnitAction() {
		try {
			$query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
			$query->distinct(true);
			$query->andWhere('u.agency = :agency');
			$query->setParameter('agency', $this->session->selected);
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
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), User::getClass(), $this);
	}
	
	/**
	 * @param User $user
	 * @param Action $submit
	 * @return UserForm
	 */
	private function createForm ( User $user, Action $submit ) {
		$seek = new Action($this, 'seek');
		$seekAgency = new Action($this, 'seekAgency');
		$searchAgency = new Action($this, 'searchAgency');
		$seekUnit = new Action($this, 'seekUnit');
		$searchUnit = new Action($this, 'searchUnit');
		$cancel = new Action($this);
		return new UserForm($user, $submit, $seek, $seekAgency, $searchAgency, $seekUnit, $searchUnit, $cancel);
	}
	
}
?>