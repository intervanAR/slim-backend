<?php
	$container = $app->getContainer();
	$container['upload_directory'] = $container->get('settings')['upload_directory'];	 

	$container['logger'] = function ($c){
		$settings = $c->get('settings')['logger'];
		$logger = new \Monolog\Logger($settings['name']);
		$logger -> pushProcessor( new \Monolog\Processor\UidProcessor);
		$logger -> pushHandler(new \Monolog\Handler\StreamHandler($settings['path']));
		return $logger;
	};

	$container['db'] = function ($c){
		$settings = $c->get('settings')['db'];
		$db = new \Medoo\Medoo($settings);
		return $db;
	};	


	$container['sistema'] = function ($c){
		$setting = $c->get('settings')['sistema'];
        if ($setting === 'gestionar') {
            $backend = new \Backend\Conectores\backend_gestionar();
		} elseif ($setting === 'arsa') {
            $backend = new \Backend\Conectores\backend_arsa();
		} elseif ($setting === 'aguas') {
            $backend = new \Backend\Conectores\backend_aguas();
        } elseif ($setting === 'creditos') {
            $backend = new \Backend\Conectores\backend_creditos();
        } elseif ($setting === 'erp') {
            $backend = new \Backend\Conectores\backend_erp();
        } elseif ($setting === 'demo') {
            $backend = new \Backend\Conectores\backend_demo();
        } else {
            throw new Exception("El parámatro sistema=$sistema definido en instalacion/settings.php es incorrecto");
		}
		return $backend;
	};	


