<?php
namespace Gesfrota\Controller;

use Gesfrota\Services\AclResource;
use Gesfrota\Services\Auth;
use Gesfrota\View\Layout;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\View\AccountCreateForm;
use PHPBootstrap\Widget\Action\Action;
use Gesfrota\Controller\Helper\InvalidRequestDataException;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\User;
use PHPBootstrap\Widget\Misc\Alert;
use Gesfrota\Util\Crypt;

class AuthController extends AbstractController {
    
    /**
     * 
     * @var Requester
     */
    protected $requester;
	
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
	
	public function signupAction() {
	    try {
    	    $layout = new Layout('auth/signup.phtml');
    	    if ($this->request->isPost() && $this->request->getPost('agency') > 0) {
    	         $agency = $this->getEntityManager()->find(Agency::getClass(), (int) $this->request->getPost('agency'));
    	         $form = new AccountCreateForm(new Action($this, 'signup'), $this->getShowAdministrativeUnits($agency), 2);
    	         $form->bind($this->request->getPost());
    	         if ($this->request->getQuery('step') == 2) {
    	             $entity = $this->getEntityManager()->getRepository(User::getClass())->findOneBy(['nif' => $this->request->getPost('nif')]);
    	             if ( $entity instanceof User) {
    	                 throw new \DomainException('Não foi possível cadastrar Conta de Usuário: Conta de Usuário já existe.');
    	             }
        	         if ( ! $form->valid() ) {
        	             throw new InvalidRequestDataException();
        	         }
        	         $entity = new Requester();
        	         $entity->setActive(false);
        	         $entity->setPassword(Crypt::suggest(10));
        	         $this->requester = $entity;
                     $form->hydrate($entity, $this->getEntityManager());
        	         $this->getEntityManager()->persist($entity);
        	         $this->getEntityManager()->flush();
        	         $layout->message = new Alert('<strong>Ok! </strong>Conta de Usuário cadastrada com sucesso! <br>Seu cadastro está em análise e em breve você receberá sua senha por e-mail.', Alert::Success);
    	        }
    	    } else {
    	        $form = new AccountCreateForm(new Action($this, 'signup'), $this->getShowAgencies(), 1);
    	    }
	    } catch ( InvalidRequestDataException $e ){
	        $form->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage(), Alert::Danger));
	    } catch ( \Exception $e ) {
	        $form->setAlert(new Alert('<strong>Error: </strong>' . $e->getMessage(), Alert::Danger));
	    }
	    $layout->form = $form;
	    return $layout;
	}
	
	public function getShowAgencies() {
        $query = $this->getEntityManager()->getRepository(Agency::getClass())->createQueryBuilder('u');
        $query->where('u.id > 0 AND u.active = true');
        $query->orderBy('u.acronym');
        $result = $query->getQuery()->getResult();
        $options = ['' => ''];
        foreach($result as $item) {
            $options[$item->id] = $item->getAcronym() . ' - ' . $item->getName();
        }
        return $options;
	}
	
	public function getShowAdministrativeUnits(Agency $agency) {
        $query = $this->getEntityManager()->getRepository(AdministrativeUnit::getClass())->createQueryBuilder('u');
        $query->where('u.agency = :agency AND u.active = true');
        $query->setParameter('agency', $agency);
        $query->orderBy('u.lft');
        
        $result = $query->getQuery()->getResult();
        $options = ['' => ''];
        foreach($result as $item) {
            $item instanceof AdministrativeUnit;
            $options[$item->id] = $item->getAcronym() . ' - ' . $item->getName();
        }
        return $options;
	}
	
	public function logoutAction() {
		Auth::getInstance()->logout();
		$this->redirect('/auth');
	}
	
	/**
	 * @return Agency
	 */
	public function getAgencyActive() {
	    return $this->getEntityManager()->find(Agency::getClass(), (int) $this->request->getPost('agency'));
	}
	
	/**
	 * @return User
	 */
	public function getUserActive() {
	    $user = Auth::getInstance()->getIdentity();
	    return isset($user['user-id']) ? $this->getEntityManager()->find(User::getClass(), (int) $user['user-id']) : null;
	}
	
}
?>