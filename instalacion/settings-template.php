<?php
	return [
			// Arreglo de configuración
			'settings'=>[

				'displayErrorDetails' =>true, // En producción poner en FALSE				
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
				'authentication' =>["basic" =>[ 
										"path"=>["/test","/api_docs","/*"],
										"ignore"=>["/test"],
										"realm"=>"BackendRealm",
										"users" => [
												"usuario1" =>"CAMBIAR",
											]											
										]  ,
									"firebase" =>[ 
										"path"=>"/servicios",
										"projectId"=>"dinahuapi-intervan",
										"keyCache"=>__DIR__.'/cache',
										"timeoutCache"=>3600,					
										] ],
				"backend" => "erp"
			]
	];
