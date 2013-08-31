<?php
namespace Sigmat\Controller;

use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\Layout;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\View\Product\ProductList;
use Sigmat\Model\Product\Product;
use Sigmat\View\Product\ProductForm;
use Sigmat\View\Product\ProductNewForm;
use Sigmat\Model\Product\ProductClass;
use PHPBootstrap\Mvc\View\JsonView;
use Doctrine\ORM\EntityNotFoundException;
use Sigmat\Model\Product\Category;
use Sigmat\View\Product\CategoryTree;

/**
 * Produto
 */
class ProductController extends AbstractController {
	
	public function indexAction() {
		$list = new ProductList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $this->createQuery(), array('limit' => null));
			$list->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
			$list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}
	
	public function newAction() {
		$request = $this->request;
		$id = $request->getQuery('key');
		if ( $id !== null ) {
			try {
				$class = $this->getEntityManager()->find(ProductClass::getClass(), ( int ) $id);
				if ( ! $class instanceof ProductClass ) {
					throw new EntityNotFoundException('Não foi possível criar o um novo Produto. Classe de Produto <em>#' . $id . '</em> não encontrada.');
				}
				$form = $this->createForm(new Action($this, 'new', array('key' => $id)));
				$helper = $this->createHelperCrud();
				if ( $helper->create($form, new Product($class)) ){
					$entity = $helper->getEntity();
					$this->setAlert(new Alert('<strong>Ok! </strong>Produto <em>#' . $entity->id . ' ' . $entity->description . '</em> criado com sucesso!', Alert::Success));
					$this->forward('/');
				}
			} catch ( NotFoundEntityException $e ) {
				$form = $this->createNewForm();
				$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			} catch ( InvalidRequestDataException $e ) {
				$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			} catch ( \Exception $e ) {
				$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
			}
		} else {
			$form = $this->createNewForm();
			if ( $request->isPost() ) {
				$id = $request->getPost('product-class');
				$this->redirect(new Action($this, 'new', array('key' => $id)));
			}
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
				$this->setAlert(new Alert('<strong>Ok! </strong>Produto <em>#' . $entity->id . ' ' . $entity->description .  '</em> alterado com sucesso!', Alert::Success));
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
	
	public function removeAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível excluir o Produto. Produto <em>#' . $id . '</em> não encontrado.'));
			$helper->delete($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Produto <em>#' . $entity->id . ' ' . $entity->description . '</em> removido com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function seekCategoryAction() {
		try {
			$id = $this->request->getQuery('query');
			$entity = $this->getCategory($id);
			if ( ! $entity instanceof Category ) {
				throw new NotFoundEntityException('Categoria <em>#' . $id . '</em> não encontrada.');
			}
			return new JsonView(array('category-id' => $entity->id, 'category-description' => $entity->description, 'flash-message' => null), false);
		} catch ( NotFoundEntityException $e ){
			return new JsonView(array('category-description' => '', 'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())), false);
		} catch ( \Exception $e ) {
			return new JsonView(array('category-description' => '', 'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error)), false);
		}
	}
	
	public function searchCategoryAction() {
		try {
			$repository = $this->getEntityManager()->getRepository(Category::getClass());
			$widget = new CategoryTree($repository->findBy(array('parent' => null)));
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function searchClassAction() {
		$term = $this->getRequest()->getQuery('query');
		$items = $this->getRequest()->getQuery('items');
		$query = $this->getEntityManager()->getRepository(ProductClass::getClass())->createQueryBuilder('u');
		$query->andWhere($query->expr()->like('u.description', $query->expr()->literal('%' . $this->sanitize($term) . '%')))
			  ->addOrderBy('u.description', 'asc')
			  ->setMaxResults($items);
		$collection = $query->getQuery()->getResult();
		$response = array();
		foreach( $collection as $entity )	{
			$item['label'] = $entity->getDescription();
			$item['value']['product-class'] = $entity->getId();
			$response[] = $item;
		}
		return new JsonView($response, false);
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), Product::getClass(), $this);
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createQuery() {
		$query = $this->getEntityManager()->getRepository(Product::getClass())->createQueryBuilder('u');
		$query->andWhere($query->expr()->eq('u.status', 1));
		return $query;
	}
	
	/**
	 * @param Action $submit
	 * @return ProductForm
	 */
	private function createForm ( Action $submit ) {
		return new ProductForm($submit, new Action($this), new Action($this, 'seek-category'), new Action($this, 'search-category'));
	}
	
	/**
	 * @return ProductNewForm
	 */
	private function createNewForm() {
		return new ProductNewForm(new Action($this, 'new'), new Action($this, 'search-class'));
	}
	
	/**
	 * @param integer $id
	 * @return Category
	 */
	private function getCategory( $id ) {
		return $this->getEntityManager()->find(Category::getClass(), ( int ) $id);
	}
}
?>