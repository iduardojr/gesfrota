<?php
namespace Sigmat\Controller;

use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\Agency\AgencyForm;
use Sigmat\View\Agency\AgencyList;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\View\Layout;
use Sigmat\Model\AdministrativeUnit\Agency;

/**
 * Orgão
 */
class AgencyController extends AbstractController {
	
	public function indexAction() {
		$list = new AgencyList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
		$helper = $this->createHelperCrud();
		$cookie = $helper->read($list, null, array('limit' => null));
		$this->response->setCookie($cookie);
		if ( $this->session->alert ) {
			$list->setAlert($this->session->alert);
			$this->session->alert = null;
		}
		return new Layout($list);
	}
	
	public function newAction() {
		try {
			$form = new AgencyForm(new Action($this, 'new'), new Action($this));
			$helper = $this->createHelperCrud();
			if ( $helper->create($form) ){
				$entity = $helper->getEntity();
				$this->session->alert = new Alert('<strong>Ok!</strong> Orgão <em>#' . $entity->id . ' ' . $entity->acronym . '</em> criado com sucesso!', Alert::Success);
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
			$form = new AgencyForm(new Action($this, 'edit', array('key' => $id)), new Action($this));
			$helper = $this->createHelperCrud();
			if ( $helper->update($form, ( int ) $id) ){
				$entity = $helper->getEntity();
				$this->session->alert = new Alert('<strong>Ok!</strong> Orgão <em>#' . $entity->id . ' ' . $entity->acronym .  '</em> alterado com sucesso!', Alert::Success);
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel editar o orgão. Orgão <em>#' . $id . '</em> não foi encontrado');
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
			$this->session->alert = new Alert('<strong>Ok!</strong> Orgão <em>#' . $entity->id . ' ' . $entity->acronym . '</em> removido com sucesso!', Alert::Success);
		} catch ( NotFoundEntityException $e ){
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel excluir o orgão. Orgão <em>#' . $id . '</em> não foi encontrado');
		} catch ( \Exception $e ) {
			$this->session->alert = new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger);
		}
		$this->forward('/');
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), Agency::getClass(), $this->getRequest());
	}
	
}
?>