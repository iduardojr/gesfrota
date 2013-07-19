<?php
namespace Sigmat\Controller;

use Doctrine\ORM\QueryBuilder;
use Sigmat\Controller\Helper\Crud;
use Sigmat\View\Stockroom\StockroomList;
use Sigmat\View\Layout;
use Sigmat\Model\Stockroom\Stockroom;
use Sigmat\View\Stockroom\StockroomForm;
use Sigmat\Model\AdministrativeUnit\Agency;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\View\Stockroom\RequestersUnitsForm;
use Sigmat\Model\AdministrativeUnit\AdministrativeUnit;
use Sigmat\View\AdministrativeUnit\AdministrativeUnitTree;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

/**
 * Almoxarifado
 */
class StockroomController extends AbstractController { 
	
	public function indexAction() {
		$this->session->units = null;
		$list = new StockroomList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
		$helper = $this->createHelperCrud();
		$cookie = $helper->read($list, $this->createQuery(), array('limit' => null));
		$this->response->setCookie($cookie);
		if ( $this->session->alert ) {
			$list->setAlert($this->session->alert);
			$this->session->alert = null;
		}
		return new Layout($list);
	}
	
	public function newAction() {
		try {
			$this->session->units = array();
			$agency = $this->getAgency();
			$form = new StockroomForm(new Action($this, 'new'), new Action($this), $this->createRequestersUnitsForm());
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, new Stockroom($agency)) ){
				$entity = $helper->getEntity();
				$this->session->alert = new Alert('<strong>Ok!</strong> Almoxarifado <em>#' . $entity->id . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success);
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
				
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function editAction() {
		try {
			$id = $this->request->getQuery('key');
			$form = new StockroomForm(new Action($this, 'edit', array('key' => $id)), new Action($this), $this->createRequestersUnitsForm());
			$helper = $this->createHelperCrud();
			if ( $helper->update($form, ( int ) $id) ){
				$entity = $helper->getEntity();
				$this->session->alert = new Alert('<strong>Ok!</strong> Almoxarifado <em>#' . $entity->id . ' ' . $entity->name .  '</em> alterado com sucesso!', Alert::Success);
				$this->forward('/');
			}
			$entity = $helper->getEntity();
			$this->session->units = $entity->getUnits();
		} catch ( NotFoundEntityException $e ){
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel editar o Almoxarifado. Almoxarifado <em>#' . $id . '</em> não foi encontrado');
			$this->forward('/');
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
			$helper->delete(( int ) $id);
			$entity = $helper->getEntity();
			$this->session->alert = new Alert('<strong>Ok!</strong> Almoxarifado <em>#' . $entity->id . ' ' . $entity->name . '</em> removido com sucesso!', Alert::Success);
		} catch ( NotFoundEntityException $e ){
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel excluir o Almoxarifado. Almoxarifado <em>#' . $id . '</em> não foi encontrado');
		} catch ( \Exception $e ) {
			$this->session->alert = new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger);
		}
		$this->forward('/');
	}
	
	public function seekUnitAction() {
		$entity = $this->getUnit( ( int ) $this->request->getQuery('query'));
		if ( $entity instanceof AdministrativeUnit && ! ( $entity instanceof Agency ) ) {
			return new JsonView(array('unit-id' => $entity->id, 'unit-name' => utf8_decode($entity->name), 'message' => null));
		}
		return new JsonView(array('unit-name' => '', 'message' => utf8_decode('Unidade Administrativa não encontrada.')));
	}
	
	public function searchUnitAction() {
		return new Layout(new AdministrativeUnitTree($this->getAgency()), null);
	}
	
	public function addUnitAction() {
		
	}
	
	public function removeUnitAction() {
		
	}
	
	/**
	 * @return RequestersUnitsForm
	 */
	private function createRequestersUnitsForm() {
		return new RequestersUnitsForm(new Action($this, 'add-unit'), new Action($this, 'remove-unit'), new Action($this, 'seek-unit'), new Action($this, 'search-unit'));
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
		return new Crud($this->getEntityManager(), Stockroom::getClass(), $this->getRequest());
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
		return $this->getEntityManager()->find(AdministrativeUnit::getClass(), $id);
	}
}
?>