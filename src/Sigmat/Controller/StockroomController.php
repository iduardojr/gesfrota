<?php
namespace Sigmat\Controller;

use Doctrine\ORM\QueryBuilder;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\View\Layout;
use Sigmat\View\Stockroom\StockroomForm;
use Sigmat\View\Stockroom\StockroomList;
use Sigmat\View\AdministrativeUnit\AdministrativeUnitTree;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\Model\Stockroom\Stockroom;
use Sigmat\Model\AdministrativeUnit\Agency;
use Sigmat\Model\AdministrativeUnit\AdministrativeUnit;
use Sigmat\View\Stockroom\RequestersUnitsForm;


/**
 * Almoxarifado
 */
class StockroomController extends AbstractController { 
	
	public function indexAction() {
		$this->session->units = null;
		$list = new StockroomList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
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
		$form = $this->createForm(new Action($this, 'new'));
		try {
			$agency = $this->getAgency();
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, new Stockroom($agency)) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Almoxarifado <em>#' . $entity->id . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível editar o Almoxarifado. Almoxarifado <em>#' . $id . '</em> não encontrado.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Almoxarifado <em>#' . $entity->id . ' ' . $entity->name .  '</em> alterado com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível excluir o Almoxarifado. Almoxarifado <em>#' . $id . '</em> não encontrado.'));
			$helper->delete($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Almoxarifado <em>#' . $entity->id . ' ' . $entity->name . '</em> removido com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function seekUnitAction() {
		try {
			$id = $this->request->getQuery('query');
			$entity = $this->getUnit($id);
			if ( ! $entity instanceof AdministrativeUnit || ( $entity instanceof Agency ) ) {
				throw new NotFoundEntityException('Unidade Administrativa <em>#' . $id . '</em> não encontrada.');	
			}
			return new JsonView(array('unit-id' => $entity->id, 'unit-name' => $entity->name, 'flash-message' => null), false);
		} catch ( NotFoundEntityException $e ){
			return new JsonView(array('unit-name' => '', 'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())), false);
		} catch ( \Exception $e ) {
			return new JsonView(array('unit-name' => '', 'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error)), false);
		}
	}
	
	public function searchUnitAction() {
		try {
			$widget = new AdministrativeUnitTree($this->getAgency());
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function addUnitAction() {
		try {
			$id = $this->request->getPost('unit-id');
			$units = $this->session->units;
			$entity = $this->getUnit($id);
			if ( ! $entity instanceof AdministrativeUnit || ( $entity instanceof Agency )  ) {
				throw new NotFoundEntityException('Não foi possível adicionar Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não encontrada.');	
			}
			$units[$id] = $entity;
			$this->session->units = $units;
			$form = $this->createRequestersUnitsForm();
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
				throw new NotFoundEntityException('Não foi possível remover Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não encontrada.');	
			}
			unset($units[(int)$id]);
			$this->session->units = $units;
			$form = $this->createRequestersUnitsForm();
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
		$query = $this->getEntityManager()->getRepository(Stockroom::getClass())->createQueryBuilder('u');
		$query->leftJoin('u.agency', 'a');
		$query->andWhere($query->expr()->eq('a.id', $this->getAgency()->getId()));
		return $query;
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		$this->request->setPost(array_merge($this->request->getPost(), $this->session->toArray()));
		return new Crud($this->getEntityManager(), Stockroom::getClass(), $this);
	}
	
	/**
	 * @return Agency
	 */
	private function getAgency() {
		return $this->getUnit(1);
	}
	
	/**
	 * @param integer $id
	 * @return AdministrativeUnit
	 */
	private function getUnit( $id ) {
		return $this->getEntityManager()->find(AdministrativeUnit::getClass(), ( int ) $id);
	}
	
	/**
	 * @param Action $submit
	 * @return StockroomForm
	 */
	private function createForm ( Action $submit ) {
		return new StockroomForm($submit, new Action($this), $this->createRequestersUnitsForm());
	}
	
	/**
	 * @return RequestersUnitsForm
	 */
	private function createRequestersUnitsForm() {
		$add = new Action($this, 'add-unit');
		$remove = new Action($this, 'remove-unit');
		$seek = new Action($this, 'seek-unit');
		$search = new Action($this, 'search-unit');
		return new RequestersUnitsForm($add, $remove, $seek, $search, $this->session);
	}
}
?>