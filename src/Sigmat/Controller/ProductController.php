<?php
namespace Sigmat\Controller;

use Doctrine\ORM\QueryBuilder;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\GUI\Layout;
use Sigmat\View\GUI\PanelQuery;
use Sigmat\View\GUI\EntityDatasource;
use Sigmat\View\ProductForm;
use Sigmat\View\ProductList;
use Sigmat\View\ProductUnitsForm;
use Sigmat\View\ProductCategoryTable;
use Sigmat\View\ProductUnitTable;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\Model\Domain\Product;
use Sigmat\Model\Domain\ProductCategory;
use Sigmat\Model\Domain\ProductUnit;

/**
 * Produto
 */
class ProductController extends AbstractController {
	
	public function indexAction() {
		$this->session->units = null;
		$list = new ProductList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'active'), new Action($this, 'search-ProductCategory'), new Action($this, 'seek-ProductCategory'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $this->createQuery(), array('limit' => 12, 'processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['description']) ) {
					$query->andWhere('u.description LIKE :description');
					$query->setParameter('description', '%' . $data['description'] . '%');
				}
				if ( !empty($data['product-category-id']) ) {
					$em = $query->getEntityManager();
					$ProductCategory = $em->find(ProductCategory::getClass(), (int) $data['product-category-id']);
					$query->andWhere('c.lft BETWEEN :lowerbound AND :upperbound');
					$query->setParameter('lowerbound', (int) $ProductCategory->getLft());
					$query->setParameter('upperbound', (int) $ProductCategory->getRgt());
				}
				if ( !empty($data['only-active']) ) {
					$query->andWhere('u.active = true');
				}
			}));
			$list->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
			$list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(). nl2br($e->getTraceAsString()), Alert::Danger));
		}
		return new Layout($list);
	}
	
	public function newAction() {
		try {
			$form = $this->createForm(new Action($this, 'new'));
			$helper = $this->createHelperCrud();
			if ( $helper->create($form) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Produto <em>#' . $entity->code . ' ' . $entity->description . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ) {
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
			$helper->setException(new NotFoundEntityException('Não foi possível editar o Produto. Produto <em>#' . $id . '</em> não encontrado.'));
			if ( $helper->update($form, $id) ) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Produto <em>#' . $entity->code . ' ' . $entity->description .  '</em> alterado com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar o Produto. Produto <em>#' . $id . '</em> não encontrado.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Produto <em>#' . $entity->code . ' ' . $entity->description . '</em> ' . ( $entity->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function searchCategoryAction() {
		try {
			$query = $this->createQueryCategory();
			$params = $this->request->getQuery();
			if ( $params['query'] ) {
				$query->from(ProductCategory::getClass(), 'p0');
				$query->andWhere('u.lft BETWEEN p0.lft AND p0.rgt');
				$query->andWhere('p0.description LIKE :description');
				$query->setParameter('description', '%' . $params['query'] . '%');
			}
			if ( $params['only-active'] ) { 
				$query->andWhere('u.active = true');
				$query->andWhere('u.id NOT IN(SELECT p1.id FROM ' . ProductCategory::getClass() . ' p1, ' . ProductCategory::getClass() . ' p2 WHERE p2.active = false AND p1.lft BETWEEN p2.lft AND p2.rgt)');
				$query->andWhere('u.lft + 1 = u.rgt');
			}
			$datasource = new EntityDatasource($query);
			$datasource->setPage($params['page']);
			$datasource->setOrderBy('lft', 'ASC');
			$table = new ProductCategoryTable(new Action($this,'search-product-category', $params));
			$table->setDataSource($datasource);
			$widget = new PanelQuery($table, new Action($this,'search-product-category', $params), $params['query']);
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function seekCategoryAction() {
		try {
			$id = $this->request->getQuery('query');
			
			$query = $this->createQueryCategory();
			if ( $this->request->getQuery('only-active') ) { 
				$query->andWhere('u.active = true');
				$query->andWhere('u.id NOT IN(SELECT p1.id FROM ' . ProductCategory::getClass() . ' p1, ' . ProductCategory::getClass() . ' p2 WHERE p2.active = false AND p1.lft BETWEEN p2.lft AND p2.rgt)');
				$query->andWhere('u.lft + 1 = u.rgt');
			}
			$query->andWhere('u.id = :entity');
			$query->setParameter('entity', (int) $id);
			$entity = $query->getQuery()->getOneOrNullResult();
			
			if ( !$entity ) {
				throw new NotFoundEntityException('Categoria <em>#' . $id . '</em> não encontrada.');
			}
			return new JsonView(array('product-ProductCategory-id' =>  $entity->code, 'product-category-description' => $entity->fullDescription, 'flash-message' => null), false);
		} catch ( NotFoundEntityException $e ){
			return new JsonView(array('product-category-description' => '', 'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())), false);
		} catch ( \Exception $e ) {
			return new JsonView(array('product-category-description' => '', 'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error)), false);
		}
	}
	
	public function addUnitAction() {
		try {
			$id = (int) $this->request->getPost('product-unit-id');
			$units = $this->session->units;
			$entity = $this->getEntityManager()->find(ProductUnit::getClass(), ( int ) $id);
			if ( ! $entity ) {
				throw new NotFoundEntityException('Não foi possível adicionar Unidade de Medida. Unidade de Medida <em>#' . $id . '</em> não encontrada.');
			}
			$units[$id] = $entity;
			$this->session->units = $units;
			$form = $this->createUnitsForm();
			$json = array($form->getName() => $form, 'flash-message' => null);
		} catch ( NotFoundEntityException $e ) {
			$json = array('flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$json = array('flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new JsonView($json, false);
	}
	
	public function removeUnitAction() {
		try {
			$id = $this->request->getQuery('key');
			$units = $this->session->units;
			if ( ! isset($units[(int)$id]) ) {
				throw new NotFoundEntityException('Não foi possível remover Unidade de Medida. Unidade de Medida <em>#' . $id . '</em> não encontrada.');
			}
			unset($units[(int)$id]);
			$this->session->units = $units;
			$form = $this->createUnitsForm();
			$json = array($form->getName() => $form, 'flash-message' => null);
		} catch ( NotFoundEntityException $e ) {
			$json = array('flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$json = array('flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new JsonView($json, false);
	}
	
	public function searchUnitAction() {
		try {
			$query = $this->createQueryUnit();
			$params = $this->request->getQuery();
			$datasource = new EntityDatasource($query);
			$datasource->setPage($params['page']);
			$datasource->setOrderBy('description', 'ASC');
			$widget = new ProductUnitTable(new Action($this,'search'));
			$widget->setDataSource($datasource);
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function seekUnitAction() {
		try {
			$id = $this->request->getQuery('query');
				
			$query = $this->createQueryUnit();
			$query->andWhere('u.id = :entity');
			$query->setParameter('entity', (int) $id);
			$entity = $query->getQuery()->getOneOrNullResult();
			if ( ! $entity ) {
				throw new NotFoundEntityException('Unidade de Medida <em>#' . $id . '</em> não encontrada.');
			}
			return new JsonView(array('product-unit-id' => $entity->code, 'product-unit-description' => $entity->description, 'flash-message' => null), false);
		} catch ( NotFoundEntityException $e ){
			return new JsonView(array('product-unit-description' => '', 'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())), false);
		} catch ( \Exception $e ) {
			return new JsonView(array('product-unit-description' => '', 'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error)), false);
		}
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		$this->request->setPost(array_merge($this->request->getPost(), $this->session->toArray()));
		return new Crud($this->getEntityManager(), Product::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return ProductForm
	 */
	private function createForm ( Action $submit ) {
		$seek = new Action($this, 'seek-category', array('only-active' => true));
		$search = new Action($this, 'search-category', array('only-active' => true));
		$cancel = new Action($this);
		return new ProductForm($submit, $search, $seek, $cancel, $this->createUnitsForm());
	}
	
	/**
	 * @return ProductUnitsForm
	 */
	private function createUnitsForm () {
		$add = new Action($this, 'add-unit');
		$remove = new Action($this, 'remove-unit');
		$seek = new Action($this, 'seek-unit');
		$search = new Action($this, 'search-unit');
		return new ProductUnitsForm($add, $remove, $seek, $search, $this->session);
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createQuery() {
		$query = $this->getEntityManager()->getRepository(Product::getClass())->createQueryBuilder('u');
		$query->leftJoin('u.category', 'c');
		return $query;
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createQueryUnit() {
		$query = $this->getEntityManager()->getRepository(ProductUnit::getClass())->createQueryBuilder('u');
		$query->andWhere('u.active = true');
		return $query;
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createQueryCategory() {
		$query = $this->getEntityManager()->getRepository(ProductCategory::getClass())->createQueryBuilder('u');
		$query->leftJoin('u.parent', 'a');
		return $query;
	}
	
}
?>