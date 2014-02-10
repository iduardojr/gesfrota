<?php
namespace Sigmat\Services;

use Doctrine\ORM\EntityManager;
use Sigmat\Model\Domain\Log;

/**
 * Registrador de logs
 */
class Logger {
	
	/**
	 * @var EntityManager
	 */
	protected static $em;
	
	/**
	 * Construtor
	 */
	protected function __construct() {}
	
	/**
	 * Configura��o do log
	 * 
	 * @param array $config
	 */
	public static function configure( array $config ) {
		self::$em = isset($config['em']) ? $config['em'] : null; 
	}

	/**
	 * Registra o log
	 * 
	 * @param Log $log
	 * @return boolean
	 */
	public static function register( Log $log ) {
		if ( self::$em ) {
			self::$em->persist($log);
			self::$em->flush();
			return true;
		}
		return false;
	}
}
?>