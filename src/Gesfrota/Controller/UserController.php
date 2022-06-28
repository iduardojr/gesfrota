<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Controller\Helper\SearchAgency;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\Manager;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\Model\Domain\TrafficController;
use Gesfrota\Model\Domain\User;
use Gesfrota\Services\Logger;
use Gesfrota\View\Layout;
use Gesfrota\View\UserForm;
use Gesfrota\View\UserList;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Dropdown\Dropdown;
use PHPBootstrap\Widget\Dropdown\DropdownLink;
use PHPBootstrap\Widget\Dropdown\TgDropdown;
use PHPBootstrap\Widget\Misc\Alert;

class UserController extends AbstractController { 
	
	use SearchAgency;
	
	public function indexAction() {
		$this->setAgencySelected(null);
		
		$filter = new Action($this);
		$new1 = new Action($this, 'newManager');
		$new2 = new Action($this, 'newFleetManager');
		$new3 = new Action($this, 'newTrafficController');
		$new4 = new Action($this, 'newDriver');
		$new5 = new Action($this, 'newRequester');
		$edit = new Action($this, 'edit');
		$active = new Action($this, 'active');
		$reset = new Action($this, 'resetPassword');
		
		$profile = function(Button $btn, User $user) {
			$profiles = [Requester::getClass(), Driver::getClass(), TrafficController::getClass(), FleetManager::getClass(), Manager::getClass()];
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
		$query->where('u.id > 0');
		$query->addOrderBy('u.acronym');
		$result = $query->getQuery()->getResult();
		$agencies = ['' => 'Todos'];
		foreach($result as $item) {
		    $agencies[$item->id] = $item . ' (' . $item->id . ')';
		}
		
		$list = new UserList($filter, $new1, $new2, $new3, $new4, $new5, $edit, $active, $reset, $profile, $agencies);
		
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
								
							case 'T':
								$query->andWhere('u INSTANCE OF ' . TrafficController::getClass());
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
	
	public function newTrafficControllerAction() {
		$user = new TrafficController();
		$form = $this->createForm($user, new Action($this, 'newTrafficController'));
		try {
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, $user) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Controlador de Tráfego <em>#' . $entity->code . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
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
			if ( ! $user instanceof User ) {
				throw new NotFoundEntityException('Não foi possível editar o Usuário. Usuário <em>#' . $id . '</em> não encontrado.');
			}
			$this->setAgencySelected($user->getLotation()->getAgency());
			
			$form = $this->createForm($user, new Action($this, 'edit', array('key' => $id)));
			$helper = $this->createHelperCrud();
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
	
	public function changeManagerAction() {
		$this->changeProfile($this->request->getQuery('key'), Manager::getClass());
		$this->forward('/');
	}
	
	public function changeFleetManagerAction() {
		$this->changeProfile($this->request->getQuery('key'), FleetManager::getClass());
		$this->forward('/');
	}
	
	public function changeTrafficControllerAction() {
		$this->changeProfile($this->request->getQuery('key'), TrafficController::getClass());
		$this->forward('/');
	}
	
	public function changeDriverAction() {
		$this->changeProfile($this->request->getQuery('key'), Driver::getClass());
		$this->forward('/');
	}
	
	public function changeRequesterAction() {
		$this->changeProfile($this->request->getQuery('key'), Requester::getClass());
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
		
		$optResultCenter = [];
		$criteria = ['active' => true, 'agency' => $this->getAgencySelected()->getId()];
		$rs = $this->getEntityManager()->getRepository(ResultCenter::getClass())->findBy($criteria);
		foreach ($rs as $result) {
			$optResultCenter[$result->id] = $result->description;
		}
		
		return new UserForm($user, $submit, $seek, $seekAgency, $searchAgency, $seekUnit, $searchUnit, $cancel, $optResultCenter);
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