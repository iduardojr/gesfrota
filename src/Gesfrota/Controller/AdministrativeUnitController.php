<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\View\AdministrativeUnitForm;
use Gesfrota\View\AdministrativeUnitList;
use Gesfrota\View\AdministrativeUnitTable;
use Gesfrota\View\Layout;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\Widget\PanelQuery;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Gesfrota\Controller\Helper\SearchAgency;

class AdministrativeUnitController extends AbstractController { 
	use SearchAgency;
	
	public function indexAction() {
		$showAgencies = $this->getShowAgencies();
		$list = new AdministrativeUnitList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'active'), $showAgencies);
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $this->createQuery(), ['limit' => 12, 'sort' => 'lft', 'order' => 'asc', 'processQuery' => function( QueryBuilder $query, array $data ) {
				if (!empty($data['agency1'])) {
					$query->where('u.agency = :agency');
					$query->setParameter('agency', $data['agency1']);
				}
				if ( !empty($data['name']) ) {
					$query->from(AdministrativeUnit::getClass(), 'p0');
					$query->andWhere('u.lft BETWEEN p0.lft AND p0.rgt');
					$query->andWhere('p0.name LIKE :name OR p0.acronym LIKE :name');
					$query->setParameter('name', '%' . $data['name'] . '%');
				}
				if ( !empty($data['only-active']) ) {
					$query->andWhere('u.active = true');
					$query->andWhere('u.id NOT IN(SELECT p1.id FROM ' . AdministrativeUnit::getClass() . ' p1, ' . AdministrativeUnit::getClass() . ' p2 WHERE p2.active = false AND p1.lft BETWEEN p2.lft AND p2.rgt)');
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
		$id = $this->request->getQuery('key');
		$form = $this->createForm(new Action($this, 'new'));
		try {
			$parent = $this->getEntityManager()->find(AdministrativeUnit::getClass(), ( int ) $id);
			if (! $parent) {
				$parent = $this->getAgencyActive();
			}
			$helper = $this->createHelperCrud();
			if ( $helper->create($form, new AdministrativeUnit($parent)) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Unidade Administrativa <em>#' . $entity->code . ' ' . $entity->fullDescription . '</em> criada com sucesso!', Alert::Success));
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
		$id = $this->request->getQuery('key');
		$form = $this->createForm(new Action($this, 'edit', array('key' => $id)));
		try {
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar a Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não encontrada.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Unidade Administrativa <em>#' . $entity->code . ' ' . $entity->fullDescription .  '</em> alterada com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar a Unidade Administrativa. Unidade Administrativa <em>#' . $id . '</em> não encontrada.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Unidade Administrativa <em>#' . $entity->code . ' ' . $entity->fullDescription . '</em> ' . ( $entity->active ? 'ativada' : 'desativada' ) . ' com sucesso!', Alert::Success));
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
		$query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
		$query->distinct(true);
		if (!$this->getAgencyActive()->isGovernment()) {
			$query->andWhere('u.agency = :agency');
			$query->setParameter('agency', $this->getAgencyActive()->getId());
		}
		$query->orderBy('u.lft');
		return $query;
	}
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		$this->request->setPost(array_merge($this->request->getPost(), array('agency' => $this->getAgencyActive())));
		return new Crud($this->getEntityManager(), AdministrativeUnit::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return AdministrativeUnitForm
	 */
	private function createForm ( Action $submit ) {
		$seekUnit = new Action($this, 'seekUnit');
		$searchUnit = new Action($this, 'searchUnit', $submit->getParameters());
		$seekAgency = new Action($this, 'seekAgency');
		$seachAgency = new Action($this, 'searchAgency');
		$cancel = new Action($this);
		$showAgency = null;
		if (! $this->getAgencyActive()->isGovernment()) {
			$showAgency = $this->getAgencyActive();
			$this->session->selected = $showAgency->getId();
		}
		return new AdministrativeUnitForm($submit, $seekUnit, $searchUnit, $seekAgency, $seachAgency, $cancel, $showAgency);
	}

}
?>