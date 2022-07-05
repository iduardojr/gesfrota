<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\View\Layout;
use Gesfrota\View\ServiceProviderForm;
use Gesfrota\View\ServiceProviderList;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;


class ServiceProviderController extends AbstractController {
	
	public function indexAction() {
		$list = new ServiceProviderList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'active'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, null, array('limit' => 20, 'processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['name']) ) {
					$query->andWhere('u.name LIKE :name OR u.alias LIKE :name');
					$query->setParameter('name', '%' . $data['name'] . '%');
				}
				if ( !empty($data['nif']) ) {
					$query->andWhere('u.nif LIKE :nif');
					$query->setParameter('nif', '%' . $data['nif'] . '%');
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
				$this->setAlert(new Alert('<strong>Ok! </strong>Prestador de Serviço <em>#' . $entity->code . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível editar o Prestador de Serviço. Prestador de Serviço <em>#' . $id . '</em> não encontrado.'));
			if ( $helper->update($form, $id) ) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Prestador de Serviço <em>#' . $entity->code . ' ' . $entity->name .  '</em> alterado com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar o Prestador de Serviço. Prestador de Serviço <em>#' . $id . '</em> não encontrado.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Prestador de Serviço <em>#' . $entity->code . ' ' . $entity->name . '</em> ' . ( $entity->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ) {
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
		return new Crud($this->getEntityManager(), ServiceProvider::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return ServiceProviderForm
	 */
	private function createForm ( Action $submit ) {
		return new ServiceProviderForm($submit, new Action($this));
	}
	
}
?>