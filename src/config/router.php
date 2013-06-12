<?php
use PHPBootstrap\Mvc\Routing\Router;

$router = new Router('/', array(), array('__NAMESPACE__' => 'Sigmat\\Controller'));
$router->addRoute(new Router('[:controller[/:action]]'));
return $router;
?>