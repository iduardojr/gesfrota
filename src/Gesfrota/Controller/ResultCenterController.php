<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Controller\Helper\SearchAgency;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\View\AdministrativeUnitForm;
use Gesfrota\View\Layout;
use Gesfrota\View\ResultCenterList;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\View\ResultCenterForm;

class ResultCenterController extends AbstractController { 
	
	use SearchAgency;
	
	public function indexAction() {
		try {
			$this->setAgencySelected(null);
			$showAgencies = $this->getShowAgencies();
			$list = new ResultCenterList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'active'), $showAgencies);
			$query = $this->getEntityManager()->getRepository(ResultCenter::getClass())->createQueryBuilder('u');
			
			$helper = $this->createHelperCrud();
			$helper->read($list, $query, ['limit' => 12, 'sort' => 'agency', 'processQuery' => function( QueryBuilder $query, array $data ) {
				if (!empty($data['agency'])) {
					$query->where('u.agency = :agency');
					$query->setParameter('agency', $data['agency']);
				}
				if ( !empty($data['description']) ) {
					$query->andWhere('u.description LIKE :description');
					$query->setParameter('description', '%' . $data['description'] . '%');
				}
				if ( !empty($data['only-active']) ) {
					$query->andWhere('u.active = true');
				}
			}]);
			$list->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
			throw $e;
			$list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}
	
	
	public function newAction() {
		try {
			$form = $this->createForm(new Action($this, 'new'));
			
			$helper = $this->createHelperCrud();
			$agency = $this->getAgencyActive()->isGovernment() ? null : $this->getAgencyActive();
			if ( $helper->create($form, new ResultCenter($agency)) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Centro de Resultado <em>#' . $entity->code . ' ' . $entity->description . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	
	public function editAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(ResultCenter::getClass(), $id);
			if ( ! $entity instanceof ResultCenter ) {
				throw new NotFoundEntityException('Não foi possível editar o Centro de Resultado. Centro de Resultado <em>#' . $id . '</em> não encontrado.');
			}
			$this->setAgencySelected($entity->getAgency());
			
			$form = $this->createForm(new Action($this, 'edit', array('key' => $id)));
			$helper = $this->createHelperCrud();
		
			if ( $helper->update($form, $entity) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Centro de Resultado <em>#' . $entity->code . ' ' . $entity->description .  '</em> alterado com sucesso!', Alert::Success));
				$this->forward('/');
			}
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch ( InvalidRequestDataException $e ){
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
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar o Centro de Resultado. Centro de Resultado <em>#' . $id . '</em> não encontrado.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Centro de Resultado <em>#' . $entity->code . ' ' . $entity->description . '</em> ' . ( $entity->active ? 'ativado' : 'desativado' ) . ' com sucesso!', Alert::Success));
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
		return new Crud($this->getEntityManager(), ResultCenter::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return ResultCenterForm
	 */
	private function createForm ( Action $submit ) {
		$seekAgency = new Action($this, 'seekAgency');
		$seachAgency = new Action($this, 'searchAgency');
		$cancel = new Action($this);
		$showAgency = null;
		if (! $this->getAgencyActive()->isGovernment()) {
			$showAgency = $this->getAgencyActive();
		}
		return new ResultCenterForm($submit, $seekAgency, $seachAgency, $cancel, $showAgency);
	}
	
	/**
	 * @return Agency
	 */
	protected function getAgencySelected() {
		if ($this->session->agency_selected > 0) {
			$selected = $this->getEntityManager()->find(Agency::getClass(), $this->session->agency_selected);
			if ($selected) {
				return $selected;
			}
		}
		return $this->getAgencyActive();
	}
	
	/**
	 * @param Agency $agency
	 */
	protected function setAgencySelected(Agency $agency = null) {
		$this->session->agency_selected = $agency ? $agency->getId() : null;
	}
}
?>