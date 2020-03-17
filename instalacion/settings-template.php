<?php
	return [
			// Arreglo de configuración
			'settings'=>[

				'displayErrorDetails' =>true, // En producción poner en FALSE				
				'logger' =>[  // Logger, nombre de log y directorio donde lo deja
					'name' => 'backend',
					'path' => '../instalacion/logs/web_services.log'
				],
				// Conexión a la base de datos, motor (Usado por medoo) 
				'db' => ["database_type" => "oracle",
						 "database_name" => "desa10g",
						 "server" =>"10.1.1.1",
						 "port" => "1521",
						 "username" => "irsol",
						 "password" => "irsol",
						 "charset"=>"WE8ISO8859P1"						
				],
				// Autenticación de Usuarios , puede utilizar validación Básica o Firebase
				'authentication' =>["basic" =>[ 
										"path"=>["/test","/api_docs","/*"], // Paths a controlar con authenticación básica
										"ignore"=>["/test"],
										"realm"=>"BackendRealm",
										"users" => [
												"usuario1" =>"CAMBIAR",
											]											
										]  ,									
								/*	// Autenticación con token JWT de Firebase
									"firebase" =>[ 
										"path"=>"/servicios_jwt",
										"projectId"=>"proyecto de firebase",
										"keyCache"=>__DIR__.'/cache',
										"timeoutCache"=>3600,					
										] */ ],
				// Indicar con que sistema se conecta
				"sistema" => "demo"
			]
	];
