<?php
namespace Sigmat\Controller;

use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\View\Layout;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\View\Product\CategoryList;
use Sigmat\Model\Product\Category;
use Sigmat\View\Product\CategoryForm;

/**
 * Categorias de Produto
 */
class ProductCategoryController extends AbstractController { 
	
	public function indexAction() {
		$list = new CategoryList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $this->createQuery(), array('limit' => null, 'sort' => 'name', 'order' => 'asc'));
			$list->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
			$list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}
	
	public function newAction() {
		$id = $this->request->getQuery('key');
		$form = new CategoryForm(new Action($this, 'new', array('key' => $id)), new Action($this));
		try {
			$parent = $this->getEntityManager()->find(Category::getClass(), ( int ) $id);
			if ( $id > 0 && $parent === null ) {
				throw new NotFoundEntityException('Não foi possível criar uma nova Categoria. Categoria Superior <em>#'. $id .'</em> não encontrada.');
			}
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, new Category($parent)) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Categoria <em>#' . $entity->id . ' ' . $entity->description . '</em> criada com sucesso!', Alert::Success));
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
		$form = new CategoryForm(new Action($this, 'edit', array('key' => $id)), new Action($this));
		try {
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar a Categoria. Categoria <em>#' . $id . '</em> não encontrada.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Categoria <em>#' . $entity->id . ' ' . $entity->description .  '</em> alterada com sucesso!', Alert::Success));
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
	
	public function removeAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível excluir a Categoria. Categoria <em>#' . $id . '</em> não encontrada.'));
			$helper->delete($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Categoria <em>#' . $id . ' ' . $entity->description . '</em> removida com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function updateAction() {
		try {
			$id = $this->request->getQuery('key');
			$parentId = $this->request->getQuery('parent');
			$entity = $this->getEntityManager()->find(Category::getClass(), ( int ) $id);
			$parent = $this->getEntityManager()->find(Category::getClass(), ( int ) $parentId);
			if ( empty($entity) ) {
				throw new NotFoundEntityException('Não foi possível atualizar a Categoria. Categoria <em>#' . $id . '</em> não encontrada.');
			}
			if ( $parentId > 0 && empty($parent) ) {
				throw new NotFoundEntityException('Não foi possível atualizar a Categoria. Categoria Superior <em>#' . $parentId . '</em> não encontrada.');
			}
			$entity->setParent($parent);
			$this->getEntityManager()->flush();
			$json = array('success' => true);
		} catch ( NotFoundEntityException $e ){
			$json = array('success' => false, 'message' => new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$json = array('success' => false, 'message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new JsonView($json, false);
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createQuery() {
		$query = $this->getEntityManager()->getRepository(Category::getClass())->createQueryBuilder('u');
		$query->leftJoin('u.parent', 'a');
		$query->add('where', 'a.id IS NULL');
		return $query;
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), Category::getClass(), $this);
	}

}
?>