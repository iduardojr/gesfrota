<?php
namespace Sigmat\Controller;

use Doctrine\ORM\QueryBuilder;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\View\GUI\Layout;
use Sigmat\View\GUI\PanelQuery;
use Sigmat\View\GUI\EntityDatasource;
use Sigmat\View\ProductCategoryList;
use Sigmat\View\ProductCategoryForm;
use Sigmat\View\ProductCategoryTable;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\Model\Domain\ProductCategory;

class ProductCategoryController extends AbstractController { 
	
	
	public function indexAction() {
		try {
			$id = $this->request->getQuery('key');
			$list = new ProductCategoryList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'active'));
			$helper = $this->createHelperCrud();
			$helper->read($list, null, array('limit' => 12, 'sort' => 'lft', 'order' => 'asc', 'processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['description']) ) {
					$query->from(ProductCategory::getClass(), 'p0');
					$query->andWhere('u.lft BETWEEN p0.lft AND p0.rgt');
					$query->andWhere('p0.description LIKE :description');
					$query->setParameter('description', '%' . $data['description'] . '%');
				}
				if ( !empty($data['only-active']) ) {
					$query->andWhere('u.active = true');
					$query->andWhere('u.id NOT IN(SELECT p1.id FROM ' . ProductCategory::getClass() . ' p1, ' . ProductCategory::getClass() . ' p2 WHERE p2.active = false AND p1.lft BETWEEN p2.lft AND p2.rgt)');
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
			$id = $this->request->getQuery('key');
			$params = $id > 0 ? array('key' => $id) : array();
			$form = $this->createForm(new Action($this, 'new', $params));
			$helper = $this->createHelperCrud();
			$parent = $this->getEntityManager()->find(ProductCategory::getClass(), ( int ) $id);
			if ( $helper->create($form, new ProductCategory($parent)) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Categoria <em>#' . $entity->code . ' ' . $entity->fullDescription . '</em> criada com sucesso!', Alert::Success));
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
		try {
			$form = $this->createForm(new Action($this, 'edit', array('key' => $id)));
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar a Categoria. Categoria <em>#' . $id . '</em> não encontrada.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Categoria <em>#' . $entity->code . ' ' . $entity->fullDescription .  '</em> alterada com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar a Categoria. Categoria <em>#' . $id . '</em> não encontrada.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Categoria <em>#' . $entity->code . ' ' . $entity->fullDescription . '</em> ' . ( $entity->active ? 'ativada' : 'desativada' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function searchAction() {
		try {
			$query = $this->getEntityManager()->getRepository(ProductCategory::getClass())->createQueryBuilder('u');
			$params = $this->request->getQuery();
			if ( $params['key'] > 0 ) {
				$query->from(ProductCategory::getClass(), 'p0');
				$query->andWhere('u.lft NOT BETWEEN p0.lft AND p0.rgt');
				$query->andWhere('p0.id = :parent');
				$query->setParameter('parent', (int)  $params['key']);
			}
			if ( $params['query'] ) {
				$query->from(ProductCategory::getClass(), 'p1');
				$query->andWhere('u.lft BETWEEN p1.lft AND p1.rgt');
				$query->andWhere('p1.description LIKE :description');
				$query->setParameter('description', '%' . $params['query'] . '%');
			}
			$datasource = new EntityDatasource($query);
			$datasource->setPage($params['page']);
			$datasource->setOrderBy('lft', 'ASC');
			$table = new ProductCategoryTable(new Action($this,'search', $params));
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
			$entity = $this->getEntityManager()->find(ProductCategory::getClass(), ( int ) $id);
			if ( !$entity ) {
				throw new NotFoundEntityException('Categoria <em>#' . $id . '</em> não encontrada.');
			}
			return new JsonView(array('product-ProductCategory-id' =>  $entity->code, 'product-ProductCategory-description' => $entity->fullDescription, 'flash-message' => null), false);
		} catch ( NotFoundEntityException $e ){
			return new JsonView(array('product-ProductCategory-description' => '', 'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())), false);
		} catch ( \Exception $e ) {
			return new JsonView(array('product-ProductCategory-description' => '', 'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error)), false);
		}
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), ProductCategory::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return CategoryForm
	 */
	private function createForm ( Action $submit ) {
		$search = new Action($this, 'search', $submit->getParameters());
		$seek = new Action($this, 'seek');
		$cancel = new Action($this);
		return new ProductCategoryForm($submit, $search, $seek, $cancel);
	}
	
}
?>