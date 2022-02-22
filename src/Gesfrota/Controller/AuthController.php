<?php
namespace Gesfrota\Controller;

use Gesfrota\Services\AclResource;
use Gesfrota\Services\Auth;
use Gesfrota\View\Layout;

class AuthController extends AbstractController {
	
	public function indexAction() {
		$layout = new Layout('auth/index.phtml');
		$layout->navbar = null;
		$layout->about = null;
		if ($this->request->isPost()) {
			$data = $this->request->getPost();
			$result = Auth::getInstance()->authenticate($data['login'], $data['password']);
			if ($result > 0) {
				if ( $this->getUserActive()->isChangePassword() ) {
					$this->redirect('/account/change-password');
				} elseif (AclResource::getInstance()->isAllowed($this->getUserActive(), AclResource::Dashboard)) {
					$this->redirect('/');
				} else {
					$this->redirect('/request');
				}
			} else {
				$layout->message = "Seu login ou senha est&aacute; incorreto.";
			}
		}
		return $layout;
	}
	
	public function resetPasswordAction() {
		$layout = new Layout('auth/reset-password.phtml');
		$layout->navbar = null;
		$layout->about = null;
		if ($this->request->isPost()) {
			$data = $this->request->getPost();
			$user = Auth::getInstance()->getAdapter()->getByIdentity($data['login']);
			if ($user) {
				$user->setPassword(null);
				$this->em->flush();
				$email = explode('@', $user->getEmail());
				$email[0] = substr($email[0], 0, 2) . '****' . substr($email[0], -3);
				$layout->message = "Uma nova senha foi enviada para o seu e-mail: </ br><strong>". implode('@', $email) . "</strong>";
			} else {
				$layout->message = "Seu login est&aacute; incorreto.";
			}
		}
		return $layout;
	}
	
	public function logoutAction() {
		Auth::getInstance()->logout();
		$this->redirect('/auth');
	}
	
}
?>