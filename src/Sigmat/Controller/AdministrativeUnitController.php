<?php
namespace Sigmat\Controller;

use Sigmat\Controller\Helper\Crud;
use Sigmat\View\Layout;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\View\AdministrativeUnit\AdministrativeUnitList;
use Sigmat\Model\AdministrativeUnit\AdministrativeUnit;
use Sigmat\View\AdministrativeUnit\AdministrativeUnitForm;
use PHPBootstrap\Mvc\View\JsonView;

/**
 * Unidade Administrativa
 */
class AdministrativeUnitController extends AbstractController { 
	
	public function indexAction() {
		$list = new AdministrativeUnitList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
		$helper = $this->createHelperCrud();
		$cookie = $helper->read($list, $this->createQuery(), array('limit' => null, 'sort' => 'name', 'order' => 'asc'));
		$this->response->setCookie($cookie);
		if ( $this->session->alert ) {
			$list->setAlert($this->session->alert);
			$this->session->alert = null;
		}
		return new Layout($list);
	}
	
	public function newAction() {
		try {
			$parent = $this->getEntityManager()->find(AdministrativeUnit::getClass(), ( int ) $this->request->getQuery('key'));
			if ( $parent === null ) {
				throw new \ErrorException('Não existe uma unidade superior');
			}
			$form = new AdministrativeUnitForm(new Action($this, 'new', array('key' => $parent->getId())), new Action($this), $parent);
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, new AdministrativeUnit($parent)) ){
				$entity = $helper->getEntity();
				$this->session->alert = new Alert('<strong>Ok!</strong> Unidade Administrativa <em>#' . $entity->id . ' ' . $entity->name . '</em> criada com sucesso!', Alert::Success);
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
				
		} catch ( \ErrorException $e ) {
			$this->session->alert = new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger);
			$this->forward('/');
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function editAction() {
		try {
			$id = ( int ) $this->request->getQuery('key'); 
			$form = new AdministrativeUnitForm(new Action($this, 'edit', array('key' => $id)), new Action($this));
			$helper = $this->createHelperCrud();
			if ( $helper->update($form, ( int ) $id) ){
				$entity = $helper->getEntity();
				$this->session->alert = new Alert('<strong>Ok!</strong> Unidade Administrativa <em>#' . $entity->id . ' ' . $entity->name .  '</em> alterada com sucesso!', Alert::Success);
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel editar a Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não foi encontrada');
		} catch ( InvalidRequestDataException $e ){
				
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function removeAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$helper->delete( ( int ) $id);
			$entity = $helper->getEntity();
			$this->session->alert = new Alert('<strong>Ok!</strong> Unidade Administrativa <em>#' . $entity->id . ' ' . $entity->name . '</em> removida com sucesso!', Alert::Success);
		} catch ( NotFoundEntityException $e ){
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel excluir a Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não foi encontrada');
		} catch ( \Exception $e ) {
			$this->session->alert = new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger);
		}
		$this->forward('/');
	}
	
	public function updateAction() {
		try {
			$id = ( int ) $this->request->getQuery('key');
			$parentId = ( int ) $this->request->getQuery('parent');
			$entity = $this->getEntityManager()->find(AdministrativeUnit::getClass(), $id);
			$parent = $this->getEntityManager()->find(AdministrativeUnit::getClass(), $parentId);
			if ( empty($entity) ) {
				throw new \Exception('<strong>Ops!</strong> Não foi possivel atualizar a Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não foi encontrada');
			}
			if ( empty($parent) ) {
				throw new \Exception('<strong>Ops!</strong> Não foi possivel atualizar a Unidade Administrativa. Unidade Administrativa <em>#' . $parentId . '</em> não foi encontrada');
			}
			$entity->setParent($parent);
			$this->getEntityManager()->flush();
			return new JsonView(array('success' => true));
		} catch ( \Exception $e ) {
			return new JsonView(array('success' => false, 'message' => new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger)));
		}
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createQuery() {
		$query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
		$query->leftJoin('u.parent', 'a');
		$query->andWhere($query->expr()->eq('u.status', 1));
		$query->andWhere($query->expr()->eq('u.id', 1));
		return $query;
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), AdministrativeUnit::getClass(), $this->getRequest());
	}
}
?>