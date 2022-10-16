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
use Gesfrota\View\DisposalItemTable;
use Gesfrota\View\DisposalList;
use Gesfrota\View\DisposalSurveyEquipamentForm;
use Gesfrota\View\DisposalSurveyVehicleForm;
use Gesfrota\View\Layout;
use Gesfrota\View\Widget\ArrayDatasource;
use PHPBootstrap\Mvc\View\JsonView;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Tooltip\Tooltip;
use Gesfrota\Model\Domain\DisposalLot;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Form\Controls\Uneditable;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Mvc\View\FileView;

class DisposalController extends AbstractController {
	
	use SearchAgency;

	public function indexAction() {
		$this->setAgencySelected(null);
		$showAgencies = $this->getShowAgencies();
		$isManager = $this->getUserActive() instanceof Manager;
		$query = $this->getEntityManager()->getRepository(Disposal::getClass())->createQueryBuilder('u');
		$query->where('u.id > 0');
		
		if (! $showAgencies ) {
		    $query->andWhere('u.agency = :agency');
		    $query->setParameter('agency', $this->getAgencyActive()->getId());
		}

		$filter = new Action($this);
		$new = new Action($this, 'new');
		$remove = new Action($this, 'delete');
		$print = new Action($this, 'print');
		$export = new Action($this, 'export');
		$lot = $isManager && $showAgencies ? new Action($this, 'make-lot') : null;
		$do = new Action($this);
		$doClosure = function( Button $button, Disposal $obj ) use ($isManager) {
		    if (! $isManager ) {
		        
		        switch ($obj->getStatus()) {
		            case Disposal::DRAFTED:
		                $for = 'appraise';
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
    		        case Disposal::DRAFTED:
    		            $for = 'appraise';
    		            $button->setTooltip(new Tooltip('Avaliar Disposição'));
    		            $button->setIcon(new Icon('icon-pencil'));
    		            break;
    		            
    		        case Disposal::APPRAISED:
    		            $for = 'confirm';
    		            $button->setTooltip(new Tooltip('Confirmar Disposição'));
    		            $button->setIcon(new Icon('icon-ok'));
    		            break;
    		       
    		        case Disposal::DECLINED:
    		        case Disposal::CONFIRMED: 
    		        default:
    		        	$for = 'view';
    		        	$button->setTooltip(new Tooltip('Visualizar Disposição'));
    		        	$button->setIcon(new Icon('icon-search'));
    		        	break;
    		    }
		    }
		    $button->getToggle()->getAction()->setMethodName($for);
		};
		$list = new DisposalList($filter, $new, $remove, $do, $doClosure, $print, $export, $lot, $showAgencies);
		try {
			$helper = $this->createHelperCrud();
			$helper->read($list, $query, array(
			    'sort' => 'u.lft',
				'limit' => 20,
				'processQuery' => function (QueryBuilder $query, array $data) {
					if (!empty($data['agency'])) {
						$query->andWhere('u.agency = :agency');
						$query->setParameter('agency', $data['agency']);
					}
					if (! empty($data['description'])) {
						$query->andWhere('u.description LIKE :query');
						$query->setParameter('query', '%' . $data['description'] . '%');
					}

					if ( !empty($data['date-initial']) ) {
						$query->andWhere('u.openedAt >= :initial');
						$query->setParameter('initial', $data['date-initial']);
					}
					if ( !empty($data['date-final']) ) {
						$query->andWhere('u.openedAt <= :final');
						$query->setParameter('final', $data['date-final'] . ' 23:59:59');
					}
					if ( !empty($data['status']) ) {
						$query->andWhere('u.status IN (:status)');
						$query->setParameter('status', $data['status']);
					}
				}
			));
			if ( $list->getBuilderTable()->getDataSource()->getSort() == 'u.lft') {
			    $list->getBuilderTable()->getDataSource()->setOrderBy('u.lft', 'asc');
			}
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
			$lotNull = $this->getEntityManager()->find(DisposalLot::getClass(), 0);
			if ($helper->create($form, new Disposal($agency, $lotNull))) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Disposição <em>#' . $entity->code . ' ' . $entity->description . '</em> criado com sucesso!', Alert::Success));
				$this->forward('/appraise/' . $entity->id);
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
	
	public function makeLotAction() {
	    try {
	        $lot = new DisposalLot();
	        $lot->setDescription('Lote de Disposição feito em ' . ucfirst(utf8_encode(strftime('%A, %d %B %G %T', strtotime('now')))));
            $disposals = $this->getEntityManager()->getRepository(Disposal::getClass())->findBy(['status' => Disposal::CONFIRMED]);
            if (count($disposals) <= 0) {
                throw new \DomainException('Não foi possível gerar Lote de Disposição. Não há nenhuma disposição confirmada para criar lote.');
            }
            foreach($disposals as $disposal) {
                $lot->addDisposal($disposal);
            }
	        $lot->toForward($this->getUserActive());
	        $this->getEntityManager()->persist($lot);
	        $this->getEntityManager()->flush($lot);
	        $this->getEntityManager()->refresh($lot);
	        $this->getEntityManager()->flush();
	        $this->setAlert(new Alert('<strong>Ok! </strong>Lote de Disposição <em>#' . $lot->code . ' ' . $lot->description . '</em> criado com sucesso!', Alert::Success));
	        $this->forward('/');
	    } catch (\DomainException $e) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	        $this->forward('/');
	    } 
	}

	public function appraiseAction() {
		try {
			$id = (int) $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Disposal::getClass(), $id);
			if (! $entity instanceof Disposal) {
				throw new NotFoundEntityException('Não foi possível editar a Disposição. Disposição <em>#' . $id . '</em> não encontrada.');
			}
			$surveyAsset = new Action($this, 'survey-asset');
			$removeAsset = new Action($this, 'remove-asset');
			$printAsset =  new Action($this, 'print-asset');
			$table = new DisposalItemTable($surveyAsset, $removeAsset, $printAsset);
			$table->setDataSource(new ArrayDatasource($entity->getAllAssets(), 'id'));
			$table->setFooter($entity);
			
			$appraise = new Action($this, 'appraise', ['key' => $id]);
			$print = new Action($this, 'print', ['key' => $id]);
			$export = new Action($this, 'export', ['key' => $id]);
			$cancel = new Action($this);
			$form = new DisposalAppraisalForm($entity, $table, $appraise, $print, $export, $cancel);
			
			if ( $this->request->isPost() ) {
				if ( $entity->getAmountAssets() > $entity->getAmountAssetsAppraise() ) {
				    throw new InvalidRequestDataException('Todos os ativos da disposição devem ser avaliados: apenas <em>'. $entity->getAmountAssetsAppraise() . '/' . $entity->getAmountAssets() . ' ativos </em> foram avaliados.');
				}
				$entity->toAppraise($this->getUserActive());
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
	        $surveyAsset = null;
	        $removeAsset = null;
	        $printAsset =  new Action($this, 'print-asset');
	        $table = new DisposalItemTable($surveyAsset, $removeAsset, $printAsset);
	        $table->setDataSource(new ArrayDatasource($entity->getAllAssets(), 'id'));
	        $table->setFooter($entity);
	        
	        $allowDevolve = $entity->getStatus() == Disposal::DECLINED || $this->getUserActive() instanceof Manager && $entity->getStatus() != Disposal::FORWARDED;
	        $print = new Action($this, 'print', ['key' => $id]);
	        $export = new Action($this, 'export', ['key' => $id]);
	        $cancel = new Action($this);
	        $confirm =  null;
	        $decline = null;
	        $devolve = $allowDevolve ? new Action($this, 'devolve', ['key' => $id]) : null;
	        $form = new DisposalConfirmForm($entity, $table, $print, $export, $cancel, $confirm, $decline, $devolve);
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
	        $surveyAsset = null;
	        $removeAsset = null;
	        $printAsset =  new Action($this, 'print-asset');
	        $table = new DisposalItemTable($surveyAsset, $removeAsset, $printAsset);
	        $table->setDataSource(new ArrayDatasource($entity->getAllAssets(), 'id'));
	        $table->setFooter($entity);
	        
	        $print = new Action($this, 'print', ['key' => $id]);
	        $export = new Action($this, 'export', ['key' => $id]);
	        $cancel = new Action($this);
	        $confirm = new Action($this, 'confirm', ['key' => $id, 'do' => 'confirmed']);
	        $decline = new Action($this, 'confirm', ['key' => $id, 'do' => 'declined']);
	        $devolve = new Action($this, 'devolve', ['key' => $id]);
	        $form = new DisposalConfirmForm($entity, $table, $print, $export, $cancel, $confirm, $decline, $devolve);
	        
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
	        $this->setAlert(new Alert('<strong>Ok! </strong>Disposição <em>#' . $id . ' ' . $entity->description . '</em> devolvida para ' . $entity->agency . ' com sucesso!', Alert::Success));
	    } catch (NotFoundEntityException $e) {
	        $this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	    } catch (\Exception $e) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	    }
	    $this->forward('/');
	}
	
