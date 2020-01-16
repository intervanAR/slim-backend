<?php
	$container = $app->getContainer();

	$container['logger'] = function ($c){
		$settings = $c->get('settings')['logger'];
		$logger = new \Monolog\logger($settings['name']);
		$logger -> pushProcessor( new \Monolog\Processor\UidProcessor);
		$logger -> pushHandler(new \Monolog\Handler\StreamHandler($settings['path']));
		return $logger;
	};

	$container['db'] = function ($c){
		$settings = $c->get('settings')['db'];
		$db = new \Medoo\Medoo($settings);
		return $db;
	};	


	$container['backend'] = function ($c){
		$setting = $c->get('settings')['backend'];
        if ($setting === 'gestionar') {
            $backend = new \Backend\Conectores\backend_gestionar();
		} elseif ($setting === 'aguas') {
            $backend = new \Backend\Conectores\backend_aguas();
        } elseif ($setting === 'creditos') {
            $backend = new \Backend\Conectores\backend_creditos();
        } elseif ($setting === 'erp') {
            $backend = new \Backend\Conectores\backend_erp();
        } else {
            throw new Exception("El parámatro backend=$backend definido en instalacion/settings.php es incorrecto");
		}
		return $backend;
	};	


