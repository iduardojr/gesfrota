<?php
namespace Sigmat\Controller;

use PHPBootstrap\Mvc\Session\Session;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\Agency\AgencyForm;
use Sigmat\View\Agency\AgencyList;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\View\Layout;
use Doctrine\ORM\QueryBuilder;
use Sigmat\Model\AdministrativeUnit\Agency;

/**
 * Orgão
 */
class AgencyController extends AbstractController {
	
	/**
	 * Construtor
	 */
	public function __construct() {
		$this->session = new Session('agency');
	}
	
	public function indexAction() {
		$list = new AgencyList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
		$process = function ( QueryBuilder $query, array $data ) {
			if ( !empty($data['name']) ) {
				$query->andWhere($query->expr()->like('u.name', $query->expr()->literal('%' . $data['name'] . '%')));
			}
			if ( !empty($data['acronym']) ) {
				$query->andWhere($query->expr()->like('u.acronym', $query->expr()->literal('%' . $data['acronym'] . '%')));
			}
			if ( !empty($data['status']) ) {
				$query->andWhere($query->expr()->eq('u.status', $data['status']-1));
			}
		};
		$helper = new Crud($this->getEntityManager(), Agency::getClass());
		$helper->read($this->request, $this->session, $list, null, array('limit' => null, 'processQuery' => $process ));
		return new Layout($list);
	}
	
	public function newAction() {
		try {
			$form = new AgencyForm(new Action($this, 'new'), new Action($this));
			$helper = new Crud($this->getEntityManager(), Agency::getClass());
			if ( $helper->create($this->request, $form) ){
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
			$helper = new Crud($this->getEntityManager(), Agency::getClass());
			if ( $helper->update($id, $this->request, $form) ){
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
			$helper = new Crud($this->getEntityManager(), Agency::getClass());
			$helper->delete($id);
			$entity = $helper->getEntity();
			$this->session->alert = new Alert('<strong>Ok!</strong> Orgão <em>#' . $entity->id . ' ' . $entity->acronym . '</em> removido com sucesso!', Alert::Success);
		} catch ( NotFoundEntityException $e ){
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel excluir o orgão. Orgão <em>#' . $id . '</em> não foi encontrado');
		} catch ( \Exception $e ) {
			$this->session->alert = new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger);
		}
		$this->forward('/');
	}
	
}
?>