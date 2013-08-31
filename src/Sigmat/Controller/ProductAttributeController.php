<?php
namespace Sigmat\Controller;

use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\View\Product\AttributeList;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\Product\AttributeForm;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Model\Product\Attribute;
use Sigmat\View\Layout;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\View\Product\AttributeOptionsForm;
use Sigmat\Controller\Helper\NotFoundEntityException;
use PHPBootstrap\Mvc\View\JsonView;
use Sigmat\Model\Product\AttributeOption;
use Doctrine\ORM\QueryBuilder;


class ProductAttributeController extends AbstractController {
	
	
	public function indexAction() {
		$this->session->options = null;
		$list = new AttributeList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $this->createQuery(), array('processQuery' => function( QueryBuilder $query, array $data ) {
				if ( isset($data['name']) ) {
					$query->andWhere($query->expr()->like('u.name', $query->expr()->literal('%' . $data['name'] . '%')));
				}
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
				$this->setAlert(new Alert('<strong>Ok! </strong>Atributo <em>#' . $entity->id . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível editar o Atributo. Atributo <em>#' . $id . '</em> não encontrado.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Atributo <em>#' . $entity->id . ' ' . $entity->name .  '</em> alterado com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível excluir o Atributo. Atributo <em>#' . $id . '</em> não encontrado.'));
			$helper->delete($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Atributo <em>#' . $entity->id . ' ' . $entity->name . '</em> removido com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function addOptionAction() {
		try {
			$options = $this->session->options;
			$options[] = new AttributeOption($this->request->getPost('option'));
			$this->session->options = $options;
			$form = $this->createAttributeOptionsForm();
			$json = array($form->getName() => $form, 'flash-message' => null);
		} catch ( NotFoundEntityException $e ) {
			$json = array('flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$json = array('flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new JsonView($json, false);
	}
	
	public function editOptionAction() {
		try {
			$request = $this->getRequest();
			$options = $this->session->options;
			$id = $request->getQuery('key');
			if ( ! isset($options[( int ) $id]) ) {
				throw new NotFoundEntityException('Não foi possível editar a Opção do Atributo. Opção <em>#' . $id . '</em> não encontrada.');
			}
			$entity = $options[( int ) $id];
			$form = $this->createAttributeOptionsForm(new Action($this, 'edit-option', array('key' => ( int ) $id)));
			$form->setData(array('option' => $entity->description, 'options' => $options));
			if ( $request->isPost() ) {
				$form->bind($request->getPost());
				if ( ! $form->valid() ) {
					throw $this->getException(new InvalidRequestDataException());
				}
				$data = $form->getData();
				$entity->setDescription($data['option']);
				$options[( int ) $id] = $entity;
				$this->session->options = $options;
				$form = $this->createAttributeOptionsForm();
			}
			$json = array($form->getName() => $form, 'flash-message' => null);
		} catch ( NotFoundEntityException $e ) {
			$json = array('flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$json = array('flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new JsonView($json, false);
	}
	
	public function cancelOptionAction() {
		$form = $this->createAttributeOptionsForm();
		return new JsonView(array($form->getName() => $form, 'flash-message' => null), false);
	}
	
	public function removeOptionAction() {
		try {
			$request = $this->getRequest();
			$options = $this->session->options;
			$id = $request->getQuery('key');
			if ( ! isset($options[( int ) $id]) ) {
				throw new NotFoundEntityException('Não foi possível remover a Opção do Atributo. Opção <em>#' . $id . '</em> não encontrada.');
			}
			unset($options[( int ) $id]);
			$this->session->options = $options;
			$form = $this->createAttributeOptionsForm();
			$json = array($form->getName() => $form, 'flash-message' => null);
		} catch ( NotFoundEntityException $e ) {
			$json = array('flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$json = array('flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new JsonView($json, false);
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		$this->request->setPost(array_merge($this->request->getPost(), $this->session->toArray()));
		return new Crud($this->getEntityManager(), Attribute::getClass(), $this);
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createQuery() {
		$query = $this->getEntityManager()->getRepository(Attribute::getClass())->createQueryBuilder('u');
		$query->andWhere($query->expr()->eq('u.status', 1));
		return $query;
	}
	
	/**
	 * @param Action $submit
	 * @return AttributeForm
	 */
	private function createForm ( Action $submit ) {
		return new AttributeForm($submit, new Action($this), $this->createAttributeOptionsForm());
	}
	
	/**
	 * @return AttributeOptionsForm
	 */
	private function createAttributeOptionsForm( Action $submit = null ) {
		$editable = true;
		$cancel = new Action($this, 'cancel-option');
		$edit = new Action($this, 'edit-option');
		$remove = new Action($this, 'remove-option');
		if ( $submit === null ) {
			$editable = false;
			$submit = new Action($this, 'add-option');
			$cancel = null;
		}
		$form = new AttributeOptionsForm($this->session, $submit, $edit, $remove, $cancel);
		if ( $editable ) {
			$form->getByName('option-submit')->setLabel('Alterar');
		}
		return $form;
	}
}
?>