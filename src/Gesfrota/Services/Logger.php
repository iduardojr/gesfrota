<?php
namespace Gesfrota\Services;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Entity;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\User;
use PHPBootstrap\Mvc\Plugin;
use PHPBootstrap\Mvc\Http\HttpRequest;
use PHPBootstrap\Mvc\Http\HttpResponse;
use PHPBootstrap\Mvc\Routing\Dispatcher;

/**
 * Registrador de logs
 */
class Logger implements Plugin {
	
	/**
	 * @var EntityManager
	 */
	protected $em;
	
	/**
	 * @var User
	 */
	protected $user;
	
	/**
	 * @var Agency
	 */
	protected $agency;
	
	/**
	 * @var HttpRequest
	 */
	protected $request;
	
	/**
	 * @var boolean
	 */
	protected $initiated;
	
	/**
	 * @var array
	 */
	protected $posted;
	
	/**
	 * @var Logger
	 */
	protected static $instance;
	
	/**
	 * Construtor
	 */
	protected function __construct() {
		$this->initiated = false;
		$this->posted = [];
	}
	
	/**
	 * @return Logger
	 */
	public static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new Logger();
		}
		return self::$instance;
	}
	
	/**
	 * Configuração do log
	 * 
	 * @param array $config
	 */
	public static function configure( array $config ) {
		self::getInstance()->em = isset($config['em']) ? $config['em'] : null; 
	}
	
	/**
	 * @return boolean
	 */
	public static function begin() {
		$that = self::getInstance();
		if ($that->em && ! $that->initiated) {
			$that->em->beginTransaction();
			$that->initiated = true;
		}
		return $that->initiated;
	}
	
	/**
	 * 
	 * @param Entity $newValue
	 * @param Entity $oldValue
	 * @return Log
	 */
	public function register( $newValue, $oldValue) {
		if ( $this->initiated && $this->user && $this->agency) {
			$log = new Log($this->request->getUri(), $this->user, $this->agency, $newValue, $oldValue);
			$this->em->persist($log);
			$this->em->flush();
			if ($log->getUser() == null) {
				$this->posted[] = $log;
			}
			return $log;
		}
		return null;
	}
	
	/**
	 *
	 * @param Entity $object
	 * @return Log
	 */
	public function create( $object) {
		return $this->register($object, null);
	}
	
	/**
	 *
	 * @param Entity $object
	 * @return Log
	 */
	public function remove($object) {
		return $this->register(null, $object);
	}
	
	/**
	 *
	 * @param Entity $object
	 * @return Log
	 */
	public function update( $object ) {
		return $this->register($object, $this->em->find(get_class($object), $object->getId()));
	}
	
	/**
	 * @param Log $log
	 */
	public function unregister(Log $log) {
		return $this->em->detach($log);
	}
	
	public function preDispatch(HttpRequest $request, HttpResponse $response, Dispatcher $dispatcher = null) {
		$controller = $dispatcher ?  $dispatcher->getController() : null;
		if ($this->initiated && $controller) {
			$this->request = $request;
			$this->user = $controller->getUserActive() ? $this->em->find(User::getClass(), $controller->getUserActive()->getId()) : null;
			$this->agency = $controller->getAgencyActive() ? $this->em->find(Agency::getClass(), $controller->getAgencyActive()->getId()) : null;
		}
	}

	public function postDispatch(HttpRequest $request, HttpResponse $response, Dispatcher $dispatcher = null) {
		
	}
	
	/**
	 * @return boolean
	 */
	public static function finish() {
		$that = self::getInstance();
		if ($that->initiated) {
			try {
				$that->em->flush();
				$that->em->commit();
				foreach ($that->posted as $log) {
					$ref = new \ReflectionProperty($log, 'user');
					$ref->setAccessible(true);
					$ref->setValue($log, $that->user);
					$that->em->flush();
				}
				return true;
			} catch (\Exception $e) {
				$that->em->rollback();
			}
		}
		return false;
	}

}
?>