<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\VehicleMaker;
use Gesfrota\View\Layout;
use Gesfrota\View\VehicleMakerForm;
use Gesfrota\View\VehicleMakerList;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class VehicleMakerController extends AbstractController { 
	
	public function indexAction() {
		$list = new VehicleMakerList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'active'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, null, array('limit' => 12, 'processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['name']) ) {
					$query->andWhere('u.name LIKE :name');
					$query->setParameter('name', '%' . $data['name'] . '%');
				}
				if ( !empty($data['type']) ) {
					$query->andWhere('u.type = :type');
					$query->setParameter('type', $data['type']);
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
			$helper = $this->createHelperCrud();
			if ( $helper->create($form) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Marca de Veículo <em>#' . $entity->code . ' ' . $entity->name . '</em> criada com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível editar a Marca de Veículo. Marca de Veículo <em>#' . $id . '</em> não encontrada.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Marca de Veículo <em>#' . $entity->code . ' ' . $entity->name .  '</em> alterada com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar a Marca de Veículo. Marca de Veículo <em>#' . $id . '</em> não encontrada.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Marca de Veículo <em>#' . $entity->code . ' ' . $entity->name . '</em> ' . ( $entity->active ? 'ativada' : 'desativada' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
	    return new Crud($this->getEntityManager(), VehicleMaker::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return VehicleMakerForm
	 */
	private function createForm ( Action $submit ) {
	    return new VehicleMakerForm($submit, new Action($this));
	}
	
}
?>