<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Controller\Helper\SearchAgency;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Disposal;
use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\Model\Domain\Fleet;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Manager;
use Gesfrota\Model\Domain\Place;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\View\DisposalAppraisalForm;
use Gesfrota\View\DisposalAssetsTable;
use Gesfrota\View\DisposalChooseForm;
use Gesfrota\View\DisposalConfirmForm;
use Gesfrota\View\DisposalItemForm;
use Gesfrota\View\DisposalItemTable;
use Gesfrota\View\DisposalList;
use Gesfrota\View\DisposalSurveyEquipamentForm;
use Gesfrota\View\DisposalSurveyVehicleForm;
use Gesfrota\View\Layout;
use Gesfrota\View\Widget\ArrayDatasource;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Tooltip\Tooltip;

class DisposalController extends AbstractController {
	
	use SearchAgency;

	public function indexAction() {
		$this->setAgencySelected(null);
		$showAgencies = $this->getShowAgencies();
		$isManager = $this->getUserActive() instanceof Manager;
		$query = $this->getEntityManager()->getRepository(Disposal::getClass())->createQueryBuilder('u');
		
		if (! $showAgencies ) {
			$query->join('u.requesterUnit', 'r');
			$query->andWhere('r.id = :unit');
			$query->setParameter('unit', $this->getAgencyActive()->getId());
		}

		$filter = new Action($this);
		$new = new Action($this, 'new');
		$remove = new Action($this, 'delete');
		$print = new Action($this, 'print');
		$do = new Action($this);
		$doClosure = function( Button $button, Disposal $obj ) use ($isManager) {
		    if (! $isManager ) {
		        
		        switch ($obj->getStatus()) {
		            case Disposal::DRAFTED:
		                $for = 'edit';
		                $button->setTooltip(new Tooltip('Avaliar Disposição'));
		                $button->setIcon(new Icon('icon-pencil'));
		                break;
		                
		            default:
		                $for = 'view';
		                $button->setTooltip(new Tooltip('Visualizar Disposição'));
		                $button->setIcon(new Icon('icon-search'));
		                break;
		        }
		    } else {
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
    		       
    		        case Disposal::DRAFTED:
    		            $for = 'edit';
    		            $button->setTooltip(new Tooltip('Avaliar Disposição'));
    		            $button->setIcon(new Icon('icon-pencil'));
    		            break;
    		            
    		        default:
    		        	$for = 'view';
    		        	$button->setTooltip(new Tooltip('Visualizar Disposição'));
    		        	$button->setIcon(new Icon('icon-search'));
    		        	break;
    		    }
		    }
		    $button->getToggle()->getAction()->setMethodName($for);
		};
		$list = new DisposalList($filter, $new, $remove, $do, $doClosure, $print, $showAgencies);
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $query, array(
				'limit' => 20,
				'processQuery' => function (QueryBuilder $query, array $data) {
					if (!empty($data['agency'])) {
						$query->andWhere('u.requesterUnit = :agency');
						$query->setParameter('agency', $data['agency']);
					}
					if (! empty($data['description'])) {
						$query->andWhere('u.description LIKE :query');
						$query->setParameter('query', '%' . $data['description'] . '%');
					}

					if ( !empty($data['date-initial']) ) {
						$query->andWhere('u.requestedAt >= :initial');
						$query->setParameter('initial', $data['date-initial']);
					}
					if ( !empty($data['date-final']) ) {
						$query->andWhere('u.requestedAt <= :final');
						$query->setParameter('final', $data['date-final'] . ' 23:59:59');
					}
					if ( !empty($data['status']) ) {
						$query->andWhere('u.status IN (:status)');
						$query->setParameter('status', $data['status']);
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
			
			$agency = $this->getAgencySelected();
			
			$new 	= new Action($this, 'new');
			$cancel = new Action($this);
			$seek 	= new Action($this, 'seek-agency');
			$search = new Action($this, 'search-agency');
			$assets = $this->getAssets($agency);
			$showAgencies = $this->getAgencyActive()->isGovernment();
			
			$form = new DisposalChooseForm($new, $cancel, $seek, $search, $assets, $showAgencies);
			
			$helper = $this->createHelperCrud();
			if ($helper->create($form, new Disposal($agency))) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Disposição <em>#' . $entity->code . ' ' . $entity->description . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/edit/' . $entity->id);
			}
		} catch (NotFoundEntityException $e) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
			$this->forward('/');
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
		    $table = new DisposalItemTable(new Action($this, 'survey-asset'), new Action($this, 'print-asset'), new Action($this, 'remove-asset'));
			$table->setDataSource(new ArrayDatasource($entity->getAllAssets(), 'id'));
			$form = new DisposalAppraisalForm($entity, new Action($this, 'edit', ['key' => $id]), new Action($this, 'print', ['key' => $id]), new Action($this), $table);
			
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
		} catch (NotFoundEntityException $e) {
		    $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		    $this->forward('/');
		}
		return new Layout($form);
	}
	
	public function viewAction() {
	    try {
	        $id = (int) $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(Disposal::getClass(), $id);
	        if (! $entity instanceof Disposal) {
	            throw new NotFoundEntityException('Não foi possível editar a Disposição. Disposição <em>#' . $id . '</em> não encontrada.');
	        }
	        $form = new DisposalConfirmForm($entity, new Action($this), new Action($this, 'print-asset'), new Action($this, 'print', ['key' => $id]));
	    } catch (NotFoundEntityException $e) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	        $this->forward('/');
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
	        
	        $form = new DisposalConfirmForm($entity, new Action($this), new Action($this, 'print-asset'), new Action($this, 'print', ['key' => $id]), new Action($this, 'confirm', ['key' => $id, 'do' => 'confirmed']), new Action($this, 'confirm', ['key' => $id, 'do' => 'declined']));
	        
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
			$content = [];
			foreach($entity->getAllAssets() as $item) {
			    $view = new Layout('disposal/print-asset.phtml', null);
			    $view->disposalItem = $item;
			    $content[] = $view->render();
			}
			$layout = new Layout(implode('<hr class="no-print">', $content), 'layout/print.phtml');
		} catch (\Exception $e) {
		    $layout = new Layout(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger), 'layout/print.phtml');
		}
		return $layout;
	}
	
	public function printAssetAction() {
	    $layout = new Layout('disposal/print-asset.phtml', 'layout/print.phtml');
	    try {
	        $id = (int) $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(DisposalItem::getClass(), $id);
	        if (! $entity instanceof DisposalItem) {
	            throw new NotFoundEntityException('Não foi possível imprimir o Ativo. Ativo <em>#' . $id . '</em> não encontrado.');
	        }
	        $layout->disposalItem = $entity;
	    } catch (\Exception $e) {
	        $layout = new Layout(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger), 'layout/print.phtml');
	    }
	    return $layout;
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
			$this->setAgencySelected($entity);
			$data['agency-id'] = $entity->getCode();
			$data['agency-name'] = $entity->getName();
			
			$table = new DisposalAssetsTable('assets');
			$table->setDataSource(new ArrayDatasource($this->getAssets($entity), 'id'));
			$fakeForm = new DisposalChooseForm(new Action($this), new Action($this), new Action($this), new Action($this), []);
			$table->prepare($fakeForm->getBuilderForm());
			
			$data[$table->getName()] = $table;
			
		} catch ( NotFoundEntityException $e ){
			$data['flash-message'] = new Alert('<strong>Ops! </strong>' . $e->getMessage());
		} catch ( \Exception $e ) {
			$data['flash-message'] = new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Error);
		}
		return new JsonView($data, false);
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
	
	
	private function getAssets(Agency $agency) {
		if ( $agency->isGovernment() ) {
			return [];
		}
		$query = $this->getEntityManager()
			->getRepository(FleetItem::getClass())
			->createQueryBuilder('u');
		$query->join('u.responsibleUnit', 'r');
		$query->andWhere('r.id = :unit');
		$query->setParameter('unit', $agency->getId());
		
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
		
		return $query->getQuery()->getResult();
		
	}

	/**
	 *
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), Disposal::getClass(), $this);
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