	public function exportAction() {
	    try {
	        $id = $this->request->getQuery('key');
	        $entity = $this->getEntityManager()->find(Disposal::getClass(), $id);
	        if (! $entity instanceof Disposal) {
	            throw new NotFoundEntityException('Não foi possível devolver a Disposição. Disposição <em>#' . $id . '</em> não encontrada.');
	        }
	        $this->response->setHeader('Content-Disposition', 'attachment; filename="disposal-'.$id. '-' . date('YmdHis') . '.csv"');
	        $this->response->setHeader('Content-Type', 'text/csv');
	        $dataset[] = ['ID', 
	                      'CÓD. PATRIMONIAL', 
	                      'PLACA',
	                      'DESCRIÇÃO DO BEM',
	                      'COD. FIPE',
	                      'CHASSI / Nº DE SERIE',
	                      'MOTOR A',
	                      'ÓRGÃO',
	                      'CNPJ DO PROPRIETÁRIO',
	                      'NOME DO PROPRIETÁRIO',
	                      'CLASSIFICAÇÃO DO BEM',
	                      'CONSERVAÇÃO DO BEM',
	                      'VALOR PATRIMONIAL',
	                      'VALOR DÉBITOS',
	                      'LOCAL DO PÁTIO'
	                     ];
	        foreach ($entity->getAllAssets() as $item) {
	            $asset = $item->getAsset();
	            $owner = $asset instanceof Vehicle ? $asset->getOwner() : $asset->getResponsibleUnit();
	            $data = [];
	            $data[] = $asset->getId();
	            $data[] = $asset->getAssetCode();
	            if ($asset instanceof Vehicle) {
    	           $data[] = $asset->getPlate();
				   $data[] = $asset->getDescription();
				   $data[] = $asset->getModel()->getFipe();
				   $data[] = $asset->getVin();
	            } else {
	               $data[] = '';
	               $data[] = $asset->getDescription();
	               $data[] = '';
	               $data[] = $asset->getSerialNumber();
	            }
				$data[] = FleetItem::getEnginesAllowed()[$asset->getEngine()];
				$data[] = $item->getDisposal()->getAgency()->getAcronym();
				$data[] = $owner->getNif();
				$data[] = $owner->getName();
				$data[] = $item->getClassification() ? DisposalItem::getClassificationAllowed()[$item->getClassification()] : '';
				$data[] = $item->getConservation() ? DisposalItem::getConservationAllowed()[$item->getConservation()] : '';
				$data[] = number_format($item->getValue(), 2, ',', '.');
				$data[] = number_format($item->getDebit(), 2, ',', '.');
				$data[] = $item->getCourtyard();
	            $dataset[] = $data;
	        }
	        $separator = ';';
	        foreach ($dataset as $row => $data) {
	            foreach ($data as $key => $value) {
	                $data[$key] = '"'. str_replace('"', '""', utf8_decode($value)) . '"';
	            }
	            $dataset[$row] = implode($separator, $data);
	        }
	        $this->response->setBody(implode("\r\n", $dataset));
	    } catch (NotFoundEntityException $e) {
	        $this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	        $this->forward('/');
	    } catch (\Exception $e) {
	        $this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	        $this->forward('/');
	    }
	}
	
