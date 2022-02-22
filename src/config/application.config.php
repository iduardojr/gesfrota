<?php
return [
	'paths' => [dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR],
	'doctrine' => [
		'connection' => [
			'driver' => 'pdo_mysql',
			'host' => 'gesfrota.mysql.dbaas.com.br',
			'port' => '3306',
			'user' => 'gesfrota',
			'password' => 'Sead2022*22',
			'dbname' => 'gesfrota'],
		'paths' => [],
		'proxies' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'proxies' . DIRECTORY_SEPARATOR 
	],
	'google' => [
		'key' => 'AIzaSyCPrFRmYsGKVGNPqvIUyMIc3KKH1K1g_Hc',
		'place' =>  [
			'location' => '-16.6808356,-49.2585076',
			'radius' => 50000,
			'region' => 'br',
			'language' => 'pt-BR'
		],
		'maps' => [
			'zoom' => 12,
			'center' => ['lat' => -16.6808356, 'lng' => -49.2585076],
			'streetViewControl' => false,
			'mapTypeControl' => false
		]
	]
];
?>