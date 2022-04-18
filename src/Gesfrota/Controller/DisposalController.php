<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\Disposal;
use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\Model\Domain\Fleet;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Owner;
use Gesfrota\Model\Domain\OwnerCompany;
use Gesfrota\Model\Domain\OwnerPerson;
use Gesfrota\Model\Domain\Place;
use Gesfrota\Model\Domain\ServiceCard;
use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\VehicleMaker;
use Gesfrota\Model\Domain\VehicleModel;
use Gesfrota\View\DisposalAppraisalForm;
use Gesfrota\View\DisposalChooseForm;
use Gesfrota\View\DisposalConfirmForm;
use Gesfrota\View\DisposalItemForm;
use Gesfrota\View\DisposalItemTable;
use Gesfrota\View\DisposalList;
use Gesfrota\View\DisposalSurveyEquipamentForm;
use Gesfrota\View\DisposalSurveyVehicleForm;
use Gesfrota\View\FleetEquipmentForm;
use Gesfrota\View\FleetOwnerForm;
use Gesfrota\View\FleetVehicleForm;
use Gesfrota\View\Layout;
use Gesfrota\View\OwnerTable;
use Gesfrota\View\ServiceCardForm;
use Gesfrota\View\VehicleModelTable;
use Gesfrota\View\Widget\ArrayDatasource;
use Gesfrota\View\Widget\BuilderForm;
use Gesfrota\View\Widget\EntityDatasource;
use Gesfrota\View\Widget\PanelQuery;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Tooltip\Tooltip;
use Gesfrota\Model\Domain\Manager;


class DisposalController extends AbstractController {

