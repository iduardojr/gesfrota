<?php
namespace Sigmat\Controller;

use Doctrine\ORM\QueryBuilder;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\GUI\Layout;
use Sigmat\View\ProductUnitList;
use Sigmat\View\ProductUnitForm;
use Sigmat\Controller\Helper\Crud;
use Sigmat\Controller\Helper\NotFoundEntityException;
use Sigmat\Controller\Helper\InvalidRequestDataException;
use Sigmat\Model\Domain\ProductUnit;

class ProductUnitController extends AbstractController {
	
	public function indexAction() {
		$this->session->options = null;
		$list = new ProductUnitList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'active'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, null, array('processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['description']) ) {
					$query->andWhere('u.description LIKE :description');
					$query->setParameter('description', '%' . $data['description'] . '%');
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
				$this->setAlert(new Alert('<strong>Ok! </strong>Unidade de Medida <em>#' . $entity->code . ' ' . $entity->description . '</em> criada com sucesso!', Alert::Success));
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
		$id = $this->request->getQuery('key');
		$form = $this->createForm(new Action($this, 'edit', array('key' => $id)));
		try {
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar a Unidade de Medida. Unidade de Medida <em>#' . $id . '</em> não encontrada.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Unidade de Medida <em>#' . $entity->code . ' ' . $entity->description .  '</em> alterada com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar a Unidade de Medida. Unidade de Medida <em>#' . $id . '</em> não encontrada.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Unidade de Medida <em>#' . $entity->id . ' ' . $entity->description . '</em> ' . ( $entity->active ? 'ativada' : 'desativada' ) . ' com sucesso!', Alert::Success));
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
		return new Crud($this->getEntityManager(), ProductUnit::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return ProductUnitForm
	 */
	private function createForm ( Action $submit ) {
		return new ProductUnitForm($submit, new Action($this));
	}
	
}
?>