	public function printAction() {
		try {
			$id = (int) $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Disposal::getClass(), $id);
			if (! $entity instanceof Disposal) {
				throw new NotFoundEntityException('Não foi possível imprimir Disposição. Disposição <em>#' . $id . '</em> não encontrada.');
			}
		    $view = new Layout('disposal/print-disposal.phtml', null);
		    $view->disposal = $entity;
		    $content[] = $view->render();
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
				$form = new DisposalSurveyVehicleForm($entity, new Action($this, 'survey-asset', ['key' => $id]), new Action($this, 'appraise', ['key' => $entity->getDisposal()->getId()]), new Action($this, 'location'));
			} else {
				$form = new DisposalSurveyEquipamentForm($entity, new Action($this, 'survey-asset', ['key' => $id]), new Action($this, 'appraise', ['key' => $entity->getDisposal()->getId()]), new Action($this, 'location'));
			}
			if ( $this->request->isPost() ) {
				$form->bind($this->request->getPost());
				if ( ! $form->valid() ) {
					throw new InvalidRequestDataException();
				}
				$form->hydrate($entity, $this->getEntityManager());
				$this->getEntityManager()->flush();
				$this->setAlert(new Alert('<strong>Ok! </strong>Ativo <em>' . $entity->code . ' ' . $entity->description . '</em> avaliado com sucesso!', Alert::Success));
				$this->forward('/appraise/' . $entity->getDisposal()->getId());
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
        	$disposal = $entity->getDisposal();
        	$this->getEntityManager()->remove($entity);
        	$this->getentitymanager()->flush();
        	
        	$surveyAsset = new Action($this, 'survey-asset');
        	$removeAsset = new Action($this, 'remove-asset');
        	$printAsset =  new Action($this, 'print-asset');
        	$table = new DisposalItemTable($surveyAsset, $removeAsset, $printAsset);
        	$table->setDataSource(new ArrayDatasource($disposal->getAllAssets(), 'id'));
        	$table->setFooter($disposal);
        	
        	$data[$table->getName()] = $table;
        	$data['alert-message'] = new Alert('<strong>Ok! </strong>Ativo <em>' . $entity->getAsset()->getCode() . ' ' . $entity->description . '</em> removido com sucesso!', Alert::Success);
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