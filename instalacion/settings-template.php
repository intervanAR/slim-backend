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
						 "charset"=>"WE8ISO8859P1",
						 "command"=>["ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'",
									  "ALTER SESSION SET NLS_NUMERIC_CHARACTERS = '.,'"]									],
				// Autenticación de Usuarios , puede utilizar validación Básica o Firebase
				'authentication' =>["basic" =>[ 
										"path"=>["/test","/api_docs","/*"], // Paths a controlar con authenticación básica
										"ignore"=>["/test"],
										"realm"=>"BackendRealm",
										"users" => [
												"usuario1" =>"CAMBIAR",
											]											
										]  ,									
				// Indicar con que sistema se conecta
				"sistema" => "demo"
			]
	];
