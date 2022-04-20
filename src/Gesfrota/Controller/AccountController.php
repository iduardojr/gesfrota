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