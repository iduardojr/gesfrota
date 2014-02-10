<?php
namespace Sigmat\Controller;

use Doctrine\ORM\EntityManager;
use PHPBootstrap\Mvc\Controller;
use PHPBootstrap\Mvc\Session\Session;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\Model\Domain\Agency;

/**
 * Constrolador Abstrato
 */
abstract class AbstractController extends Controller { 
	
	/**
	 * @var Session
	 */
	protected $session;
	
	/**
	 * Construtor
	 */
	public function __construct() {
		$this->session = new Session('storage');
		if ( $this->session->identify != md5(get_class($this)) ) {
			Session::unregister($this->session);
			$this->session = new Session('storage');
			$this->session->identify = md5(get_class($this));
		}
	}
	
	/**
	 * @return Agency
	 */
	protected function getAgencyActive() {
		return $this->getEntityManager()->find(Agency::getClass(), ( int ) 1);
	}

	/**
	 * Filtra um valor
	 * 
	 * @param string $value
	 */
	protected function sanitize( $value ) {
		$value = trim($value);
		$value = strip_tags($value);
		return $value;
	}
	
	/**
	 * Redireciona para uma ação da propria classe
	 * 
	 * @param string $action
	 */
	protected function forward( $action ) {
		$this->redirect(new Action($this, trim($action, '/')));
	}
	
	/**
	 * Obtem o gerenciador de entidades
	 * 
	 * @return EntityManager
	 */
	protected function getEntityManager() {
		return $this->em;
	}
	
	/**
	 * Obtem um alerta
	 *  
	 * @return Alert
	 */
	protected function getAlert() {
		$alert = $this->session->alert;
		$this->session->alert = null;
		return $alert;
	}
	
	/**
	 * Atribui um alerta
	 * 
	 * @param Alert $alert
	 */
	protected function setAlert( Alert $alert = null ) {
		$this->session->alert = $alert;
	}
	
}
?>