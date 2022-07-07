<?php
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Gesfrota\Model\Domain\Place;
use Gesfrota\Services\AclResource;
use Gesfrota\Services\Auth;
use Gesfrota\Services\Error;
use Gesfrota\Services\Logger;
use Gesfrota\Services\NotFound;
use Gesfrota\Util\Crypt;
use Gesfrota\View\Render\Html5\RendererDirection;
use Gesfrota\View\Render\Html5\RendererDynInput;
use Gesfrota\View\Render\Html5\RendererDynInputAdd;
use Gesfrota\View\Render\Html5\RendererDynInputRemove;
use Gesfrota\View\Render\Html5\RendererPlaceInput;
use Gesfrota\View\Render\Html5\RendererWaypointsInput;
use Gesfrota\View\Widget\Direction;
use Gesfrota\View\Widget\DynInput;
use Gesfrota\View\Widget\DynInputAdd;
use Gesfrota\View\Widget\DynInputRemove;
use Gesfrota\View\Widget\PlaceInput;
use Gesfrota\View\Widget\WaypointsInput;
use PHPBootstrap\Mvc\Application;
use PHPBootstrap\Render\RenderKit;
use PHPBootstrap\Widget\Action\Action;
use Gesfrota\Util\MatchAgainst;
use PHPBootstrap\Widget\Pagination\Pagination;
use Gesfrota\View\Render\Html5\RendererPagination;

// MODE
$isDevMode = getenv('APPLICATION_ENV') == 'development' || stripos($_SERVER['HTTP_HOST'], 'homo') !== false;

// LOADER
if ( file_exists('../vendor/autoload.php') ) {
	include '../vendor/autoload.php';
	spl_autoload_register( function( $className ) {
	    @include_once( str_replace('\\', DIRECTORY_SEPARATOR, $className)  . '.php');
	});
} else {
	spl_autoload_register( function( $className ) {
		@include_once( str_replace('\\', DIRECTORY_SEPARATOR, $className)  . '.php');
	});
}

// ERROR REPORTING
if ( $isDevMode ) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	ini_set('log_errors', 0);
	error_reporting(E_ALL ^ E_DEPRECATED);
} else {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	ini_set('log_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT ^ E_DEPRECATED);
}
if (PHP_MAJOR_VERSION >= 7) {
    set_error_handler(function ($errno, $errstr) {
       return strpos($errstr, 'Declaration of') === 0;
    }, E_WARNING);
}
ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log');

// LOCALE
date_default_timezone_set('America/Sao_Paulo');
date_default_timezone_set('America/Bahia');
if ( PATH_SEPARATOR === ';' ) {
	setlocale(LC_TIME, 'ptb');
} else {
	setlocale(LC_TIME, 'pt_BR');
}

// CONFIGURE
$config = include '../src/config/application.config.php';
if ( $isDevMode ) { 
	$config = array_replace_recursive($config, include '../src/config/local.config.php');
}
$paths = explode(PATH_SEPARATOR, get_include_path());
$paths = array_merge($paths, $config['paths'], array(dirname(__DIR__), dirname(__DIR__) . DIRECTORY_SEPARATOR .'src'));
set_include_path(implode(PATH_SEPARATOR, $paths)); 

$doctrine = Setup::createAnnotationMetadataConfiguration($config['doctrine']['paths'], $isDevMode, $config['doctrine']['proxies']);
$doctrine->addCustomStringFunction('MATCH', MatchAgainst::class);
$paths = explode(PATH_SEPARATOR, get_include_path());

// ROUTER
$router = include 'src/config/router.php';
Action::setRouter($router);

// LOGGER 
Logger::configure(['em' => EntityManager::create($config['doctrine']['connection'], $doctrine)]);

// CRYPTO
Crypt::setKey('G35fr074');

// PLACE
Place::setParameters(array_merge(['key' => $config['google']['key']], $config['google']['place']));

define('GOOGLE_KEY_APP', $config['google']['key']);
define('DIR_ROOT' , rtrim(__DIR__, DIRECTORY_SEPARATOR));

// RENDERKIT
RenderKit::getInstance()->addRenderer(DynInput::RendererType, RendererDynInput::class);
RenderKit::getInstance()->addRenderer(DynInputAdd::RendererType, RendererDynInputAdd::class);
RenderKit::getInstance()->addRenderer(DynInputRemove::RendererType, RendererDynInputRemove::class);
RenderKit::getInstance()->addRenderer(PlaceInput::RendererType, RendererPlaceInput::class);
RenderKit::getInstance()->addRenderer(WaypointsInput::RendererType, RendererWaypointsInput::class);
RenderKit::getInstance()->addRenderer(Direction::RendererType, RendererDirection::class);
RenderKit::getInstance()->addRenderer(Pagination::RendererType, RendererPagination::class);

// EXECUTE
Logger::begin();
$application = new Application($router);
$application->config = $config;
$application->em = EntityManager::create($config['doctrine']['connection'], $doctrine);
$application->attach(new NotFound());
$application->attach(new Error());
$application->attach(Logger::getInstance());
$application->attach(Auth::getInstance());
$application->attach(AclResource::getInstance());
Auth::getInstance()->getAdapter()->setEntityManager($application->em);
$application->run();
Logger::finish();
?>