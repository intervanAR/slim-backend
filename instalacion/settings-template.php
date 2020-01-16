<?php
	return [
			'settings'=>[
				'displayErrorDetails' =>true, 				
				'logger' =>[
					'name' => 'backend',
					'path' => '../instalacion/logs/web_services.log'
				],
				'db' => ["database_type" => "oracle",
						 "database_name" => "desa10g",
						 "server" =>"10.1.1.1",
						 "port" => "1521",
						 "username" => "irsol",
						 "password" => "irsol",
						 "charset"=>"WE8ISO8859P1"						
				],
				'authentication' =>[ 
					"path"=>"/*",
					"ignore"=>"/test",
					"realm"=>"BackendRealm",
					"users" => [
							"usuario1" =>"CAMBIAR",
						]						
					
				],
				"backend" => "erp"
				]
	];
