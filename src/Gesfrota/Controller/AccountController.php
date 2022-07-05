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
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgLink;

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
	        $display->append(new Panel('<hr>'));
	        if ($id > 0) {
    	        $notice = $this->getEntityManager()->find(Notice::getClass(), $id);
    	        if ( $notice instanceof Notice) {
    	            $title = new Title($notice->getTitle(), 4);
    	            $display->append($title);
    	            $button = new Button('Marcar como lido', new TgLink(new Action(NoticeController::getClass(), 'read', ['key' => $notice->getId()])), Button::Mini);
    	            $button->setDisabled($notice->isReadBy($this->getUserActive()));
    	            $button->setName('mark-read');
    	            $display->append($button);
    	            $date = $notice->getCreatedAt()->format('d/m/Y H:i:s');
    	            $date.= $notice->getUpdatedAt() > $notice->getCreatedAt() ? ' • Atualizado em ' . $notice->getUpdatedAt()->format('d/m/Y H:i:s') : '';
    	            $display->append(new Paragraph('<small>' . $date . '</small>'));
    	           
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
	        $display->setName('notice-display');
	        
	        $filter = new Action($this, 'notices');
    	    $view = new Action($this, 'notices');
    	    $read = new Action(NoticeController::getClass(), 'read');
    	    $list = new AccountNoticesTable($filter, $view, $read, $this->getUserActive(), $display);
    	    
    	        
    	    
    	    $query = $this->getEntityManager()->getRepository(Notice::getClass())->createQueryBuilder('u');
    	    $query->where('u.active = true AND u.id != :about');
    	    $query->setParameter('about', Notice::ABOUT);
    	    
    	    $request = $this->getRequest();
    	    $response = $this->getResponse();
    	    
    	    
    	    $defaults = ['limit' => 12, 'sort' => 'updatedAt', 'order' => 'desc'];
    	    
    	    $storage = ['identify' => md5('account_notices')];
    	    $cookie = $request->getCookie('storage');
    	    if ( $cookie !== null ) {
    	        $cookie = json_decode($cookie, true);
    	        if (isset($cookie['identify']) && $storage['identify'] == $cookie['identify'] ) {
    	            $storage = $cookie;
    	        }
    	    }
    	    
    	    $defaults = array_merge($defaults, isset($storage['data']) ? $storage['data'] : []);
    	    
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
    	    $cookie = new Cookie('storage', json_encode($storage));
    	    $cookie->setPath('/account/notices');
    	    $response->setCookie($cookie);
    	    
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
			$query = $this->getEntityManager()->getRepository(Agency::getClass())->createQueryBuilder('u');
			$table = new AccountAccessTable(new Action($this, 'access'), $this->getAgencyActive());
			$table->setDatasource(new EntityDatasource($query, ['limit' => 0, 'sort' => 'acronym']));
			$table->setAlert($this->getAlert());
		} catch ( \Exception $e ) {
			$table->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
		}
		return new Layout($table);
	}
	
}
?>