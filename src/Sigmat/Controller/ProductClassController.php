<?php
namespace Sigmat\Controller;

use Doctrine\ORM\QueryBuilder;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\View\Layout;
use Sigmat\View\Product\ProductClassForm;
use Sigmat\View\Product\ProductClassList;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\View\Product\AttributesForm;
use Sigmat\Model\Product\ProductClass;
use Sigmat\Model\Product\Attribute;
use Sigmat\View\Product\AttributesList;
use Sigmat\View\EntityDatasource;



/**
 * Classe de Produto
 */
class ProductClassController extends AbstractController { 
	
	public function indexAction() {
		$this->session->attributes = null;
		$list = new ProductClassList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $this->createQuery(), array('processQuery' => function( QueryBuilder $query, array $data ) {
				if ( isset($data['description']) ) {
					$query->andWhere($query->expr()->like('u.description', $query->expr()->literal('%' . $data['description'] . '%')));
				}
			}));
			if ( $alert = $this->getAlert() ) {
				$list->setAlert($alert);
			}
		} catch ( \Exception $e ) {
			$list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}
	
	public function newAction() {
		$form = $this->createForm(new Action($this, 'new'));
		try {
			$helper = $this->createHelperCrud();
			if ( $helper->create($form) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Classe de Produto <em>#' . $entity->id . ' ' . $entity->description . '</em> criada com sucesso!', Alert::Success));
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
		$id = $this->request->getQuery('key');
		$form = $this->createForm(new Action($this, 'edit', array('key' => $id)));
		try {
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar a Classe de Produto. Classe de Produto <em>#' . $id . '</em> não encontrada.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Classe de Produto <em>#' . $entity->id . ' ' . $entity->description .  '</em> alterada com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function removeAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível excluir a Classe de Produto. Classe de Produto <em>#' . $id . '</em> não encontrada.'));
			$helper->delete($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Classe de Produto <em>#' . $entity->id . ' ' . $entity->description . '</em> removida com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function seekAttributeAction() {
		try {
			$id = $this->request->getQuery('query');
			$entity = $this->getAttribute($id);
			if ( ! $entity instanceof Attribute ) {
				throw new NotFoundEntityException('Atributo <em>#' . $id . '</em> não encontrado.');	
			}
			return new JsonView(array('attribute-id' => $entity->id, 'attribute-description' => $entity->description, 'flash-message' => null), false);
		} catch ( NotFoundEntityException $e ){
			return new JsonView(array('attribute-description' => '', 'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())), false);
		} catch ( \Exception $e ) {
			return new JsonView(array('attribute-description' => '', 'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error)), false);
		}
	}
	
	public function searchAttributeAction() {
		try {
			$datasource = new EntityDatasource($this->createAttributeQuery());
			$datasource->setPage($this->request->getQuery('page'));
			$widget = new AttributesList(new Action($this, 'search-attribute'));
			$widget->setDataSource($datasource);
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function addAttributeAction() {
		try {
			$id = $this->request->getPost('attribute-id');
			$attributes = $this->session->attributes;
			$entity = $this->getAttribute($id);
			if ( ! $entity instanceof Attribute  ) {
				throw new NotFoundEntityException('Não foi possível adicionar Atributo. Atributo <em>#' . $id . '</em> não encontrado.');	
			}
			$attributes[$id] = $entity;
			$this->session->attributes = $attributes;
			$form = $this->createAttributesForm();
			$json = array($form->getName() => $form, 'flash-message' => null);
		} catch ( NotFoundEntityException $e ) {
			$json = array('flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$json = array('flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new JsonView($json, false);
	}
	
	public function removeAttributeAction() {
		try {
			$id = $this->request->getQuery('key');
			$attributes = $this->session->attributes;
			if ( ! isset($attributes[( int )$id]) ) {
				throw new NotFoundEntityException('Não foi possível remover Atributo. Atributo <em>#' . $id . '</em> não encontrado.');	
			}
			unset($attributes[( int )$id]);
			$this->session->attributes = $attributes;
			$form = $this->createAttributesForm();
			$json = array($form->getName() => $form, 'flash-message' => null);
		} catch ( NotFoundEntityException $e ) {
			$json = array('flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$json = array('flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new JsonView($json, false);
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createQuery() {
		$query = $this->getEntityManager()->getRepository(ProductClass::getClass())->createQueryBuilder('u');
		$query->andWhere($query->expr()->eq('u.status', 1));
		return $query;
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createAttributeQuery() {
		$query = $this->getEntityManager()->getRepository(Attribute::getClass())->createQueryBuilder('u');
		$query->andWhere($query->expr()->eq('u.status', 1));
		return $query;
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		$this->request->setPost(array_merge($this->request->getPost(), $this->session->toArray()));
		return new Crud($this->getEntityManager(), ProductClass::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return ProductClassForm
	 */
	private function createForm ( Action $submit ) {
		return new ProductClassForm($submit, new Action($this), $this->createAttributesForm());
	}
	
	/**
	 * @param integer $id
	 * @return Attribute
	 */
	private function getAttribute( $id ) {
		return $this->getEntityManager()->find(Attribute::getClass(), ( int ) $id);
	}
	
	/**
	 * @return AttributesForm
	 */
	private function createAttributesForm() {
		$add = new Action($this, 'add-attribute');
		$remove = new Action($this, 'remove-attribute');
		$seek = new Action($this, 'seek-attribute');
		$search = new Action($this, 'search-attribute');
		return new AttributesForm($this->session, $add, $remove, $seek, $search);
	}
}
?>