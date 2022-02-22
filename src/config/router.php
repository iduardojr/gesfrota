<?php
use PHPBootstrap\Mvc\Routing\Router;

$router = new Router('/', [], ['__NAMESPACE__' => 'Gesfrota\\Controller']);
$router->addRoute(new Router('[:controller[/:action][/:key]]', ['key' => '[0-9]+']));
return $router;
?>