	public function indexAction() {
		$this->session->cards = null;

		$query = $this->getEntityManager()
			->getRepository(Disposal::getClass())
			->createQueryBuilder('u');
		$query->join('u.requesterUnit', 'r');
		$query->andWhere('r.id = :unit');
		$query->setParameter('unit', $this->getAgencyActive()
			->getId());

		$isManager = ! $this->getUserActive() instanceof Manager;
		$filter = new Action($this);
		$new = new Action($this, 'new');
		$remove = new Action($this, 'delete');
		$print = new Action($this, 'edit');
		$do = new Action($this);
		$doClosure = function( Button $button, Disposal $obj ) use($isManager) {
			$button->setDisabled(!$isManager);
		    switch ($obj->getStatus()) {
		        case Disposal::DECLINED:
		        case Disposal::CONFIRMED: 
		        	$for = 'devolve';
		        	$button->setTooltip(new Tooltip('Devolver Disposição'));
		        	$button->setIcon(new Icon('icon-backward'));
		        	break;
		            
		        case Disposal::REQUESTED:
		            $for = 'confirm';
		            $button->setTooltip(new Tooltip('Confirmar Disposição'));
		            $button->setIcon(new Icon('icon-ok'));
		            break;
		            
		        default:
		        	$for = 'edit';
		        	$button->setTooltip(new Tooltip('Avaliar Disposição'));
		        	$button->setIcon(new Icon('icon-pencil'));
		        	$button->setDisabled(false);
		        	break;
		           
		    }
		    $button->getToggle()->getAction()->setMethodName($for);
		};
		$list = new DisposalList($filter, $new, $remove, $do, $doClosure, $print, $isManager);
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $query, array(
				'limit' => 12,
				'processQuery' => function (QueryBuilder $query, array $data) {
					if (! empty($data['description'])) {
						$q1 = $this->getEntityManager()
							->getRepository(Vehicle::getClass())
							->createQueryBuilder('v');
						$q1->select('v.id');
						$q1->join('v.model', 'm1');
						$q1->join('m1.maker', 'm2');
						$q1->where('m1.name LIKE :query');
						$q1->orWhere('m2.name LIKE :query');
						$q1->orWhere("CONCAT(m2.name, ' ', m1.name) LIKE :query");

						$q2 = $this->getEntityManager()
							->getRepository(Equipment::getClass())
							->createQueryBuilder('e');
						$q2->select('e.id');
						$q2->andWhere('e.description LIKE :query');

						$query->andWhere('u.id IN (' . $q1->getDQL() . ') OR u.id IN (' . $q2->getDQL() . ')');
						$query->setParameter('query', '%' . $data['description'] . '%');
					}

					if (! empty($data['engine'])) {
						$query->andWhere('u.engine IN (:engine)');
						$query->setParameter('engine', $data['engine']);
					}
					if (! empty($data['fleet'])) {}
					if (! empty($data['only-active'])) {
						$query->andWhere('u.active = true');
					}
				}
			));
			$list->setAlert($this->getAlert());
		} catch (\Exception $e) {
			$list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($list);
	}

	public function newAction() {
		try {
			$query = $this->getEntityManager()
				->getRepository(FleetItem::getClass())
				->createQueryBuilder('u');
			$query->join('u.responsibleUnit', 'r');
			$query->andWhere('r.id = :unit');
			$query->setParameter('unit', $this->getAgencyActive()
				->getId());
	
			$query->andWhere('u.fleet = :fleet');
			$query->setParameter('fleet', Fleet::OWN);
	
			$q1 = $this->getEntityManager()
				->getRepository(DisposalItem::getClass())
				->createQueryBuilder('v');
			$q1->select('IDENTITY(v.asset)');
			$q1->join('v.disposal', 'd');
			$q1->where('d.status NOT IN (:disposal)');
			$query->andWhere('u.id NOT IN (' . $q1->getDQL() . ')');
			$query->setParameter('disposal', [Disposal::DECLINED]);
			
			$assets = $query->getQuery()->getResult();
			
			$id = (int) $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(FleetItem::getClass(), $id);
			
			$form = new DisposalChooseForm(new Action($this, 'new'), new Action($this), $assets);
			
			$helper = $this->createHelperCrud();
			if ($helper->create($form, new Disposal($this->getAgencyActive()))) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Disposição <em>#' . $entity->code . ' ' . $entity->description . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/edit/' . $entity->id);
			}
		} catch (InvalidRequestDataException $e) {
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch (\Exception $e) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}

	public function editAction() {
		try {
			$id = (int) $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Disposal::getClass(), $id);
			if (! $entity instanceof Disposal) {
				throw new NotFoundEntityException('Não foi possível editar a Disposição. Disposição <em>#' . $id . '</em> não encontrada.');
			}
			
			if ($entity->getStatus() == Disposal::DRAFTED) {
				$table = new DisposalItemTable(new Action($this, 'survey-asset'), new Action($this, 'remove-asset'));
				$table->setDataSource(new ArrayDatasource($entity->getAllAssets(), 'id'));
				$form = new DisposalAppraisalForm($entity, new Action($this, 'edit', ['key' => $id]), new Action($this), $table);
			} else {
				$form = new DisposalConfirmForm($entity, new Action($this), new Action($this, 'view-asset'), new Action($this, 'print', ['key' => $id]) );
			}
			
			if ( $this->request->isPost() ) {
				if ( ! $form->valid() ) {
					throw new InvalidRequestDataException();
				}
				$entity->toRequest($this->getUserActive());
				$this->getEntityManager()->flush();
				$this->setAlert(new Alert('<strong>Ok! </strong>Ativo <em>' . $entity->code . ' ' . $entity->description . '</em> avaliado com sucesso!', Alert::Success));
				$this->forward('/');
			} 
			$form->setAlert($this->getAlert());
			
		} catch (InvalidRequestDataException $e) {
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch (\Exception $e) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function confirmAction() {
	    try {
	        $id = (int) $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(Disposal::getClass(), $id);
	        if (! $entity instanceof Disposal) {
	            throw new NotFoundEntityException('Não foi possível confirmar a Disposição. Disposição <em>#' . $id . '</em> não encontrada.');
	        }
	        
	        $form = new DisposalConfirmForm($entity, new Action($this), new Action($this, 'view-asset'), new Action($this, 'confirm', ['key' => $id, 'do' => 'confirmed']), new Action($this, 'confirm', ['key' => $id, 'do' => 'declined']));
	        
	        if ( $this->request->isPost() ) {
	            $do = $this->request->getQuery('do');
	            switch ($do) {
	                case 'confirmed':
	                    $entity->toConfirm($this->getUserActive());
	                    break;
	                    
	                case 'declined':
	                    $form->bind($this->request->getPost());
	                    if ( ! $form->valid() ) {
	                        throw new InvalidRequestDataException();
	                    }
	                    $data = $form->getBuilderForm()->getData();
	                    $entity->toDecline($this->getUserActive(), $data['justify']);
	                    break;
	                    
	                default:
	                    throw new \InvalidArgumentException('Não foi possível confirmar Disposição. Operação "<em>'. $do .'"</em> inválida.');
	            }
	            
	            $this->getEntityManager()->flush();
	            $this->setAlert(new Alert('<strong>Ok! </strong>Ativo <em>' . $entity->code . ' ' . $entity->description . '</em> avaliado com sucesso!', Alert::Success));
	            $this->forward('/');
	        }
	        $form->setAlert($this->getAlert());
	        
	    } catch (InvalidRequestDataException $e) {
	        $form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch (\Exception $e) {
	        $form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	    }
	    return new Layout($form);
	}
	
	public function deleteAction() {
		try {
			$id = $this->request->getQuery('key');
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível excluir a Disposição. Disposição <em>#' . $id . '</em> não encontrada.'));
			$helper->delete($id);
			$entity = $helper->getEntity();
			$this->setAlert(new Alert('<strong>Ok! </strong>Disposição <em>#' . $id . ' ' . $entity->description . '</em> excluída com sucesso!', Alert::Success));
		} catch (NotFoundEntityException $e) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch (\Exception $e) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function devolveAction() {
	    try {
	        $id = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(Disposal::getClass(), $id);
	        if (! $entity instanceof Disposal) {
	            throw new NotFoundEntityException('Não foi possível devolver a Disposição. Disposição <em>#' . $id . '</em> não encontrada.');
	        }
	        $entity->toDevolve();
	        $this->getEntityManager()->flush();
	        $this->setAlert(new Alert('<strong>Ok! </strong>Disposição <em>#' . $id . ' ' . $entity->description . '</em> devolvida para ' . $entity->requesterUnit . ' com sucesso!', Alert::Success));
	    } catch (NotFoundEntityException $e) {
	        $this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch (\Exception $e) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	    }
	    $this->forward('/');
	}
	
	public function printAction() {
		try {
			$id = (int) $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Disposal::getClass(), $id);
			if (! $entity instanceof Disposal) {
				throw new NotFoundEntityException('Não foi possível imprimir Disposição. Disposição <em>#' . $id . '</em> não encontrada.');
			}
			$view = new Box();
			$view->setName('disposal-view');
			foreach($entity->getAllAssets() as $item) {
				$page = new DisposalItemForm($item);
				$page->extract($item);
				$view->append($page->getPanel());
			}
		} catch (\Exception $e) {
			$view = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger);
		}
		return new Layout($view, 'layout/print.phtml');
	}
	
	public function locationAction() {
		try {
			$places = Place::autocomplete($this->request->getQuery('query'));
			$options = [];
			foreach( $places as $obj ) {
				$options[] = ['label' => $obj->getDescription(),
					'value' => $obj->getPlace()
				];
			}
			return new JsonView($options, false);
		} catch (\ErrorException $e) {
			return new JsonView(['error' => $e->getMessage()], false);
		}
	}
	
	public function surveyAssetAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(DisposalItem::getClass(), ( int ) $id);
			if ( ! $entity instanceof DisposalItem ) {
				throw new NotFoundEntityException('Não foi possível avaliar o Ativo. Ativo <em>#' . $id . '</em> não encontrado.');
			}
			if ($entity->getAsset() instanceof Vehicle) {
				$form = new DisposalSurveyVehicleForm($entity, new Action($this, 'survey-asset', ['key' => $id]), new Action($this, 'edit', ['key' => $entity->getDisposal()->getId()]), new Action($this, 'location'));
			} else {
				$form = new DisposalSurveyEquipamentForm($entity, new Action($this, 'survey-asset', ['key' => $id]), new Action($this, 'edit', ['key' => $entity->getDisposal()->getId()]), new Action($this, 'location'));
			}
			if ( $this->request->isPost() ) {
				$form->bind($this->request->getPost());
				if ( ! $form->valid() ) {
					throw new InvalidRequestDataException();
				}
				$form->hydrate($entity, $this->getEntityManager());
				$this->getEntityManager()->flush();
				$this->setAlert(new Alert('<strong>Ok! </strong>Ativo <em>' . $entity->code . ' ' . $entity->description . '</em> avaliado com sucesso!', Alert::Success));
				$this->forward('/edit/' . $entity->getDisposal()->getId());
			} 
			$form->extract($entity);
		} catch (NotFoundEntityException $e) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
			$this->forward('/');
		} catch (InvalidRequestDataException $e) {
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch (\Exception $e) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function removeAssetAction() {
	    try {
	        $id = $this->request->getQuery('key');
	        $entity = $this->em->find(DisposalItem::getClass(), ( int ) $id);
	        if ( ! $entity instanceof DisposalItem ) {
        	    throw new NotFoundEntityException('Não foi possível remover o Ativo. Ativo <em>#' . $id . '</em> não encontrado.');
        	}
        	$this->getEntityManager()->remove($entity);
        	$this->getentitymanager()->flush();
        	
        	$disposal = $entity->getDisposal();
        	
        	$table = new DisposalItemTable(new Action($this, 'survey-asset'), new Action($this, 'remove-asset'));
        	$table->setDataSource(new ArrayDatasource($disposal->getAllAssets(), 'id'));
        	
        	$data[$table->getName()] = $table;
        	$data['alert-message'] = new Alert('<strong>Ok! </strong>Ativo <em>' . $entity->getAsset()->getCode() . ' ' . $entity->description . '</em> removido com sucesso!', Alert::Success);
        	$data['assets-total'] = $disposal->getTotalAssets();
        	$data['assets-value'] = $disposal->getTotalAssetsValued();
        	$data['assets-count'] = $disposal->getTotalAssetsValued() . ' / ' . $disposal->getTotalAssets();
	    } catch (NotFoundEntityException $e) {
	    	$data['alert-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
	    } catch (\Exception $e) {
	    	$data['alert-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger);
	    }
	    return new JsonView($data, false);
	}
	
	public function viewAssetAction() {
	   try {
	        $id = (int) $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(DisposalItem::getClass(), $id);
	        if (! $entity instanceof DisposalItem) {
	            throw new NotFoundEntityException('Não foi possível visualizar o Ativo. Ativo <em>#' . $id . '</em> não encontrado.');
	        }
	        
	        $view = new DisposalItemForm($entity);
	        $view->extract($entity);
	    } catch (\Exception $e) {
	        $view = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger);
	    } 
	    return new Layout($view, 'layout/print.phtml');
	}
	
	public function seekOwnerAction() {
		try {
			$data['owner-id'] = '';
			$data['owner-name'] = '';
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()
				->getRepository(Owner::getClass())
				->findOneBy([
				'id' => $id,
				'active' => true
			]);
			if (! $entity instanceof Owner) {
				throw new NotFoundEntityException('Proprietário <em>#' . $id . '</em> não encontrado.');
			}
			$data['owner-id'] = $entity->getCode();
			$data['owner-name'] = $entity->getName();
			$data['flash-message'] = null;
		} catch (NotFoundEntityException $e) {
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch (\Exception $e) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}

	public function searchOwnerAction() {
		try {
			$query = $this->getEntityManager()
				->getRepository(Owner::getClass())
				->createQueryBuilder('u');
			$query->distinct(true);
			$params = $this->request->getQuery();
			$query->andWhere('u.active = true');
			if ($params['query']) {
				$query->andWhere('u.name LIKE :query');
				$query->orWhere('u.nif LIKE :query');
				$query->setParameter('query', '%' . $params['query'] . '%');
			}

			$datasource = new EntityDatasource($query);
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new OwnerTable(new Action($this, 'searchOwner', $params));
			$table->setDataSource($datasource);
			$modal = $this->createForm(Vehicle::getClass(), new Action($this))->getModalOwner();
			$widget = new PanelQuery($table, new Action($this, 'searchOwner', $params), $params['query'], $modal);
		} catch (\Exception $e) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}

	public function newOwnerPersonAction() {
		$form = new FleetOwnerForm(OwnerPerson::getClass(), new Action($this, 'newOwnerPerson'));
		if ($this->request->isPost()) {
			try {
				$form->bind($this->request->getPost());
				if (! $form->valid()) {
					throw new InvalidRequestDataException();
				}
				$data = $form->getData();
				$owner = new OwnerPerson();
				$owner->setName($data['name']);
				$owner->setNif($data['nif']);
				$this->em->persist($owner);
				$this->em->flush();
				$data['owner-id'] = $owner->getCode();
				$data['owner-name'] = $owner->getName();
				$data['alert-message'] = null;
			} catch (\Exception $e) {
				$data['alert-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
			}
			return new JsonView($data, false);
		}
		return new Layout($form, null);
	}

	public function newOwnerCompanyAction() {
		$form = new FleetOwnerForm(OwnerCompany::getClass(), new Action($this, 'newOwnerCompany'));
		if ($this->request->isPost()) {
			try {
				$form->bind($this->request->getPost());
				if (! $form->valid()) {
					throw new InvalidRequestDataException();
				}
				$data = $form->getData();
				$owner = new OwnerCompany();
				$owner->setName($data['name']);
				$owner->setNif($data['nif']);
				$this->em->persist($owner);
				$this->em->flush();
				$data['owner-id'] = $owner->getCode();
				$data['owner-name'] = $owner->getName();
				$data['alert-message'] = null;
			} catch (\Exception $e) {
				$data['alert-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
			}
			return new JsonView($data, false);
		}
		return new Layout($form, null);
	}

	public function seekVehiclePlateAction() {
		try {
			$plate = strtoupper($this->request->getQuery('query'));
			$data['plate'] = $plate;
			$entity = $this->getEntityManager()
				->getRepository(Vehicle::getClass())
				->findOneBy([
				'plate' => $plate
			]);
			$data['flash-message'] = null;
			if ($entity instanceof Vehicle) {
				throw new \DomainException('Veículo <em>' . $entity->getPlate() . ' ' . $entity->getDescription() . '</em> já está registrado em ' . $entity->getResponsibleUnit()->getAcronym());
			}
		} catch (\DomainException $e) {
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch (\Exception $e) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . get_class($e) . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}

	public function searchVehiclePlateAction() {
		try {
			$plate = strtoupper($this->request->getQuery('query'));
			$entity = $this->getEntityManager()
				->getRepository(Vehicle::getClass())
				->findOneBy([
				'plate' => $plate
			]);
			$data['vehicle-plate'] = $entity->getPlate();
			$data['vehicle-description'] = $entity->getDescription();
			$data['responsible-unit-description'] = $entity->getResponsibleUnit()->getName();
			$data['flash-message'] = null;
		} catch (\DomainException $e) {
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch (\Exception $e) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . get_class($e) . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}

	public function seekVehicleAction() {
		try {
			$data['vehicle-model-id'] = '';
			$data['vehicle-model-name'] = '';
			$data['vehicle-maker-id'] = '';
			$data['vehicle-maker-name'] = '';
			$data['vehicle-family-id'] = '';
			$data['vehicle-family-name'] = '';
			$id = $this->request->getQuery('query');
			$entity = $this->getEntityManager()->find(VehicleModel::getClass(), (int) $id);
			if (! $entity instanceof VehicleModel) {
				throw new NotFoundEntityException('Modelo de Veículo <em>#' . $id . '</em> não encontrado.');
			}
			$data['vehicle-model-id'] = $entity->getCode();
			$data['vehicle-model-name'] = $entity->getName();
			$data['vehicle-maker-id'] = $entity->getMaker()->getCode();
			$data['vehicle-maker-name'] = $entity->getMaker()->getName();
			$data['vehicle-family-id'] = $entity->getFamily()->getCode();
			$data['vehicle-family-name'] = $entity->getFamily()->getName();
			$data['flash-message'] = null;
		} catch (NotFoundEntityException $e) {
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch (\Exception $e) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
	}
	
	public function searchVehicleAction() {
		try {
			$query = $this->getEntityManager()
				->getRepository(VehicleModel::getClass())
				->createQueryBuilder('u');
			$query->distinct(true);
			$params = $this->request->getQuery();
			if ($params['query']) {
				$query->from(VehicleMaker::getClass(), 'p');
				$query->andWhere('u.name LIKE :query');
				$query->orWhere('p.name LIKE :query');
				$query->orWhere("CONCAT(p.name, ' ', u.name) LIKE :query");
				$query->setParameter('query', '%' . $params['query'] . '%');
			}

			$datasource = new EntityDatasource($query);
			$datasource->setPage(isset($params['page']) ? $params['page'] : 1);
			$table = new VehicleModelTable(new Action($this, 'searchVehicle', $params));
			$table->setDataSource($datasource);
			$modal = $this->createForm(Vehicle::getClass(), new Action($this))->getModalVehicleModel();
			$widget = new PanelQuery($table, new Action($this, 'searchVehicle', $params), $params['query'], $modal);
		} catch (\Exception $e) {
			$widget = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new Layout($widget, null);
	}

	public function transferVehicleAction() {
		try {
			$plate = strtoupper($this->request->getPost('vehicle-plate'));
			$entity = $this->getEntityManager()
				->getRepository(Vehicle::getClass())
				->findOneBy([
				'plate' => $plate
			]);
			if ($entity === null) {
				throw new \DomainException('Veículo <em>' . $plate . '</em> não encontrado.');
			}
			$entity->setResponsibleUnit($this->getAgencyActive());
			$this->getEntityManager()->flush();
			$this->setAlert(new Alert('<strong>Ok! </strong>Veículo <em>#' . $entity->code . ' ' . $entity->description . '</em> transferido com sucesso!', Alert::Success));
		} catch (NotFoundEntityException $e) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		}
		$this->forward('/');
	}

	public function addCardAction() {
		try {
			$form = $this->createSubform();
			$form->bind($this->request->getPost());
			$table = $form->getTableCollection();
			$data = $form->getData();
			$provider = $this->getEntityManager()->find(ServiceProvider::getClass(), (int) $data['service-provider-id']);
			if (! $provider) {
				throw new NotFoundEntityException('Não foi possível adicionar Cartão de Serviço. Provedor de Serviço <em>#' . $data['service-provider-id'] . '</em> não encontrado.');
			}
			$table->addItem(new ServiceCard($data['service-card-number'], $provider));
			$form->setData([]);
			$json = array(
				$form->getName() => $form,
				'flash-message' => null
			);
		} catch (NotFoundEntityException $e) {
			$json = array(
				'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())
			);
		} catch (\Exception $e) {
			$json = array(
				'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger)
			);
		}
		return new JsonView($json, false);
	}

	public function removeCardAction() {
		try {
			$form = $this->createSubform();
			$table = $form->getTableCollection();
			$key = (int) $this->request->getQuery('key');
			if (! $table->removeItem($key)) {
				throw new NotFoundEntityException('Não foi possível remover Cartão de Serviço. Cartão <em>#' . $key . '</em> não encontrado.');
			}
			$json = array(
				$form->getName() => $form,
				'flash-message' => null
			);
		} catch (NotFoundEntityException $e) {
			$json = array(
				'flash-message' => new Alert('<strong>Ops! </strong>' . $e->getMessage())
			);
		} catch (\Exception $e) {
			$json = array(
				'flash-message' => new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger)
			);
		}
		return new JsonView($json, false);
	}

	/**
	 *
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), Disposal::getClass(), $this);
	}

	/**
	 *
	 * @param string|FleetItem $item
	 * @param Action $submit
	 * @return BuilderForm
	 */
	private function createForm($item, Action $submit) {
		$subform = $this->createSubform();
		$cancel = new Action($this);

		if (is_object($item)) {
			$item = get_class($item);
		}
		switch ($item) {
			case Vehicle::getClass():
				$seek['vehicle-plate'] = new Action($this, 'seekVehiclePlate');
				$seek['vehicle'] = new Action($this, 'seekVehicle');
				$seek['owner'] = new Action($this, 'seekOwner');
				$find['vehicle'] = new Action($this, 'searchVehicle');
				$find['owner'] = new Action($this, 'searchOwner');
				$newOwnerPerson = new Action($this, 'newOwnerPerson');
				$newOwnerCompany = new Action($this, 'newOwnerCompany');
				return new FleetVehicleForm($submit, $seek['vehicle-plate'], $seek['vehicle'], $find['vehicle'], $seek['owner'], $find['owner'], $newOwnerPerson, $newOwnerCompany, $cancel, $subform);
				break;

			case Equipment::getClass():
				return new FleetEquipmentForm($submit, $cancel, $subform);
				break;

			default:
				throw new \InvalidArgumentException('Form not implements for ' . $item);
				break;
		}
	}

	/**
	 *
	 * @return ServiceCardForm
	 */
	private function createSubform() {
		$options = [];
		$query = $this->getEntityManager()
			->getRepository(ServiceProvider::getClass())
			->createQueryBuilder('u');
		$query->andWhere('u.active = true');
		$result = $query->getQuery()->getResult();

		foreach ($result as $item) {
			$options[$item->getId()] = $item->getName();
		}
		return new ServiceCardForm(new Action($this, 'addCard'), new Action($this, 'removeCard'), $options, $this->session);
	}
}
?>