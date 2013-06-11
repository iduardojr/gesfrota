<?php
use PHPBootstrap\Mvc\Routing\Router;

return new Router('/[:controller[/:action]]', array(), array('__NAMESPACE__' => 'Sigmat\\Controller'));
?>