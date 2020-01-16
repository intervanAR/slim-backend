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
$authentication = $settings["settings"]["authentication"];

$authentication["before"] = function ($request, $arguments) {
        return $request->withAttribute("user", $arguments["user"]);
};

$authentication["error"] = function ($response, $arguments) {
        $data = [];
        $data["status"] = "error";
        $data["message"] = $arguments["message"];

        $body = $response->getBody();
        $body->write(json_encode($data, JSON_UNESCAPED_SLASHES));

        return $response->withBody($body);
};

$app -> add(new Tuupola\Middleware\HttpBasicAuthentication($authentication));

/*
$projectId = 'dinahuapi-intervan';
$pool = new Kodus\Cache\FileCache(__DIR__.'/cache', 3600 );  
$verifier = IdTokenVerifier::createWithProjectIdAndCache($projectId,$pool);

$app -> add(new \Backend\Controllers\JwtFirebaseAuthentication($authentication,$verifier));
*/
$app->run();