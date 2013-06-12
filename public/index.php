<?php
use Sigmat\Common\Logging\Logger;
use Doctrine\ORM\EntityManager;
use PHPBootstrap\Mvc\Application;
use Doctrine\ORM\Tools\Setup;
use Sigmat\Common\Plugin\NotFound;
use Sigmat\Common\Plugin\Error;
use PHPBootstrap\Widget\Action\Action;

// MODE
$isDevMode = getenv('APPLICATION_ENV') == 'development';

// LOADER
if ( file_exists('../vendor/autoload.php') ) {
	$loader = include '../vendor/autoload.php';
} else {
	spl_autoload_register( function( $className ) {
		@include_once( str_replace('\\', DIRECTORY_SEPARATOR, $className)  . '.php');
	});
}

// ERROR REPORTING
error_reporting(E_ALL ^ E_NOTICE);
if ( $isDevMode ) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	ini_set('log_errors', 0);
} else {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	ini_set('log_errors', 1);
}
ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log');

// LOCALE
date_default_timezone_set('America/Sao_Paulo');
if ( PATH_SEPARATOR === ';' ) {
	setlocale(LC_TIME, 'ptb');
} else {
	setlocale(LC_TIME, 'pt_BR');
}

// CONFIGURE
$config = include '../src/config/application.config.php';
if ( $isDevMode ) { 
	$config = $config + include '../src/config/local.config.php';
}
$paths = explode(PATH_SEPARATOR, get_include_path());
$paths = array_merge($paths, $config['paths'], array(dirname(__DIR__), dirname(__DIR__) . '\\src'));
set_include_path(implode(PATH_SEPARATOR, $paths));
$doctrine = Setup::createAnnotationMetadataConfiguration($config['doctrine']['paths'], $isDevMode, $config['doctrine']['proxies']);

// ROUTER
$router = include 'src/config/router.php';
Action::setRouter($router);

// LOGGER 
Logger::configure(array('em' => EntityManager::create($config['doctrine']['connection'], $doctrine)));

// EXECUTE
$application = new Application($router);
$application->attach(new NotFound());
$application->attach(new Error());
$application->config = $config;
$application->em = EntityManager::create($config['doctrine']['connection'], $doctrine);
$application->run();
?>