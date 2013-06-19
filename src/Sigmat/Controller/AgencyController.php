<?php
namespace Sigmat\Controller;

use Sigmat\View\Layout;
use Sigmat\View\Agency\AgencyForm;
use Sigmat\View\Agency\AgencyList;
use Sigmat\View\EntityDatasource;
use PHPBootstrap\Mvc\Session\Session;
use Doctrine\ORM\QueryBuilder;
use Sigmat\Model\Agency\Agency;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Action\Action;

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
		$query = $this->getEntityManager()->getRepository(Agency::getClass())->createQueryBuilder('u');
		$process = function ( QueryBuilder $query, array $data ) {
			$where = $query->expr()->andx();
			if ( !empty($data['name']) ) {
				$expr = $query->expr()->like('u.name', $query->expr()->literal('%' . $data['name'] . '%'));
            	$where->add($expr);
			}
			if ( !empty($data['acronym']) ) {
				$expr = $query->expr()->like('u.acronym', $query->expr()->literal('%' . $data['acronym'] . '%'));
				$where->add($expr);
			}
			if ( !empty($data['status']) ) {
				$expr = $query->expr()->eq('u.status', $data['status']-1);
            	$where->add($expr);
			}
        	if ( $where->count() > 0 ) {
           		$query->where($where);
        	}
		};
		$datasource = new EntityDatasource($query, $this->session, $process);
		if ( $this->request->isPost() ) {
			$datasource->setFilter($this->request->getPost());
		}
		$query = $this->request->getQuery();
		if ( isset($query['sort']) ) {
			$datasource->toggleOrder($this->sanitize($query['sort']));
		}
		if ( isset($query['reset-filter']) ) {
			$datasource->setFilter(array());
		}
		if ( isset($query['page']) ) {
			$datasource->setPage((int) $query['page']);
		}
		if ( isset($query['limit']) ) {
			$datasource->setLimit((int) $query['limit']);
		}
		$list = new AgencyList($datasource, new Action($this));
		if ( $this->session->alert ) {
			$list->setAlert($this->session->alert);
			$this->session->alert = null;
		}
		return new Layout($list);
	}
	
	public function newAction() {
		$form = new AgencyForm(new Action($this, 'new'), new Action($this));
		if ( $this->request->isPost() ) {
			$form->bind($this->request->getPost());
			if ( $form->valid() ) {
				$entity = new Agency();
				$form->hydrate($entity);
				$this->getEntityManager()->persist($entity);
				$this->getEntityManager()->flush();
				$this->session->alert = new Alert('<strong>Ok!</strong> Orgão #' . $entity->getId() . ' ' . $entity->getAcronym() . ' criado com sucesso!', Alert::Success);
				$this->forward('/');
			}
		}
		return new Layout($form);
	}
	
	public function editAction() {
		$id = $this->request->getQuery('key');
		$entity = $this->getEntityManager()->find(Agency::getClass(), ( int ) $id);
		if ( ! $entity instanceof Agency ) {
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel editar o orgão. Orgão #' . $id . ' não foi encontrado');
			$this->forward('/');
		} 
		$form = new AgencyForm(new Action($this, 'edit', array('key' => $id)), new Action($this));
		if ( $this->request->isPost() ) {
			$form->bind($this->request->getPost());
			if ( $form->valid() ) {
				$form->hydrate($entity);
				$this->getEntityManager()->persist($entity);
				$this->getEntityManager()->flush();
				$this->session->alert = new Alert('<strong>Ok!</strong> Orgão #' . $id . ' ' . $entity->getAcronym() . ' alterado com sucesso!', Alert::Success);
				$this->forward('/');
			}
		}
		$form->extract($entity);
		$form->getButtonByName('submit')->setLabel('Salvar');
		return new Layout($form);
	}
	
	public function removeAction() {
		$id = $this->request->getQuery('key');
		$entity = $this->getEntityManager()->find(Agency::getClass(), ( int ) $id);
		if ( ! $entity instanceof Agency ) {
			$this->session->alert = new Alert('<strong>Ops!</strong> Não foi possivel excluir o orgão. Orgão #' . $id . ' não foi encontrado');
			$this->forward('/');
		}
		$this->getEntityManager()->remove($entity);
		$this->getEntityManager()->flush();
		$this->session->alert = new Alert('<strong>Ok!</strong> Orgão #' . $id . ' ' . $entity->getAcronym() . ' removido com sucesso!', Alert::Success);
		$this->forward('/');
	}
	
}
?>