<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="Search API", version="1.0.0")
 */

Use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * @OA\Get(
 *     path="/backend/test",
 *     description="Verifica el acceso al servidor rest",
 *     @OA\Response(response="default", description="Test page")
 * )
 */

$app->get('/test', function (Request $request, Response $response, array $args) {
		    $response->getBody()->write("Está funcionando");
		    $this->logger->debug('Test! Está funcionando');
		    return $response;
		}
	);




/**
 * @OA\Get(
 *     path="/backend/api_docs",
 *     description="Api Docs para swagger",
 *     @OA\Response(response="default", description="Welcome page")
 * )
 */
$app->get('/api_docs', function (Request $request, Response $response, array $args) {

			$this->logger->debug('/api_docs');
			$openapi = \OpenApi\scan('../src');

			$response->withHeader('Content-Type', 'application/x-yaml');
		    $response->write($openapi->toYaml());

		}
	);


$app->post('/servicios/consultas_dinamicas', function (Request $request, Response $response, array $args) {

			$myresponse = $response->withHeader('Content-Type', 'application/json');
			$body = json_decode($request->getBody()->getContents(),true);

			$reporte = isset($body["reporte"]) ? $body["reporte"] : null;
			$parametros = isset($body["parametros"]) ? $body["parametros"] : array();

			$datos = $this->backend->get_consulta_dinamica($reporte, $parametros);

			$myresponse->write(json_encode($datos));
		    return $myresponse;
		}
	);

$app->get('/servicios/cuentas_x_usuario', function (Request $request, Response $response, array $args) {

			$myresponse = $response->withHeader('Content-Type', 'application/json');			
			$body = json_decode($request->getBody()->getContents(),true);

			$datos = $this->backend->get_cuentas($body);

			$myresponse->write(json_encode($datos));
		    return $myresponse;
			}
	);

$app->post('/servicios/cuentas_x_objetos', function (Request $request, Response $response, array $args) {

			$myresponse = $response->withHeader('Content-Type', 'application/json');			
			$body = json_decode($request->getBody()->getContents(),true);

			$datos = $this->backend->get_cuentas_x_objetos($body);

			$myresponse->write(json_encode($datos));
		    return $myresponse;
			}
	);

$app->get('/servicios/consulta_deuda', function (Request $request, Response $response, array $args) {

			$myresponse = $response->withHeader('Content-Type', 'application/json');			
			$body = json_decode($request->getBody()->getContents(),true);

			$datos = $this->backend->consulta_deuda($body);

			$myresponse->write(json_encode($datos));
		    return $myresponse;
			}
	);
