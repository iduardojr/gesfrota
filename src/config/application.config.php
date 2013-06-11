<?php
return array(
		'paths' => array( dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR ),
		'doctrine' => array(
				'connection' => array(
						'driver' => 'pdo_mysql',
						'host' => 'localhost',
						'port' => '3306',
						'user' => 'root',
						'password' => '',
						'dbname' => 'sigmat'),
				'paths' => array(),
				'proxies' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'proxies' . DIRECTORY_SEPARATOR 
		)
);
?>