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
		$form = new AdministrativeUnitForm(new Action($this, 'new', array('key' => $id)), new Action($this));
		try {
			$parent = $this->getEntityManager()->find(AdministrativeUnit::getClass(), ( int ) $id);
			if ( $parent === null ) {
				throw new NotFoundEntityException('Não foi possível criar uma nova Unidade Administrativa. Unidade Superior <em>#'. $this->request->getQuery('key') .'</em> não encontrada.');
			}
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, new AdministrativeUnit($parent)) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Unidade Administrativa <em>#' . $entity->id . ' ' . $entity->name . '</em> criada com sucesso!', Alert::Success));
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
		$form = new AdministrativeUnitForm(new Action($this, 'edit', array('key' => $id)), new Action($this));
		try {
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar a Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não encontrada.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Unidade Administrativa <em>#' . $entity->id . ' ' . $entity->name .  '</em> alterada com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível excluir a Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não encontrada.'));
			$helper->delete($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Unidade Administrativa <em>#' . $entity->id . ' ' . $entity->name . '</em> removida com sucesso!', Alert::Success));
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
			$entity = $this->getEntityManager()->find(AdministrativeUnit::getClass(), ( int ) $id);
			$parent = $this->getEntityManager()->find(AdministrativeUnit::getClass(), ( int ) $parentId);
			if ( empty($entity) ) {
				throw new NotFoundEntityException('Não foi possível atualizar a Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não encontrada.');
			}
			if ( empty($parent) ) {
				throw new NotFoundEntityException('Não foi possível atualizar a Unidade Administrativa. Unidade Superior <em>#' . $parentId . '</em> não encontrada.');
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
		return new Crud($this->getEntityManager(), AdministrativeUnit::getClass(), $this);
	}
}
?>