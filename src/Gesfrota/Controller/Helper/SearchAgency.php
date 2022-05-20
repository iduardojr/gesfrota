<?php
namespace Gesfrota\Controller\Helper;

use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\View\AdministrativeUnitTable;
use Gesfrota\View\AgencyTable;
use Gesfrota\View\Layout;
use Gesfrota\View\ResultCenterTable;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\Widget\PanelQuery;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\Manager;

trait SearchAgency {
	
	/**
	 * @param Agency $agency
	 */
	abstract protected function setAgencySelected(Agency $agency = null);
	
	/**
	 * @return Agency
	 */
	abstract protected function getAgencySelected(); 
	
	public function seekAgencyAction() {
		try {
			$data['agency-id'] = null;
			$data['agency-name'] = null;
			$data['administrative-unit-id'] = null;
			$data['administrative-unit-name'] = null;
			$data['result-center-required'] = false;
			$data['results-center'] = [];
			$data['result-center-id'] = null;
			$data['flash-message'] = null;
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(Agency::getClass())->findOneBy(['id' => $id, 'active' => true]);
			if ( ! $entity instanceof Agency ) {
				throw new NotFoundEntityException('Órgão <em>#' . $id . '</em> não encontrado.');
			}
			$this->setAgencySelected($entity);
			
			$data['agency-id'] = $entity->getCode();
			$data['agency-name'] = $entity->getName();
			$data['owner-id'] = $entity->getOwner()->getCode();
			$data['owner-name'] = $entity->getOwner()->getName();
			$data['result-center-required'] = $entity->isResultCenterRequired();
			$data['results-center'] = $entity->getResultCentersActived();
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function searchAgencyAction() {
		try {
			$query = $this->getEntityManager()->getRepository(Agency::getClass())->createQueryBuilder('u');
			$query->andWhere('u.id > 0');
			$params = $this->request->getQuery();
			if ( $params['query'] ) {
				$query->andWhere('u.name LIKE :name OR u.acronym LIKE :name');
				$query->setParameter('name', '%' . $params['query'] . '%');
			}
			$datasource = new EntityDatasource($query);
			$datasource->setOrderBy('acronym', 'ASC');
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new AgencyTable(new Action($this,'searchAgency', $params));
			$table->setDataSource($datasource);
			$widget = new PanelQuery($table, new Action($this,'searchAgency', $params), $params['query'], 'agency-search');
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function seekUnitAction() {
		try {
			$data['administrative-unit-id'] = null;
			$data['administrative-unit-name'] = null;
			$data['flash-message'] = null;
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->findOneBy(['id' => $id, 'active' => true, 'agency' => $this->getAgencySelected()->getId()]);
			if ( (! $entity instanceof AdministrativeUnit) ) {
				throw new NotFoundEntityException('Unidade Administrativa <em>#' . $id . '</em> não encontrada.');
			}
			$data['administrative-unit-id'] = $entity->getCode();
			$data['administrative-unit-name'] = $entity->getName();
			$data['result-center-required'] = $entity->getAgency()->isResultCenterRequired();
			if ( $this->getUserActive()->getLotation() != $entity && ( $this->getUserActive() instanceof Manager || $this->getUserActive() instanceof FleetManager )) {
				$data['result-center-id'] = $this->getAgencySelected()->getResultCentersActived();
			} else {
				$data['result-center-id'] = $this->getUserActive()->getResultCentersActived();
			}
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function searchUnitAction() {
		try {
			$query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
			$query->distinct(true);
			$query->andWhere('u.agency = :agency');
			$query->setParameter('agency', $this->getAgencySelected()->getId());
			$query->orderBy('u.lft');
			$params = $this->request->getQuery();
			if ( isset($params['query']) ) {
				$query->from(AdministrativeUnit::getClass(), 'p0');
				$query->andWhere('u.lft BETWEEN p0.lft AND p0.rgt');
				$query->andWhere('p0.name LIKE :name OR p0.acronym LIKE :name');
				$query->setParameter('name', '%' . $params['query'] . '%');
			}
			$datasource = new EntityDatasource($query);
			$datasource->setOrderBy('lft', 'ASC');
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new AdministrativeUnitTable(new Action($this,'searchUnit', $params));
			$table->setDataSource($datasource);
			$widget = new PanelQuery($table, new Action($this,'searchUnit', $params), $params['query'], 'administrative-unit-search');
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
	
	public function seekResultCenterAction() {
		try {
			$data['result-center-id'] = null;
			$data['result-center-description'] = null;
			$data['flash-message'] = null;
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(ResultCenter::getClass())->findOneBy(['id' => $id, 'active' => true, 'agency' => $this->getAgencySelected()->getId()]);
			if ( (! $entity instanceof ResultCenter) ) {
				throw new NotFoundEntityException('Centro de Resultado <em>#' . $id . '</em> não encontrado.');
			}
			$data['results-center'] = $entity->getResultCentersActived();
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function searchResultCenterAction() {
		try {
			$query = $this->getEntityManager()->getRepository(ResultCenter::getClass())->createQueryBuilder('u');
			$query->distinct(true);
			$query->where('u.active = true');
			$query->andWhere('u.agency = :agency');
			$query->setParameter('agency', $this->getAgencySelected()->getId());
			$params = $this->request->getQuery();
			$datasource = new EntityDatasource($query);
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new ResultCenterTable(new Action($this,'searchResultCenter', $params));
			$table->setDataSource($datasource);
			$widget = $table;
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
}
