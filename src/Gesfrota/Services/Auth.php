<?php
namespace Gesfrota\Services;

use Gesfrota\Controller\AuthController;
use PHPBootstrap\Mvc\Plugin;
use PHPBootstrap\Mvc\Auth\Storage\SessionStorage;
use PHPBootstrap\Mvc\Http\HttpRequest;
use PHPBootstrap\Mvc\Http\HttpResponse;
use PHPBootstrap\Mvc\Routing\Dispatcher;

class Auth extends \PHPBootstrap\Mvc\Auth\Auth implements Plugin {
	
	/**
	 * @var Auth
	 */
	private static $instance;
	
	/**
	 * @param SessionStorage $storage
	 * @param UserAdapter $adapter
	 */
	private function __construct(SessionStorage $storage, UserAdapter $adapter) {
		parent::__construct($storage, $adapter);
	}
	
	/**
	 * @return Auth
	 */
	public static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new Auth(new SessionStorage('authenticate'), new UserAdapter());
		}
		return self::$instance;
	}
	
	/**
	 * @return UserAdapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}
	
	/**
	 * @return SessionStorage
	 */
	public function getStorage() {
		return $this->storage;
	}
	
	/**
	 *
	 * @see Plugin::preDispatch()
	 */
	public function preDispatch( HttpRequest $request, HttpResponse $response, Dispatcher $dispatcher = null ) {
		$controller = $dispatcher ? $dispatcher->getController() : null;
		if (! $controller instanceof AuthController  && ! $this->isAuthenticated() ) {
			$response->redirect('/auth');
			return false;
		}
	}
	
	/**
	 *
	 * @see Plugin::postDispatch()
	 */
	public function postDispatch( HttpRequest $request, HttpResponse $response, Dispatcher $dispatcher = null ) {
		
	}
}