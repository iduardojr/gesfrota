<?php
namespace Sigmat\Controller;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Sigmat\Controller\Helper\Crud;
use Sigmat\View\Stockroom\StockroomList;
use Sigmat\View\Layout;
use Sigmat\Model\Stockroom\Stockroom;
use Sigmat\View\Stockroom\StockroomForm;
use Sigmat\Model\AdministrativeUnit\Agency;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Mvc\Session\Session;
use PHPBootstrap\Widget\Misc\Alert;

/**
 * Almoxarifado
 */
class StockroomController extends AbstractController { 
	
	/**
	 * Construtor
	 */
	public function __construct() {
		$this->session = new Session('stockroom');
	}
	
	public function indexAction() {
		$list = new StockroomList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
		$process = function ( QueryBuilder $query, array $data ) {
			if ( !empty($data['name']) ) {
				$query->andWhere($query->expr()->like('u.name', $query->expr()->literal('%' . $data['name'] . '%')));
			}
			if ( !empty($data['status']) ) {
				$query->andWhere($query->expr()->eq('u.status', $data['status']-1));
			}
		};
		$query = $this->getEntityManager()->getRepository(Stockroom::getClass())->createQueryBuilder('u');
		$query->leftJoin('u.agency', 'a');
		$query->andWhere($query->expr()->eq('a.id', 1));
		$helper = new Crud($this->getEntityManager(), Stockroom::getClass());
		$helper->read($this->request, $this->session, $list, $query, array('limit' => null, 'processQuery' => $process ));
		
		return new Layout($list);
	}
	
	public function newAction() {
		try {
			$form = new StockroomForm(new Action($this, 'new'), new Action($this));
			$agency = $this->getEntityManager()->find(Agency::getClass(), 1);
			$helper = new Crud($this->getEntityManager(), Stockroom::getClass());
			$helper->attach(Crud::PrePersist, function( Stockroom $object, EntityManager $em ) use ( $agency ) {
				$object->setAgency($agency);
			});
			if ( $helper->create($this->request, $form) ){
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
			$form = new StockroomForm(new Action($this, 'edit', array('key' => $id)), new Action($this));
			$helper = new Crud($this->getEntityManager(), Stockroom::getClass());
			if ( $helper->update($id, $this->request, $form) ){
				$entity = $helper->getEntity();
				$this->session->alert = new Alert('<strong>Ok!</strong> Almoxarifado <em>#' . $entity->id . ' ' . $entity->name .  '</em> alterado com sucesso!', Alert::Success);
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel editar o Almoxarifado. Almoxarifado <em>#' . $id . '</em> não foi encontrado');
		} catch ( InvalidRequestDataException $e ){
				
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function removeAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = new Crud($this->getEntityManager(), Stockroom::getClass());
			$helper->delete($id);
			$entity = $helper->getEntity();
			$this->session->alert = new Alert('<strong>Ok!</strong> Almoxarifado <em>#' . $entity->id . ' ' . $entity->name . '</em> removido com sucesso!', Alert::Success);
		} catch ( NotFoundEntityException $e ){
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel excluir o Almoxarifado. Almoxarifado <em>#' . $id . '</em> não foi encontrado');
		} catch ( \Exception $e ) {
			$this->session->alert = new Alert('<strong>Error: </strong> ' . $e->getMessage(), Alert::Danger);
		}
		$this->forward('/');
	}
}
?>