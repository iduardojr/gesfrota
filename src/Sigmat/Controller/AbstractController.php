<?php
namespace Sigmat\Controller;

use PHPBootstrap\Mvc\Controller;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Mvc\Session\Session;
use Doctrine\ORM\EntityManager;

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
	
}
?>