<?php
namespace Sigmat\Controller;

use Doctrine\ORM\QueryBuilder;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\View\GUI\Layout;
use Sigmat\View\StockroomForm;
use Sigmat\View\StockroomList;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\Model\Domain\Stockroom;

class StockroomController extends AbstractController { 
	
	public function indexAction() {
		$list = new StockroomList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'active'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $this->createQuery(), array('limit' => 12, 'processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['name']) ) {
					$query->andWhere('u.name LIKE :name');
					$query->setParameter('name', '%' . $data['name'] . '%');
				}
				if ( !empty($data['only-active']) ) {
					$query->andWhere('u.active = true');
				}
			}));
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
				$this->setAlert(new Alert('<strong>Ok! </strong>Almoxarifado <em>#' . $entity->code . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
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
				$this->setAlert(new Alert('<strong>Ok! </strong>Almoxarifado <em>#' . $entity->code . ' ' . $entity->name .  '</em> alterado com sucesso!', Alert::Success));
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
	
	public function activeAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar o Almoxarifado. Almoxarifado <em>#' . $id . '</em> não encontrado.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Almoxarifado <em>#' . $entity->code . ' ' . $entity->name . '</em> ' . ( $entity->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	/**
	 * @return QueryBuilder
	 */
	private function createQuery() {
		$query = $this->getEntityManager()->getRepository(Stockroom::getClass())->createQueryBuilder('u');
		$query->leftJoin('u.agency', 'a');
		$query->andWhere($query->expr()->eq('a.id', $this->getAgencyActive()->getId()));
		return $query;
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), Stockroom::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return StockroomForm
	 */
	private function createForm ( Action $submit ) {
		return new StockroomForm($submit, new Action($this));
	}
	
}
?>