<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\VehicleFamily;
use Gesfrota\Model\Domain\VehicleMaker;
use Gesfrota\Model\Domain\VehicleModel;
use Gesfrota\View\Layout;
use Gesfrota\View\VehicleFamilyTable;
use Gesfrota\View\VehicleMakerTable;
use Gesfrota\View\VehicleModelForm;
use Gesfrota\View\VehicleModelList;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\Widget\PanelQuery;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class VehicleModelController extends AbstractController { 
	
	public function indexAction() {
		$result = $this->getEntityManager()->getRepository(VehicleFamily::getClass())->findAll();
		$families = [0 => 'Todas'];
		foreach ($result as $family) {
			$families[$family->getId()] = $family->getName();
		}
		$list = new VehicleModelList(new Action($this), new Action($this, 'new'), new Action($this, 'edit'), new Action($this, 'active'), $families);
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, null, ['limit' => 20, 'processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['name']) ) {
			        $query->join('u.maker', 'p');
			        $query->andWhere("u.name LIKE :query OR p.name LIKE :query OR CONCAT(p.name, ' ', u.name) LIKE :query");
			        $query->setParameter('query', '%' . $data['name'] . '%');
				}
				
				if ( !empty($data['family']) ) {
					$query->join('u.family', 'f');
					$query->andWhere('f.id = :family');
					$query->setParameter('family', $data['family']);
				}
				if ( !empty($data['only-active']) ) {
					$query->andWhere('u.active = true');
				}
			}]);
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
				$this->setAlert(new Alert('<strong>Ok! </strong>Modelo de Veículo <em>#' . $entity->code . ' ' . $entity->name . '</em> criada com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível editar a Modelo de Veículo. Modelo de Veículo <em>#' . $id . '</em> não encontrada.'));
			if ( $helper->update($form, $id) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Modelo de Veículo <em>#' . $entity->code . ' ' . $entity->name .  '</em> alterada com sucesso!', Alert::Success));
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
			$helper->setException(new NotFoundEntityException('Não foi possível ativar/desativar a Modelo de Veículo. Modelo de Veículo <em>#' . $id . '</em> não encontrada.'));
			$helper->active($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Modelo de Veículo <em>#' . $entity->code . ' ' . $entity->name . '</em> ' . ( $entity->active ? 'ativada' : 'desativada' ) . ' com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ){
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function searchFamilyAction() {
	    try {
	        $query = $this->getEntityManager()->getRepository(VehicleFamily::getClass())->createQueryBuilder('u');
	        $query->andWhere('u.active = true');
	        $params = $this->request->getQuery();
	        if ( $params['query'] ) {
	            $query->andWhere('u.name LIKE :name');
	            $query->setParameter('name', '%' . $params['query'] . '%');
	        }
	        $datasource = new EntityDatasource($query);
	        $datasource->setPage(isset($params['page']) ? $params['page']: 1);
	        $table = new VehicleFamilyTable(new Action($this,'searchFamily', $params));
	        $table->setDataSource($datasource);
	        $widget = new PanelQuery($table, new Action($this,'searchFamily', $params), $params['query'], $this->createForm(new Action($this))->getModalFamily());
	    } catch ( \Exception $e ) {
	        $widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
	    }
	    return new Layout($widget, null);
	}
	
	public function seekFamilyAction() {
	    try {
	        $id = $this->request->getQuery('query');
	        $entity = $this->getEntityManager()->find(VehicleFamily::getClass(), (int) $id);
	        if ( ! ( $entity || $entity->getActive()) ) {
	            throw new NotFoundEntityException('Família de Veículo <em>#' . $id . '</em> não encontrada.');
	        }
	        return new JsonView(array('vehicle-family-id' => $entity->code, 'vehicle-family-name' => $entity->name, 'flash-message' => null), false);
	    } catch ( NotFoundEntityException $e ){
	        return new JsonView(array('vehicle-family-name' => '', 'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())), false);
	    } catch ( \Exception $e ) {
	        return new JsonView(array('vehicle-family-name' => '', 'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error)), false);
	    }
	}
	
	public function searchMakerAction() {
	    try {
	        $query = $this->getEntityManager()->getRepository(VehicleMaker::getClass())->createQueryBuilder('u');
	        $query->andWhere('u.active = true');
	        $params = $this->request->getQuery();
	        if ( $params['query'] ) {
	            $query->andWhere('u.name LIKE :name');
	            $query->setParameter('name', '%' . $params['query'] . '%');
	        }
	        $datasource = new EntityDatasource($query);
	        $datasource->setPage(isset($params['page']) ? $params['page']: 1);
	        $table = new VehicleMakerTable(new Action($this,'searchMaker', $params));
	        $table->setDataSource($datasource);
	        $widget = new PanelQuery($table, new Action($this,'searchMaker', $params), $params['query'], $this->createForm(new Action($this))->getModalMaker());
	    } catch ( \Exception $e ) {
	        $widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
	    }
	    return new Layout($widget, null);
	}
	
	public function seekMakerAction() {
	    try {
	        $id = $this->request->getQuery('query');
	        $entity = $this->getEntityManager()->find(VehicleMaker::getClass(), (int) $id);
	        if ( ! ($entity || $entity->getActive()) ) {
	            throw new NotFoundEntityException('Fabricante de Veículo <em>#' . $id . '</em> não encontrada.');
	        }
	        return new JsonView(array('vehicle-maker-id' => $entity->code, 'vehicle-maker-name' => $entity->name, 'flash-message' => null), false);
	    } catch ( NotFoundEntityException $e ){
	        return new JsonView(array('vehicle-maker-name' => '', 'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())), false);
	    } catch ( \Exception $e ) {
	        return new JsonView(array('vehicle-maker-name' => '', 'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error)), false);
	    }
	}
	
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
	    return new Crud($this->getEntityManager(), VehicleModel::getClass(), $this);
	}
	
	/**
	 * @param Action $submit
	 * @return VehicleModelForm
	 */
	private function createForm ( Action $submit = null) {
	    $seek1= new Action($this, 'seekFamily');
	    $search1 = new Action($this, 'searchFamily');
	    $seek2 = new Action($this, 'seekMaker');
	    $search2= new Action($this, 'searchMaker');
	    return new VehicleModelForm($submit, $seek1, $search1, $seek2, $search2, new Action($this));
	}
	
}
?>