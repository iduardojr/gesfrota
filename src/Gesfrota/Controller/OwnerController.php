<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\Owner;
use Gesfrota\Model\Domain\OwnerCompany;
use Gesfrota\Model\Domain\OwnerPerson;
use Gesfrota\View\Layout;
use Gesfrota\View\OwnerForm;
use Gesfrota\View\OwnerList;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class OwnerController extends AbstractController {
	
	public function indexAction() {
	    $list = new OwnerList(new Action($this), new Action($this, 'newPerson'), new Action($this, 'newCompany'), new Action($this, 'edit'), new Action($this, 'active'));
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, null, array('limit' => 20, 'processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['name']) ) {
					$query->andWhere('u.name LIKE :name');
					$query->setParameter('name', '%' . $data['name'] . '%');
				}
				if ( !empty($data['nif']) ) {
					$query->andWhere('u.nif LIKE :nif');
					$query->setParameter('nif', '%' . $data['nif'] . '%');
				}
				if ( !empty($data['status']) ) {
				    $query->andWhere('u.active = :status');
				    $query->setParameter('status', $data['status'] > 0);
				}
			}));
			$list->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
			$list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}
	
	public function newPersonAction() {
	    $form = $this->createForm(OwnerPerson::getClass(), new Action($this, 'newPerson'));
		try {
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, OwnerPerson::getClass()) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Proprietário <em>#' . $entity->code . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function newCompanyAction() {
	    $form = $this->createForm(OwnerCompany::getClass(), new Action($this, 'newCompany'));
	    try {
	        $helper = $this->createHelperCrud();
	        if ( $helper->create($form, OwnerCompany::getClass()) ){
	            $entity = $helper->getEntity();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Proprietário <em>#' . $entity->code . ' ' . $entity->name . '</em> criado com sucesso!', Alert::Success));
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
			$entity = $this->getEntityManager()->find(Owner::getClass(), (int) $id);
			if ( ! $entity ) {
				throw new NotFoundEntityException('Não foi possível editar o Proprietário. Proprietário <em>#' . $id . '</em> não encontrado.');
			}
			if ( $entity instanceof OwnerCompany && $entity->isReadOnly() ) {
				throw new NotFoundEntityException('Não foi possível editar o Proprietário. Proprietário <em>#' . $entity->code . ' ' . $entity->name .  '</em> é somente de leitura.');
			}
			
			$form = $this->createForm($entity, new Action($this, 'edit', array('key' => $id)));
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar o Proprietário. Proprietário <em>#' . $id . '</em> não encontrado.'));
			if ( $helper->update($form, $id) ) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Proprietário <em>#' . $entity->code . ' ' . $entity->name .  '</em> alterado com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar o Proprietário. Proprietário <em>#' . $id . '</em> não encontrado.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Proprietário <em>#' . $entity->code . ' ' . $entity->name . '</em> ' . ( $entity->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
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
		return new Crud($this->getEntityManager(), Owner::getClass(), $this);
	}
	
	/**
	 * @param string|Owner $owner
	 * @param Action $submit
	 * @return OwnerForm
	 */
	private function createForm ( $owner, Action $submit ) {
		return new OwnerForm($owner, $submit, new Action($this));
	}
	
}
?>