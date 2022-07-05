<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\View\AgencyForm;
use Gesfrota\View\AgencyList;
use Gesfrota\View\Layout;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Mvc\View\JsonView;


class AgencyController extends AbstractController {
	
	public function indexAction() {
		try {
			$filter = new Action($this);
			$new = new Action($this, 'new');
			$transfer1 = new Action(TransferFleetController::getClass());
			$transfer2 = new Action(TransferUsersController::getClass());
			$edit = new Action($this, 'edit');
			$active = new Action($this, 'active');
			$list = new AgencyList($filter, $new, $transfer1, $transfer2, $edit, $active);
			$helper = $this->createHelperCrud();
			$query = $this->getEntityManager()->getRepository(Agency::getClass())->createQueryBuilder('u');
			$query->andWhere('u.id > 0');
			$helper->read($list, $query, array('limit' => 20, 'processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['name']) ) {
					$query->andWhere('u.name LIKE :name or u.acronym LIKE :name');
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
		try {
			$form = $this->createForm(new Action($this, 'new'));
			$helper = $this->createHelperCrud();
			if ( $helper->create($form) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Orgão <em>#' . $entity->code . ' ' . $entity->acronym . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function editAction() {
		try {
			$id = $this->request->getQuery('key');
			$form = $this->createForm(new Action($this, 'edit', array('key' => $id)));
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar o Orgão. Orgão <em>#' . $id . '</em> não encontrado.'));
			if ( $helper->update($form, $id) ) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Orgão <em>#' . $entity->code . ' ' . $entity->acronym .  '</em> alterado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( InvalidRequestDataException $e ) {
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function activeAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar o Orgão. Orgão <em>#' . $id . '</em> não encontrado.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Orgão <em>#' . $entity->code . ' ' . $entity->acronym . '</em> ' . ( $entity->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function resultCenterAction() {
		try {
			$data['flash-message'] = null;
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Agency::getClass(), $id);
			if ( ! $entity instanceof Agency ) {
				throw new NotFoundEntityException('Órgão <em>#' . $id . '</em> não encontrado.');
			}
			$data['results-center'] = $entity->getResultCentersActived();
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), Agency::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return AgencyForm
	 */
	private function createForm ( Action $submit ) {
		return new AgencyForm($submit, new Action($this));
	}
	
}
?>