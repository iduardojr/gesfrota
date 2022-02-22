<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\View\DriverForm;
use Gesfrota\View\DriverList;
use Gesfrota\View\Layout;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\AdministrativeUnitTable;
use Gesfrota\View\Widget\PanelQuery;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Misc\Title;
use Gesfrota\Model\Domain\User;

class DriverController extends AbstractController {
	
	public function indexAction() {
		$filter = new Action($this);
		$new = new Action($this, 'new');
		$edit = new Action($this, 'edit');
		$active = new Action($this, 'active');
		$search = new Action($this, 'search');
		$transfer = new Action($this, 'transfer');
		$reset = new Action($this, 'resetPassword');
		
		$list = new DriverList($filter, $new, $edit, $active, $search, $transfer, $reset);
		try {
			$helper = $this->createHelperCrud();
			$query = $this->getEntityManager()->createQueryBuilder();
			$query->select('u');
			$query->from(Driver::getClass(), 'u');
			$query->join('u.lotation', 'l');
			$query->where('l.agency = :agency');
			$query->setParameter('agency', $this->getAgencyActive()->getId());
			$helper->read($list, $query, array('limit' => 12, 'processQuery' => function( QueryBuilder $query, array $data ) {
				
				if ( !empty($data['name']) ) {
					$query->andWhere('u.name LIKE :name');
					$query->setParameter('name', '%' . $data['name'] . '%');
				}
				
				if ( !empty($data['nif']) ) {
					$query->andWhere('u.nif = :nif');
					$query->setParameter('nif', $data['nif']);
				}
				
				if ( !empty($data['vehicles']) ) {
					$subquery = $this->getEntityManager()->createQueryBuilder('u2');
					$subquery->from(Driver::getClass(), 'u2');
					$subquery->select('u2');
					foreach($data['vehicles'] as $key => $val) {
						$subquery->orWhere('u.vehicles LIKE :vehicles' . $key);
						$query->setParameter('vehicles' . $key, '%' . $val . '%');
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
		$form = $this->createForm(new Action($this, 'new'));
		try {
			$helper = $this->createHelperCrud();
			$nif = $this->getRequest()->getPost('nif');
			$entity = $this->getEntityManager()->getRepository(User::getClass())->findOneBy(['nif' => $nif]);
			if ( $entity instanceof User) {
				throw new \DomainException($entity->getUserType() .' <em>' . $entity->getName() . ' (CPF' . $entity->getNif() . ')</em> já está registrado em '. $entity->getLotation()->getAgency()->getAcronym());
			}
			if ( $helper->create($form, new Driver($this->getAgencyActive())) ){
				$entity = $helper->getEntity();
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
		$id = $this->request->getQuery('key');
		$form = $this->createForm(new Action($this, 'edit', array('key' => $id)));
		try {
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar o Motorista. Motorista <em>#' . $id . '</em> não encontrado.'));
			if ( $helper->update($form, $id) ) {
				$entity = $helper->getEntity();
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
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar o Motorista. Motorista <em>#' . $id . '</em> não encontrado.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Motorista <em>#' . $entity->code . ' ' . $entity->name . '</em> ' . ( $entity->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
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
			$entity = $this->getEntityManager()->getRepository(Driver::getClass())->findOneBy(['nif' => $nif]);
			if ( $entity === null ) {
				throw new \DomainException('Motorista <em>CPF ' . $nif . '</em> não encontrado.');
			}
			$entity->setLotation($this->getUserActive()->getLotation());
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
			$entity = $this->getEntityManager()->find(Driver::getClass(), $id);
			if (! $entity instanceof Driver) {
				throw new NotFoundEntityException('Não foi possível redefinir a senha do Motorista. Motorista <em>#' . $id . '</em> não encontrado.');
			}
			$entity->setPassword(null);
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
			$entity = $this->getEntityManager()->getRepository(Driver::getClass())->findOneBy(['nif' => $nif]);
			if (! $entity instanceof Driver) {
				throw new \DomainException('Motorista <em>CPF ' . $nif . '</em> não encontrado.');
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
	
	public function seekUnitAction() {
		try {
			$data['administrative-unit-id'] = null;
			$data['administrative-unit-name'] = null;
			$data['flash-message'] = null;
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->findOneBy(['id' => $id, 'active' => true, 'agency' => $this->getAgencyActive()->getId()]);
			if ( (! $entity instanceof AdministrativeUnit)) {
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
			$query->setParameter('agency', $this->getAgencyActive()->getId());
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
		return new Crud($this->getEntityManager(), Driver::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return DriverForm
	 */
	private function createForm ( Action $submit ) {
		return new DriverForm($this->getAgencyActive(), $submit, new Action($this, 'seek'), new Action($this, 'seekUnit'), new Action($this, 'searchUnit'), new Action($this));
	}
	
}
?>