<?php
namespace Gesfrota\Controller;

use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\View\AgencyTable;
use Gesfrota\View\Layout;
use Gesfrota\View\TransferFleetForm;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\Widget\PanelQuery;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use Gesfrota\Model\Domain\FleetItem;
use PHPBootstrap\Widget\Action\TgAjax;

class TransferFleetController extends AbstractController {
	
	/**
	 * @var TransferFleetForm
	 */
	protected $form;
	
	public function __construct() {
		$submit = new Action($this);
		$seek1 = new Action($this, 'seek-from');
		$seek2 = new Action($this, 'seek-to');
		$search = new Action($this, 'search-agency');
		$cancel = new Action(AgencyController::getClass());
		
		$this->form = new TransferFleetForm($submit, $seek1, $seek2, $search, $cancel);
		parent::__construct();
	}
	
	public function indexAction() {
		try {
			if ($this->request->isPost()) {
				$post = $this->request->getPost();
				$entityFrom = $this->getEntityManager()->find(Agency::getClass(), (int) $post['from-agency-id']);
				if ( $entityFrom instanceof Agency && ! $entityFrom->isGovernment() ) {
					$ds = $this->createDataSource($entityFrom);
					$ds->setPage($this->session->page_from);
					$this->form->getTableFrom()->setDataSource($ds);
				}
				$entityTo = $this->getEntityManager()->find(Agency::getClass(), (int) $post['to-agency-id']);
				if ( $entityTo instanceof Agency && ! $entityTo->isGovernment()) {
					$ds = $this->createDataSource($entityTo);
					$this->form->getTableTo()->setDataSource($ds);
					$this->form->bind($post);
				}
				if ( ! $this->form->valid() ) {
					throw new InvalidRequestDataException();
				}
				if (! $entityFrom instanceof Agency) {
				    throw new \DomainException('Não foi possivel Transferir a Frota: Órgão de Origem <em>#' . $post['from-agency-id'] . '</em> não encontrado.');
				}
				if (! $entityTo instanceof Agency) {
				    throw new \DomainException('Não foi possível Transferir a Frota: Órgão de Destino <em>#' . $post['to-agency-id'] . '</em> não encontrado.');
				}
				if ( ! isset($post['from-fleet']) ) {
					throw new InvalidRequestDataException('Por favor, selecione pelo menos um item da frota para ser transferido.');
				}
				foreach($post['from-fleet'] as $key) {
					$item = $this->getEntityManager()->find(FleetItem::getClass(), (int) $key);
					if (! $item instanceof FleetItem) {
						throw new \DomainException('Não foi possivel Transferir a Frota: Item da Frota <em>#' . $key . '</em> não encontrado.');
					}
					$item->setResponsibleUnit($entityTo);
				}
				$this->getEntityManager()->flush();
				
				$success = '<span class="badge badge-success"> '. count($post['from-fleet']) . '</span> Itens da Frota ';
				$success.= 'transferidos com sucesso de <em>#' . $entityFrom->code . ' ' . $entityFrom->acronym . '</em> ';
				$success.= 'para <em>#' . $entityTo->code . ' ' . $entityTo->acronym . '</em>.';
				$this->form->setAlert(new Alert($success, Alert::Success));
			}
		} catch ( InvalidRequestDataException $e ){
			$this->form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($this->form);
	}
	
	public function seekFromAction() {
		try {
			$data['from-agency-id'] = null;
			$data['from-agency-name'] = null;
			$data['flash-message'] = null;
			$data[$this->form->getTableFrom()->getName()] = $this->form->getTableFrom();
			$params = $this->request->getQuery();
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->find(Agency::getClass(), (int) $params['query']);
			if ( ! $entity instanceof Agency || $entity->isGovernment() ) {
				throw new NotFoundEntityException('Órgão <em>#' . $id . '</em> não encontrado.');
			}
			$data['from-agency-id'] = $entity->getCode();
			$data['from-agency-name'] = $entity->getName();
			
			$this->session->page_from = isset($params['page']) ? $params['page'] : 1;
			$ds = $this->createDataSource($entity);
			$ds->setPage($this->session->page_from);
			
			$toggle = new TgAjax(new Action($this, 'seekFrom', $params), $this->form->getTableFrom(), TgAjax::Json);
			
			$this->form->getTableFrom()->setDataSource($ds);
			$this->form->getTableFrom()->buildPagination($toggle);
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function seekToAction() {
		try {
			$data['to-agency-id'] = null;
			$data['to-agency-name'] = null;
			$data['flash-message'] = null;
			$data[$this->form->getTableTo()->getName()] = $this->form->getTableTo();
			$params = $this->request->getQuery();
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->find(Agency::getClass(), (int) $params['query']);
			if ( ! $entity instanceof Agency || $entity->isGovernment() ) {
				throw new NotFoundEntityException('Órgão <em>#' . $id . '</em> não encontrado.');
			}
			$data['to-agency-id'] = $entity->getCode();
			$data['to-agency-name'] = $entity->getName();
			
			$ds = $this->createDataSource($entity);
			$ds->setPage(isset($params['page']) ? $params['page'] : 1);
			
			$toggle = new TgAjax(new Action($this, 'seekTo', $params), $this->form->getTableTo(), TgAjax::Json);
			
			$this->form->getTableTo()->setDataSource($ds);
			$this->form->getTableTo()->buildPagination($toggle);
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
	
	/**
	 * 
	 * @param Agency $entity
	 * @return EntityDatasource
	 */
	private function createDataSource(Agency $entity) {
		$query = $this->getEntityManager()->getRepository(FleetItem::getClass())->createQueryBuilder('f');
		$query->where('f.responsibleUnit = :agency');
		$query->setParameter('agency', $entity->getId());
		return new EntityDatasource($query, ['limit' => 25]);
	}
	
}
?>