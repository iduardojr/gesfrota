<?php
namespace Gesfrota\Controller\Helper;

use Gesfrota\Model\Domain\Agency;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Mvc\View\JsonView;
use Gesfrota\View\AgencyTable;
use Gesfrota\View\Widget\PanelQuery;
use Gesfrota\View\Widget\EntityDatasource;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Modal\Modal;
use Gesfrota\View\Layout;
use PHPBootstrap\Widget\Misc\Title;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\View\AdministrativeUnitTable;

trait SearchAgency {
	
	public function seekAgencyAction() {
		try {
			$data['agency-id'] = null;
			$data['agency-name'] = null;
			$data['administrative-unit-id'] = null;
			$data['administrative-unit-name'] = null;
			$data['flash-message'] = null;
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->getRepository(Agency::getClass())->findOneBy(['id' => $id, 'active' => true]);
			if ( ! $entity instanceof Agency ) {
				throw new NotFoundEntityException('Órgão <em>#' . $id . '</em> não encontrado.');
			}
			$this->session->selected = $entity->getId();
			$data['agency-id'] = $entity->getCode();
			$data['agency-name'] = $entity->getName();
			$data['owner-id'] = $entity->getOwner()->getCode();
			$data['owner-name'] = $entity->getOwner()->getName();
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
				$query->andWhere('u.name LIKE :name');
				$query->orWhere('u.acronym LIKE :name');
				$query->setParameter('name', '%' . $params['query'] . '%');
			}
			$datasource = new EntityDatasource($query);
			$datasource->setOrderBy('name', 'ASC');
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new AgencyTable(new Action($this,'searchAgency', $params));
			$table->setDataSource($datasource);
			$widget = new PanelQuery($table, new Action($this,'searchAgency', $params), $params['query'], new Modal('agency-search', new Title('Unidades Administrativas', 3)));
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
			$entity = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->findOneBy(['id' => $id, 'active' => true, 'agency' => $this->session->selected]);
			if ( (! $entity instanceof AdministrativeUnit) ) {
				throw new NotFoundEntityException('Unidade Administrativa <em>#' . $id . '</em> não encontrada.');
			}
			$data['administrative-unit-id'] = $entity->getCode();
			$data['administrative-unit-name'] = $entity->getName();
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
			$query->setParameter('agency', $this->session->selected);
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
			$widget = new PanelQuery($table, new Action($this,'searchUnit', $params), $params['query'], new Modal('administrative-unit-search', new Title('Unidades Administrativas', 3)));
		} catch ( \Exception $e ) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}
}
