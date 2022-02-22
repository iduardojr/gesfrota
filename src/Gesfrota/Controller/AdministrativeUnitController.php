<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\View\AdministrativeUnitForm;
use Gesfrota\View\AdministrativeUnitList;
use Gesfrota\View\AdministrativeUnitTable;
use Gesfrota\View\Layout;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\Widget\PanelQuery;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class AdministrativeUnitController extends AbstractController { 
	
	public function indexAction() {
		$list = new AdministrativeUnitList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'active'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $this->createQuery(), array('limit' => 12, 'sort' => 'lft', 'order' => 'asc', 'processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['name']) ) {
					$query->from(AdministrativeUnit::getClass(), 'p0');
					$query->andWhere('u.lft BETWEEN p0.lft AND p0.rgt');
					$query->andWhere('p0.name LIKE :name OR p0.acronym LIKE :name');
					$query->setParameter('name', '%' . $data['name'] . '%');
				}
				if ( !empty($data['only-active']) ) {
					$query->andWhere('u.active = true');
					$query->andWhere('u.id NOT IN(SELECT p1.id FROM ' . AdministrativeUnit::getClass() . ' p1, ' . AdministrativeUnit::getClass() . ' p2 WHERE p2.active = false AND p1.lft BETWEEN p2.lft AND p2.rgt)');
				}
			}));
			$list->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
			$list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}
	
	
	public function newAction() {
		$id = $this->request->getQuery('key');
		$form = $this->createForm(new Action($this, 'new'));
		try {
			$parent = $this->getEntityManager()->find(AdministrativeUnit::getClass(), ( int ) $id);
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, new AdministrativeUnit($this->getAgencyActive(), $parent)) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Unidade Administrativa <em>#' . $entity->code . ' ' . $entity->fullDescription . '</em> criada com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
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
			$helper->setException(new NotFoundEntityException('Não foi possível editar a Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não encontrada.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Unidade Administrativa <em>#' . $entity->code . ' ' . $entity->fullDescription .  '</em> alterada com sucesso!', Alert::Success));
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
	
	public function activeAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar a Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não encontrada.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Unidade Administrativa <em>#' . $entity->code . ' ' . $entity->fullDescription . '</em> ' . ( $entity->active ? 'ativada' : 'desativada' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function searchAction() {
		try {
			$query = $this->createQuery();
			$params = $this->request->getQuery();
			if ( $params['key'] > 0 ) {
				$query->from(AdministrativeUnit::getClass(), 'p');
				$query->andWhere('u.lft NOT BETWEEN p.lft AND p.rgt');
				$query->andWhere('p.id = :parent');
				$query->setParameter('parent', (int) $params['key']);
			}
			if ( $params['query'] ) {
				$query->from(AdministrativeUnit::getClass(), 'p0');
				$query->andWhere('u.lft BETWEEN p0.lft AND p0.rgt');
				$query->andWhere('p0.name LIKE :name');
				$query->setParameter('name', '%' . $params['query'] . '%');
			}
			$datasource = new EntityDatasource($query);
			$datasource->setOrderBy('lft', 'ASC');
			$datasource->setPage($params['page']);
			$table = new AdministrativeUnitTable(new Action($this,'search', $params));
			$table->setDataSource($datasource);
			$widget = new PanelQuery($table, new Action($this,'search', $params), $params['query']);
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function seekAction() {
		try {
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->find(AdministrativeUnit::getClass(), (int) $id);
			if ( ! $entity ) {
				throw new NotFoundEntityException('Unidade Administrativa <em>#' . $id . '</em> não encontrada.');
			}
			return new JsonView(array('administrative-unit-id' => $entity->code, 'administrative-unit-description' => $entity->fullDescription, 'flash-message' => null), false);
		} catch ( NotFoundEntityException $e ){
			return new JsonView(array('administrative-unit-description' => '', 'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())), false);
		} catch ( \Exception $e ) {
			return new JsonView(array('administrative-unit-description' => '', 'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error)), false);
		}
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createQuery() {
		$query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
		$query->distinct(true);
		$query->andWhere('u.agency = :agency');
		$query->setParameter('agency', $this->getAgencyActive()->getId());
		$query->orderBy('u.lft');
		return $query;
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		$this->request->setPost(array_merge($this->request->getPost(), array('agency' => $this->getAgencyActive())));
		return new Crud($this->getEntityManager(), AdministrativeUnit::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return AdministrativeUnitForm
	 */
	private function createForm ( Action $submit ) {
		return new AdministrativeUnitForm($submit, new Action($this, 'seek'), new Action($this, 'search', $submit->getParameters()), new Action($this));
	}

}
?>