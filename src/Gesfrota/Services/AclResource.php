<?php
namespace Gesfrota\Services;

use Gesfrota\Controller\AbstractController;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\Manager;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Layout;
use PHPBootstrap\Mvc\Plugin;
use PHPBootstrap\Mvc\Acl\Acl;
use PHPBootstrap\Mvc\Http\HttpRequest;
use PHPBootstrap\Mvc\Http\HttpResponse;
use PHPBootstrap\Mvc\Routing\Dispatcher;
use Gesfrota\Controller\AuthController;
use Gesfrota\Controller\IndexController;

class AclResource implements Plugin {
	
	const Dashboard = 'IndexController';
	
	const Fleet = 'FleetController';
	const Disposal = 'DisposalController';
	const Driver = 'DriverController';
	const Requester = 'RequesterController';
	const Request = 'RequestController';
	
	const Reports = 'Reports';
	
	const AdministrativeUnit = 'AdministrativeUnitController';
	const Owner = 'OwnerController';
	const ServiceProvider = 'ServiceProviderController';
	const VehicleFamily = 'VehicleFamilyController';
	const VehicleMaker = 'VehicleMakerController';
	const VehicleModel = 'VehicleModelController';
	const User = 'UserController';
	const Audit = 'AuditController';
	
	const Account = 'AccountController';
	const Auth = 'AuthController';
	
	/**
	 * @var AclResource
	 */
	private static $instance;
	
	/**
	 * @var Acl
	 */
	protected $acl;
	
	private function __construct() {
		$this->acl = new Acl(false);
		$this->acl->allow(null, self::Auth);
		
		
		$this->acl->allow(Manager::getClass());
		
		$resource = [
			self::Dashboard,
			self::Fleet, 
			self::Disposal, 
			self::Request, 
			self::Driver, 
			//self::Requester, 
			self::Account,
			//self::AdministrativeUnit
		];
		$this->acl->allow(FleetManager::getClass(), $resource);
		$this->acl->deny(FleetManager::getClass(), self::Disposal, ['confirm', 'devolve']);
		
		$resource = [
			self::Request,
			self::Account
		];
		$this->acl->allow([Driver::getClass(), Requester::getClass()], $resource);
		
		$this->acl->deny([FleetManager::getClass(), Driver::getClass(), Requester::getClass()], self::Account, ['access']);
		$this->acl->deny(Requester::getClass(), self::Request, ['confirm', 'decline', 'initiate', 'finish']);
		$this->acl->deny(Driver::getClass(), self::Request, ['confirm', 'decline']);
	}
	
	/**
	 * @return AclResource
	 */
	public static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new AclResource();
		}
		return self::$instance;
	}
	
	/**
	 * @param string|User $role
	 * @param string|AbstractController $resource
	 * @param string $privilege
	 * @return boolean
	 */
	public function isAllowed($role, $resource, $privilege = null ) {
		if (is_object($role)) {
			$role = get_class($role);
		}
		if (is_object($resource)) {
			$resource = get_class($resource);
		}
		$resource = str_replace('Gesfrota\\Controller\\', '', $resource);
		$privilege = str_replace('Action', '', $privilege);
		return $this->acl->isAllowed($role, $resource, $privilege);
	}
	
	public function preDispatch(HttpRequest $request, HttpResponse $response, Dispatcher $dispatcher = null) {
		$controller = $dispatcher ? $dispatcher->getController() : null;
		if ( $controller ) {
			$user = $controller->getUserActive();
			if ( ! $this->isAllowed($user, $controller, $dispatcher->getAction())) {
				if ($controller instanceof IndexController && ($user instanceof Requester || $user instanceof Driver) ) {
					$response->redirect('/request');
				} else {
					$response->setStatus(HttpResponse::Forbidden);
					$layout = new Layout('layout/403.phtml');
					$uri = $request->getUri();
					$match = null;
					if ( preg_match('|^[^\?#]*|', $uri, $match) ) {
						$layout->uri = '/' . trim($match[0], "/ \t\n\r\0\x0B");
					}
					$response->setBody($layout);
					return false;
				}
			}
			 
		}
		
	}

	public function postDispatch(HttpRequest $request, HttpResponse $response, Dispatcher $dispatcher = null) {
		$controller = $dispatcher ? $dispatcher->getController() : null;
		$view = $response->getBody();
		if ( $controller  && ! $controller instanceof AuthController && $response->isSuccessful() && $view instanceof Layout ) {
			$view->BuiderNavbar($this, $controller->getUserActive(), $controller->getAgencyActive()->getAcronym());
		}
	}

}
?>