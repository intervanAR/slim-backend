<?php

use \Backend\SlimBackend;
use Kreait\Firebase\JWT\IdTokenVerifier;


require '../vendor/autoload.php';

$settings = require('../instalacion/settings.php');

$app = new \Slim\App($settings);

SlimBackend::setBackend($app);

require '../src/dependencies.php';

require '../src/rest/routes.php';


/* Authentication */
if( isset($settings["settings"]["authentication"])){

	// Recorrer los mencanismos de authenticación
	$auths = $settings["settings"]["authentication"];
	foreach ($auths as $key => $auth) {
		# code...
		switch ($key) {
		 	case 'basic':
		 		# code...
					$auth["before"] = function ($request, $arguments) {
        						return $request->withAttribute("user", $arguments["user"]);
						};
					$auth["error"] = function ($response, $arguments) {
						        $data = [];
						        $data["status"] = "error";
						        $data["message"] = $arguments["message"];

						        $body = $response->getBody();
						        $body->write(json_encode($data, JSON_UNESCAPED_SLASHES));

						        return $response->withBody($body);
						};
					$app -> add(new Tuupola\Middleware\HttpBasicAuthentication($auth));
		 		break;
		 	case 'firebase':
		 		# code...
					$auth["before"] = function ($request, $arguments) {
        						return $request->withAttribute("user", $arguments["user"]);
						};
					$auth["error"] = function ($response, $arguments) {
						        $data = [];
						        $data["status"] = "error";
						        $data["message"] = $arguments["message"];

						        $body = $response->getBody();
						        $body->write(json_encode($data, JSON_UNESCAPED_SLASHES));

						        return $response->withBody($body);
						};
					$pool = new Kodus\Cache\FileCache($auth["keyCache"] , $auth["timeoutCache"] );  
					$verifier = IdTokenVerifier::createWithProjectIdAndCache($auth["projectId"],$pool);
					$app -> add(new \Backend\Controllers\JwtFirebaseAuthentication($auth,$verifier));
		 		break;

		 	default:
		 		# code...
		 		break;
		 } 
	}


}

if(isset($settings["settings"]["empresaMiddleware"])){ 
	$app -> add(new Backend\Controllers\EmpresaMiddleware($settings["settings"]["empresaMiddleware"]));
}

// Lanzar servidor
$app->run();