<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\Notice;
use Gesfrota\View\Layout;
use Gesfrota\View\NoticeForm;
use Gesfrota\View\NoticeList;
use Gesfrota\View\NoticeReadTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;


class NoticeController extends AbstractController {
	
	public function indexAction() {
		try {
			$filter = new Action($this);
			$new = new Action($this, 'new');
			$edit = new Action($this, 'edit');
			$remove = new Action($this, 'remove');
			$views = new Action($this, 'views');
			$list = new NoticeList($filter, $new, $edit, $remove, $views);
			$helper = $this->createHelperCrud();
			$query = $this->getEntityManager()->getRepository(Notice::getClass())->createQueryBuilder('u');
			$helper->read($list, $query, array('limit' => 20, 'processQuery' => function( QueryBuilder $query, array $data ) {
				if ( !empty($data['terms']) ) {
					$query->andWhere('MATCH(u.title, u.body) AGAINST(:term boolean)>0');
					$query->setParameter('term',  $data['terms']);
				}
				
				if ( !empty($data['date-initial']) ) {
				    $query->andWhere('u.createdAt >= :initial');
				    $query->setParameter('initial', $data['date-initial']);
				}
				if ( !empty($data['date-final']) ) {
				    $query->andWhere('u.createdAt <= :final');
				    $query->setParameter('final', $data['date-final'] . ' 23:59:59');
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
	
	public function newAction() {
		try {
			$form = $this->createForm(new Action($this, 'new'));
			$helper = $this->createHelperCrud();
			if ( $helper->create($form) ){
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Notificação <em>#' . $entity->code . ' ' . $entity->title . '</em> criada com sucesso!', Alert::Success));
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
			$edit = new Action($this, 'edit', array('key' => $id));
			$remove = new Action($this, 'remove', array('key' => $id));
			$views = new Action($this, 'views', array('key' => $id));
			$form = $this->createForm($edit, $remove, $views );
			$helper = $this->createHelperCrud();
			$helper->setException(new NotFoundEntityException('Não foi possível editar Notificação. Notificação <em>#' . $id . '</em> não encontrada.'));
			if ( $helper->update($form, $id) ) {
				$entity = $helper->getEntity();
				$this->setAlert(new Alert('<strong>Ok! </strong>Notificação <em>#' . $entity->code . ' ' . $entity->title .  '</em> alterada com sucesso!', Alert::Success));
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
	
	public function removeAction() {
		try {
			$id = $this->request->getQuery('key');
			$entity = $this->getEntityManager()->find(Notice::getClass(), $id);
			if (! $entity instanceof Notice) {
			    throw new NotFoundEntityException('Não foi possível excluir Notificação. Notificação <em>#' . $id . '</em> não encontrada.');
			}
			$helper = $this->createHelperCrud();
			$helper->delete($entity);
			$this->setAlert(new Alert('<strong>Ok! </strong>Notificação <em>#' . $id . ' ' . $entity->title . '</em> excluída com sucesso!', Alert::Success));
		} catch ( NotFoundEntityException $e ) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
		} catch ( \Exception $e ) {
			$this->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		$this->forward('/');
	}
	
	public function readAction() {
	    $id = $this->request->getQuery('key');
	    $entity = $this->getEntityManager()->find(Notice::getClass(), $id);
	    if ( $entity instanceof Notice && ! $entity->isReadBy($this->getUserActive())) {
	        $entity->readBy($this->getUserActive());
	        $this->getEntityManager()->flush();
	    }
	    $this->redirect($this->request->getHeader('Referer'));
	}
	
	public function viewsAction() {
	    try {
    	    $id = $this->request->getQuery('key');
    	    $entity = $this->getEntityManager()->find(Notice::getClass(), $id);
    	    if (! $entity instanceof Notice) {
    	        throw new NotFoundEntityException('Não foi possível visualizar as leituras da Notificação. Notificação <em>#' . $id . '</em> não encontrada.');
    	    }
    	    $table = new NoticeReadTable($entity->getReadByUsers());
	    } catch ( NotFoundEntityException $e ) {
	        $this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage()));
	        $this->forward('/');
	    }
	    return new Layout($table, null);
	}
	
	
	/**
	 * @return Crud
	 */
	private function createHelperCrud() {
		return new Crud($this->getEntityManager(), Notice::getClass(), $this);
	}
	
	/**
	 * 
	 * @param Action $submit
	 * @param Action $remove
	 * @param Action $views
	 * @return NoticeForm
	 */
	private function createForm ( Action $submit, Action $remove = null, Action $views = null ) {
		return new NoticeForm($submit, new Action($this), $remove, $views);
	}
	
}
?>