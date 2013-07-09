<?php
namespace Sigmat\Controller;

use Doctrine\ORM\EntityManager;
use Sigmat\Controller\Helper\Crud;
use Sigmat\View\Layout;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Mvc\Session\Session;
use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\View\AdministrativeUnit\AdministrativeUnitList;
use Sigmat\Model\AdministrativeUnit\AdministrativeUnit;
use Sigmat\View\AdministrativeUnit\AdministrativeUnitForm;
use PHPBootstrap\Mvc\View\JsonView;

/**
 * Unidade Administrativa
 */
class AdministrativeUnitController extends AbstractController { 
	
	/**
	 * Construtor
	 */
	public function __construct() {
		$this->session = new Session('administrative-unit');
	}
	
	public function indexAction() {
		$list = new AdministrativeUnitList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'remove'));
		$helper = new Crud($this->getEntityManager(), AdministrativeUnit::getClass());
		$helper->read($this->request, $this->session, $list, $this->getQuery(), array('limit' => null, 'sort' => 'name', 'order' => 'asc'));
		return new Layout($list);
	}
	
	public function newAction() {
		try {
			$parent = $this->getEntityManager()->find(AdministrativeUnit::getClass(), ( int ) $this->request->getQuery('key'));
			if ( $parent === null ) {
				throw new \ErrorException('Não existe uma unidade superior');
			}
			$form = new AdministrativeUnitForm(new Action($this, 'new', array('key' => $parent->getId())), new Action($this), $parent);
			$helper = new Crud($this->getEntityManager(), AdministrativeUnit::getClass());
			$helper->attach(Crud::PrePersist, function( AdministrativeUnit $object, EntityManager $em ) use ( $parent ) {
				$object->setParent($parent);
			});
			if ( $helper->create($this->request, $form) ){
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
			$parent = $this->getEntityManager()->find(AdministrativeUnit::getClass(), $id)->getParent();
			if ( $parent === null ) {
				throw new \ErrorException('Não existe uma unidade superior');
			}
			$form = new AdministrativeUnitForm(new Action($this, 'edit', array('key' => $id)), new Action($this), $parent);
			$helper = new Crud($this->getEntityManager(), AdministrativeUnit::getClass());
			if ( $helper->update($id, $this->request, $form) ){
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
			$entity = $this->getEntityManager()->find(AdministrativeUnit::getClass(), ( int ) $id);
			if ( ! $entity ) {
				throw new NotFoundEntityException();
			}
			$entity->setStatus(false);
			$this->getEntityManager()->flush();
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
	
	private function getQuery() {
		$query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
		$query->leftJoin('u.parent', 'a');
		$query->andWhere($query->expr()->eq('u.status', 1));
		$query->andWhere($query->expr()->eq('u.id', 1));
		$query->orderBy('u.name', 'asc');
		return $query;
	}
}
?>