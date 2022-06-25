<?php
namespace Gesfrota\Controller;

use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\View\AccountAccessTable;
use Gesfrota\View\AccountPasswordForm;
use Gesfrota\View\AccountProfileForm;
use Gesfrota\View\Layout;
use Gesfrota\View\Widget\EntityDatasource;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Gesfrota\Services\Auth;
use Gesfrota\View\AccountNoticesTable;
use Gesfrota\Model\Notice;
use Gesfrota\Controller\Helper\Crud;
use PHPBootstrap\Mvc\Http\Cookie;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Misc\Title;

class AccountController extends AbstractController { 
	
	
	public function indexAction() {
		$form = new AccountProfileForm(new Action($this), new Action($this, 'changePassword'), new Action(AuthController::getClass(), 'logout'));
		try {
			if ( $this->request->isPost() ) {
				$form->bind($this->request->getPost());
				if ( ! $form->valid() ) {
					throw new InvalidRequestDataException();
				}
				$form->hydrate($this->getUserActive(), $this->getEntityManager());
				$this->getEntityManager()->flush();
				$this->setAlert(new Alert('<strong>Ok! </strong>Perfil alterado com sucesso!', Alert::Success));
				$this->forward('/');
			} else {
				$form->setAlert($this->getAlert());
				$form->extract($this->getUserActive());
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage(), Alert::Danger));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function changePasswordAction() {
		$form = new AccountPasswordForm(new Action($this, 'changePassword'), new Action($this));
		try {
			if ( $this->request->isPost() ) {
				$form->bind($this->request->getPost());
				if ( ! $form->valid() ) {
					throw new InvalidRequestDataException();
				}
				$redirect = $this->getUserActive()->isChangePassword() ? '/' : '/account';
				$form->hydrate($this->getUserActive(), $this->getEntityManager());
				$this->getEntityManager()->flush();
				$this->setAlert(new Alert('<strong>Ok! </strong>Senha alterada com sucesso!', Alert::Success));
				$this->redirect($redirect);
			}
		} catch ( InvalidRequestDataException $e ){
			$form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage(), Alert::Danger));
		} catch ( \Exception $e ) {
			$form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($form);
	}
	
	public function noticesAction() {
	    try {
	        $id = $this->request->getQuery('key');
	        $display = new Box();
	        $display->append(new Panel('<hr style="margin-top: 7px">'));
	        if ($id > 0) {
    	        $notice = $this->getEntityManager()->find(Notice::getClass(), $id);
    	        if ( $notice instanceof Notice) {
    	            $title = new Title($notice->getTitle(), 4);
    	            $title->setSubtext('Última atualização em ' . $notice->getUpdatedAt()->format('d/m/Y H:i:s'));
    	            $display->append($title);
    	            $display->append(new Panel('<hr>'));
    	            $display->append(new Panel(str_replace('{notice-read-current}', '/notice/read/' . $notice->getId(), $notice->getBody())));
    	        } else {
    	            $display->append(new Alert('<strong>Ops! </strong> Notificação não encontrada.'));
    	        }
	        } else {
	            $display->append(new Panel('<blockquote class="pull-right">
                                                <p>Selecione uma Notificação para lê-la<p>
                                                <small>Nenhuma notificação aberta</small>
                                            </blockquote>'));
	        }
	        
	        $filter = new Action($this);
    	    $view = new Action($this, 'notices');
    	    $read = new Action(NoticeController::getClass(), 'read');
    	    $list = new AccountNoticesTable($filter, $view, $read, $this->getUserActive(), $display);
    	    
    	        
    	    $display->setName('notice-display');
    	    $query = $this->getEntityManager()->getRepository(Notice::getClass())->createQueryBuilder('u');
    	    $query->where('u.active = true AND u.id != :about');
    	    $query->setParameter('about', Notice::ABOUT);
    	    
    	    $request = $this->getRequest();
    	    $response = $this->getResponse();
    	    
    	    $storage = ['identify' => md5('account::notices')];
    	    $storage['data']['limit'] = 1;
    	    $cookie = $request->getCookie('storage');
    	    
    	    if ( $cookie !== null ) {
    	        $storage = json_decode($cookie, true);
    	        if ( isset($storage['identify']) && isset($cookie['identify']) && $storage['identify'] == $cookie['identify'] ) {
    	            $storage = $cookie;
    	        }
    	    }
    	    
    	    
    	    $defaults = $storage['data'];
    	    $get = $request->getQuery();
    	    $datasource = new EntityDatasource($query, $defaults);
    	    
    	    if ( isset($get['sort']) ) {
    	        $datasource->toggleOrder(trim($get['sort']));
    	        $storage['data']['sort'] = $datasource->getSort();
    	        $storage['data']['order'] = $datasource->getOrder();
    	    }
    	    if ( isset($get['page']) ) {
    	        $datasource->setPage($get['page']);
    	        $storage['data']['page'] = $datasource->getPage();
    	    }
    	    if ( isset($get['limit']) ) {
    	        $datasource->setLimit((int) $get['limit']);
    	        $storage['data']['limit'] = $datasource->getLimit();
    	    }
    	    $list->setDatasource($datasource);
    	    $response->setCookie(new Cookie('storage', json_encode($storage)));
    	    
    	    $list->setAlert($this->getAlert());
    	} catch ( \Exception $e ) {
    	    $list->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
    	}
	   return new Layout($list);
	}
	
	
	public function accessAction() {
		try {
			$key = $this->request->getQuery('key');
			if ($key || $key === '0') {
				$agency = $this->getEntityManager()->find(Agency::getClass(), (int) $key);
				if ( ! $agency instanceof Agency ) {
					throw new \Exception('Órgão não acessível. Órgão <em>#'.$key.'</em> não encontrado.');
				}
				Auth::getInstance()->getStorage()->write(['user-id' => $this->getUserActive()->getId(), 'lotation-id' => (int) $key]);
				$this->setAlert(new Alert('<strong>Ok! </strong>Órgão <em>' . $agency->acronym . ' (#' . $agency->code . ') </em> acessado com sucesso!', Alert::Success));
			}
			$query = $this->em->getRepository(Agency::getClass())->createQueryBuilder('u');
			$table = new AccountAccessTable(new Action($this, 'access'), $this->getAgencyActive());
			$table->setDatasource(new EntityDatasource($query, ['limit' => 0]));
			$table->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
			$table->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($table);
	}
	
}
?>