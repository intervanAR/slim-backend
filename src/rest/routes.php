<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="Backend API", version="1.0.0")
 */

Use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Http\UploadedFile;
use \Backend\SlimBackend;
/**
 * Moves the uploaded file to the upload directory and assigns it a unique name
 * to avoid overwriting an existing uploaded file.
 *
 * @param string $directory directory to which the file is moved
 * @param UploadedFile $uploadedFile file uploaded file to move
 * @return string filename of moved file
 */
function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}


function array_a_utf8($datos){
		if (is_string($datos)) {
			return utf8_encode($datos);
		}
		if (!is_array($datos)) {
			return $datos;
		}
		$ret = array();
		foreach ($datos as $i => $d) {
			$ret[$i] = array_a_utf8($d);
		}
		return $ret;
	}

  function utf8_to_array($datos){
        if (is_string($datos)) {
            return utf8_decode($datos);
        }
        if (!is_array($datos)) {
            return $datos;
        }
        $ret = array();
        foreach ($datos as $i => $d) {
            $ret[$i] = utf8_to_array($d);
        }
        return $ret;
  }


/**
 * @OA\Get(
 *     path="/backend/test",
 *     summary="Responde si el backend está funcionadno",
 *     tags={"Varios"},
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
 *     summary="Retorna especificación Open Api para Swagger",
 *     tags={"Varios"},
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

 /** @OA\Post(
       path="/backend/servicios/consultas_dinamicas",
	   tags={"Notificaciones"},
       summary="Ejecuta una consulta almacenada con parámetros",
       description="Consultas para reportes de notificaciones",
       @OA\RequestBody(
          	@OA\JsonContent(type="object",
				@OA\Property(
				  property="reporte",
				  type="string"
				),
				@OA\Property(
				  	property="parametros",
					  	type="array",
					  	@OA\Items(
							@OA\Property(property="nombre_param_n", type="string"),
							@OA\Property(property="valor_param_n", type="string"),
						),
						example={"codigo_postal":"8500"}
				),
			)
	    ),
       @OA\Response(
           response=200,
           description="Retorna un arreglo de filas de registros, según la consulta generada",
           @OA\JsonContent(
          		type="object"
          )
       )
   )
*/

$app->post('/servicios/consultas_dinamicas', function (Request $request, Response $response, array $args) {

			$this->logger->debug('/servicios/consultas_dinamicas:'.$request->getBody()->getContents());

			$body = $request->getParsedBody();

			$reporte = isset($body["reporte"]) ? $body["reporte"] : null;
			$parametros = isset($body["parametros"]) ? $body["parametros"] : array();

			$datos = $this->sistema->get_consulta_dinamica($reporte, $parametros);


			$myresponse = $response->withHeader('Content-Type', 'application/json')->
					write(json_encode(array_a_utf8($datos)));
		    return $myresponse;
		}
	);

/**
    @OA\Get(
       	path="/backend/servicios/buscar_cuenta",
       	tags={"Cuentas Clientes"},
       	summary="Busca cuentas para asociar a usuario por tipo y nro de documento o por mail",
       	description="Se puede buscar por tipo y nro de documento o por mail",
       	operationId="get_cuentas",
       	deprecated=false,
     		@OA\Parameter(
    			name="usuario",
     			in="query",
			    description="Identificar de usuario",
			    required=false,
			    explode=false,
			    ),
     *     @OA\Parameter(
     *         name="tipo_doc",
     *         in="query",
     *         description="Tipo de Documento",
     *         required=false,
     *         explode=false,
     *     ),
     *     @OA\Parameter(
     *         name="nro_documento",
     *         in="query",
     *         description="Nro Documento",
     *         required=false,
     *         explode=false,
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="email",
     *         required=false,
     *         explode=false,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *   		   @OA\Schema(
     *     			   type="array",
					   @OA\Schema(
					     @OA\Property(property="tipo_objeto", type="string"),
					     @OA\Property(property="id_objeto", type="string")
					   )
					)
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status value"
     *     ),
     *     security={
     *         {"backend_auth": {"write:cuentas", "read:cuentas"}}
     *     }
     * )
     */
$app->get('/servicios/buscar_cuenta', function (Request $request, Response $response, array $args) {

			$this->logger->debug('/servicios/buscar_cuenta:'.json_encode($request->getQueryParams()));

			$body = $request->getQueryParams();

			$datos = $this->sistema->get_cuentas($body)[0];

			$cantidad = count($datos);

			$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
									withAddedHeader('Cantidad-Registros', "$cantidad");			
			$myresponse->write(json_encode($datos));
		    return $myresponse;
			}
	);

/**
    @OA\Get(
       	path="/backend/servicios/cuentas_x_usuario",
       	tags={"Cuentas Clientes"},
       	summary="Busca cuentas para asociar a usuario por tipo y nro de documento o por mail",
       	description="Se puede buscar por tipo y nro de documento o por mail",
       	operationId="get_cuentas",
       	deprecated=false,
     		@OA\Parameter(
    			name="usuario",
     			in="query",
			    description="Identificar de usuario",
			    required=false,
			    explode=false,
			    ),
     *     @OA\Parameter(
     *         name="tipo_doc",
     *         in="query",
     *         description="Tipo de Documento",
     *         required=false,
     *         explode=false,
     *     ),
     *     @OA\Parameter(
     *         name="nro_documento",
     *         in="query",
     *         description="Nro Documento",
     *         required=false,
     *         explode=false,
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="email",
     *         required=false,
     *         explode=false,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *   		   @OA\Schema(
     *     			   type="array",
					   @OA\Schema(
					     @OA\Property(property="tipo_objeto", type="string"),
					     @OA\Property(property="id_objeto", type="string")
					   )
					)
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status value"
     *     ),
     *     security={
     *         {"backend_auth": {"write:cuentas", "read:cuentas"}}
     *     }
     * )
     */
$app->get('/servicios/cuentas_x_usuario', function (Request $request, Response $response, array $args) {

			$this->logger->debug('/servicios/cuentas_x_usuario:'.json_encode($request->getQueryParams()));

			$body = $request->getQueryParams();

			$datos = $this->sistema->get_cuentas($body);

			$cantidad = count($datos);

			$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
									withAddedHeader('Cantidad-Registros', "$cantidad");			
			$myresponse->write(json_encode($datos));
		    return $myresponse;
			}
	);


/**
 * @OA\Post(
       path="/backend/servicios/cuentas_x_objetos",
	   tags={"Cuentas Clientes"},
       summary="Describe un conjunto de cuentas",
       description="Consultas detalles de cuentas. Es llamado cuando se necesita mostrar información de cuentas, detalle de deudas y facturas",
       @OA\RequestBody(
          @OA\JsonContent(type="array",
            @OA\Items(
		    	@OA\Property(property="tipo_objeto", type="string"),
		    	@OA\Property(property="id_objeto", type="string"),
		    	@OA\Property(property="usuario", type="string"),
		    	@OA\Property(property="alias_cuenta", type="string"),     
				example = {"tipo_objeto":"TCC","id_objeto":"1-0-16","usuario":"8BXBtYw6FRf2Mtyvl1TqnMzpMkB2","alias_cuenta":"Casa de Pedro"}		    	
     			),
			)
       ),
       @OA\Response(
           response=200,
           description="OK"
       )
   )
 */
$app->post('/servicios/cuentas_x_objetos', function (Request $request, Response $response, array $args) {

			$body = json_decode($request->getBody()->getContents(),true);

			$datos = $this->sistema->get_cuentas_x_objetos($body);

			$cantidad = count($datos);

			$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
									withAddedHeader('Cantidad-Registros', "$cantidad");			
			$myresponse->write(json_encode($datos));
		    return $myresponse;
			}
	);

/**
 * @OA\Post(
       path="/backend/servicios/consulta_deuda",
	   tags={"Deudas Clientes"},
       summary="Consulta deude de cuentas o de usuario dado por tipo y nro de documento",
       description="Consultas detalle de deudas",
       @OA\RequestBody(
          	@OA\JsonContent(
				@OA\Property(property="tipoDeuda", 
					type="string",
					enum={"deuda", "prox" , "todo"}
					),
				@OA\Property(property="nro_docuemnto", 
					type="string",
					),
				@OA\Property(property="email", 
					type="string",
					),
				@OA\Property(property="cuentas", 
          			type="array",
		            @OA\Items(
				    	@OA\Property(property="tipo_objeto", type="string"),
				    	@OA\Property(property="id_objeto", type="string"),
				    	@OA\Property(property="usuario", type="string"),
				    	@OA\Property(property="alias_cuenta", type="string"),     
				    	@OA\Property(property="id_cuenta", type="string"),     
				    	@OA\Property(property="tipo_cuenta", type="string"),     
				    	@OA\Property(property="desc_tipo_cuenta", type="string"),
				    	@OA\Property(property="nro_cuenta", type="string"),
				    	@OA\Property(property="descripcion", type="string"),          
				    	@OA\Property(property="responsable_pago", type="string"),          
				    	@OA\Property(property="id_persona", type="string"),          
				    	@OA\Property(property="enviar_mail", type="string",enum={"S", "N"}),          
				    	@OA\Property(property="pa_activo", type="string",enum={"S", "N"}),          
				    	@OA\Property(property="pa_fecha_desde", type="string"),          
				    	@OA\Property(property="pa_fecha_hasta", type="string"),
				    	@OA\Property(property="id_nro_cuenta", type="string"),          
						example = {"alias_cuenta":"CENTRO","id_cuenta":"4592","tipo_cuenta":"TCR1","nro_cuenta":"4898","desc_tipo_cuenta":"Partida","descripcion":"Partida 4898  ","responsable_pago":" ","id_persona":"427","enviar_mail":"S","pa_activo":"S","pa_fecha_desde":"2019-12-09 00:00:00","pa_fecha_hasta":"","id_nro_cuenta":"4592"}		    	
		     			),
		     		)
			)
       ),
       @OA\Response(
           response=200,
           description = "Retorna el detalle de la deuda de las cuentas o del usuario, Debe retornar los campos ordenados para hacer corte de control ",
          	@OA\JsonContent(
				@OA\Property(property="deuda", 
          			type="array",
		        	@OA\Items(
				    	@OA\Property(property="cont_id", type="string"),
				    	@OA\Property(property="cont_desc1", type="string"),
				    	@OA\Property(property="cont_desc2", type="string"),
				    	@OA\Property(property="id_cuenta", type="string"),
				    	@OA\Property(property="cue_desc1", type="string"),
				    	@OA\Property(property="cue_desc2", type="string"),
				    	@OA\Property(property="imp_id", type="string"),
				    	@OA\Property(property="imp_desc1", type="string"),
				    	@OA\Property(property="imp_desc2", type="string"),
				    	@OA\Property(property="per_id", type="string"),
				    	@OA\Property(property="per_desc1", type="string"),
				    	@OA\Property(property="per_desc2", type="string"),
				    	@OA\Property(property="deu_id", type="string"),
				    	@OA\Property(property="deu_desc1", type="string"),
				    	@OA\Property(property="deu_desc2", type="string"),
				    	@OA\Property(property="deu_vto", type="string"),
				    	@OA\Property(property="deu_capital", type="string"),
				    	@OA\Property(property="deu_recargo", type="string"),
						example = {
							"id_cuenta":"1192",
							"cont_id":"1484",
							"cont_desc1":"GOMEZ EUGENIA DE MALDONADO",
							"cont_desc2":"Otro 12817","cue_id":"1192",
							"cue_desc1":"prueba 2",
							"cue_desc2":"BARTOLOME MITRE N\u00b0 424",
							"imp_id":"1",
							"imp_desc1":"SERVICIOS P\u00daBLICOS",
							"per_id":"1#2019",
							"per_desc1":"Deuda 2019",
							"deu_id":"1236165",
							"deu_desc1":"6\/2019 Vto:14\/07\/19",
							"deu_vto":"2019-07-14 00:00:00",
							"deu_capital":"451.53",
							"deu_recargo":".86"
						})		    	
		     		)
		     	)
			)
       )
   )
 */

   $app->post('/servicios/consulta_deuda', function (Request $request, Response $response, array $args) {

			$this->logger->debug('/servicios/cuentas_x_usuario:'.$request->getBody()->getContents());
			
			$parametros =  $request->getParsedBody(); 

			$datos = $this->sistema->consulta_deuda($parametros);

			$cantidad = count($datos);

			$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
									withAddedHeader('Cantidad-Registros', "$cantidad");			

			$myresponse->write(json_encode($datos));
		    return $myresponse;
			}
	);


/**
 * 	@OA\Post(
       path="/backend/servicios/resumen_pago",
	   tags={"Deudas Clientes"},
       summary="Genera una actualización de deuda con el detalle de comporbantes pasados",
       description="Puede generar una o varias facturas",
       @OA\RequestBody(
          	@OA\JsonContent(
          		@OA\Property(property="fecha_actualizacion", 
          			type="string",
          			example="2020-01-23"),
				@OA\Property(property="id_comprobantes", 
          			type="array",
		        	@OA\Items(
				    	@OA\Property(property="id", type="string"),
						example = {"id":"1236165"}		    	
		     		)
		     	)
			)
       ),
       @OA\Response(
           response=200,
           description = "Retorna la lista de comprobantes generados, el total de la operación y la máxima fecha de vto.",
          	@OA\JsonContent(
          		@OA\Property(property="rta", 
          			type="string",
          			example="OK"),
          		@OA\Property(property="comprobantesFact", 
          			type="string",
          			example="#1245973#1245974#1245975#1245976#1245977#"),          			
				@OA\Property(property="total", type="string"),
				@OA\Property(property="max_fecha_vto", 
					type="string", 
					example="2010-01-23 00:00:00"),
				@OA\Property(property="comprobantes", 
          			type="array",
		        	@OA\Items(
				    	@OA\Property(property="id_comprobante", type="string"),
				    	@OA\Property(property="total", type="string"),
				    	@OA\Property(property="fecha_vto", type="string"),
				    	@OA\Property(property="cod_concepto", type="string"),
				    	@OA\Property(property="desc_concepto", type="string"),
				    	@OA\Property(property="descripcion", type="string"),
						example = {
				            "id_comprobante": "1245973",
				            "total": "999.16",
				            "fecha_vto": "2020-01-22 00:00:00",
				            "cod_concepto": "1",
				            "desc_concepto": "SERVICIOS P\u00daBLICOS",
				            "descripcion": "F.Nro:501174 Partida 4898: 1\/2018 1-2-3\/2019 3\/2018 0\/3 4-5-6\/2019 "
        				}		    	
		     		)
		     	)
			)
       )
   )
 */

$app->post('/servicios/resumen_pago', function (Request $request, Response $response, array $args) {

			$this->logger->debug('/servicios/resumen_pago ');

			$parametros = $request->getParsedBody(); 
 			if (isset($parametros['id_comprobantes']))
				$id_comprobantes= $parametros['id_comprobantes'];
			else
				$id_comprobantes= null;

			if (isset($parametros['fecha_actualizacion']))
				$fecha_actualizacion= $parametros['fecha_actualizacion'];
			else
				$fecha_actualizacion= NULL;		

			$datos = $this->sistema->resumen_pago($id_comprobantes, $fecha_actualizacion);


			//resumen_deuda_hidratado = rest_hidratador::hidratar($this->get_spec_deuda('Resumen'), $rta);
			$cantidad = count($datos);

			$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
									withAddedHeader('Cantidad-Registros', "$cantidad");			

			$myresponse->write(json_encode($datos));
		    return $myresponse;
			}
	);


/**
 * 	@OA\Post(
       path="/backend/servicios/debitoAutomatico",
	   tags={"Cuentas Clientes"},
       summary="Establece débito automático para una cuenta",
       description="Asociarle al débito automático de una cuenta. El sistema subyacente deberá realzar el registro de esta información",
       @OA\RequestBody(
          	@OA\JsonContent(
          		@OA\Property(property="tipo_cuenta", 
          			type="string",
          			),
				@OA\Property(property="nro_cuenta", 
          			type="string",
          			),
				@OA\Property(property="tipo_documento", 
          			type="string",
          			),
				@OA\Property(property="nro_documento", 
          			type="string",
          			),
				@OA\Property(property="cbu", 
          			type="string",
          			),
			)
       ),
       @OA\Response(
           response=200,
           description = "Retorna OK o un mensaje de error",
          	@OA\JsonContent(
          		@OA\Property(property="resultado", 
          			type="string",
          			example="OK"),
          	)
        ),
       @OA\Response(
           response=500,
           description = "Retorna OK o un mensaje de error",
          	@OA\JsonContent(
          		@OA\Property(property="resultado", 
          			type="string",
          			example="Se ha producido un error al establece débito automático"),
          	)
        )
   )
 */
$app->Post('/servicios/debitoAutomatico', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/debitoAutomatico:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

	$rta= $this->sistema->alta_debito_automatico($parametros);

	if ($rta == 'OK') {
		$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	}else{
		$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
								withStatus(302);	
	}
	$myresponse->write(json_encode(array('resultado'=>$rta)));
});


/**
 * 	@OA\Delete(
       path="/backend/servicios/debitoAutomatico",
	   tags={"Cuentas Clientes"},
       summary="Desasocia débito automático para una cuenta",
       description="EL sistema debe deshabilitar el débito automático para la cuenta",
       @OA\RequestBody(
          	@OA\JsonContent(
          		@OA\Property(property="tipo_cuenta", 
          			type="string",
          			),
				@OA\Property(property="nro_cuenta", 
          			type="string",
          			),
			)
       ),
       @OA\Response(
           response=200,
           description = "Retorna OK o un mensaje de error",
          	@OA\JsonContent(
          		@OA\Property(property="resultado", 
          			type="string",
          			example="OK"),
          	)
        ),
       @OA\Response(
           response=500,
           description = "Retorna OK o un mensaje de error",
          	@OA\JsonContent(
          		@OA\Property(property="resultado", 
          			type="string",
          			example="Se ha producido un error al dar de baja débito automático"),
          	)
        )
   )
 */

$app->Delete('/servicios/debitoAutomatico', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/debitoAutomatico:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

	$rta= $this->sistema->baja_debito_automatico($parametros);

	if ($rta == 'OK') {
		$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	}else{
		$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
								withStatus(302);	
	}
	$myresponse->write(json_encode(array('resultado'=>$rta)));
});

/**
 * 	@OA\Post(
       path="/backend/servicios/facturaElectronica",
	   tags={"Cuentas Clientes"},
       summary="Solicita asociación de Factura Electrónica a la cuenta",
       description="Asociarle factura electrónica a la cuenta",
       @OA\RequestBody(
          	@OA\JsonContent(
          		@OA\Property(property="tipo_cuenta", 
          			type="string",
          			),
				@OA\Property(property="nro_cuenta", 
          			type="string",
          			),
				@OA\Property(property="tipo_documento", 
          			type="string",
          			),
				@OA\Property(property="nro_documento", 
          			type="string",
          			),
				@OA\Property(property="cbu", 
          			type="string",
          			),
			)
       ),
       @OA\Response(
           response=200,
           description = "Retorna OK o un mensaje de error",
          	@OA\JsonContent(
          		@OA\Property(property="resultado", 
          			type="string",
          			example="OK"),
          	)
        ),
       @OA\Response(
           response=500,
           description = "Retorna OK o un mensaje de error",
          	@OA\JsonContent(
          		@OA\Property(property="resultado", 
          			type="string",
          			example="Se ha producido un error al dar de alta factura electrónica"),
          	)
        )
   )
 */
   $app->Post('/servicios/facturaElectronica', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/facturaElectronica:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

	$rta= $this->sistema->alta_factura_electronica($parametros);

	if ($rta == 'OK') {
		$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	}else{
		$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
								withStatus(302);	
	}
	$myresponse->write(json_encode(array('resultado'=>$rta)));
});


/**
 * 	@OA\Delete(
       path="/backend/servicios/facturaElectronica",
	   tags={"Deudas Clientes"},
       summary="Desasocia factura electronica para una cuenta",
       description="EL sistema debe deshabilitar el envío de factura electronica para la cuenta",
       @OA\RequestBody(
          	@OA\JsonContent(
          		@OA\Property(property="tipo_cuenta", 
          			type="string",
          			),
				@OA\Property(property="nro_cuenta", 
          			type="string",
          			),
			)
       ),
       @OA\Response(
           response=200,
           description = "Retorna OK o un mensaje de error",
          	@OA\JsonContent(
          		@OA\Property(property="resultado", 
          			type="string",
          			example="OK"),
          	)
        ),
       @OA\Response(
           response=500,
           description = "Retorna OK o un mensaje de error",
          	@OA\JsonContent(
          		@OA\Property(property="resultado", 
          			type="string",
          			example="Se ha producido un error al dar de baja factura electroncia"),
          	)
        )
   )
 */

$app->Delete('/servicios/facturaElectronica', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/facturaElectronica:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

	$rta= $this->sistema->baja_factura_electronica($parametros);

	if ($rta == 'OK') {
		$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	}else{
		$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
								withStatus(302);	
	}
	$myresponse->write(json_encode(array('resultado'=>$rta)));
});



/** 
 * @OA\Post(
		path="/backend/servicios/crear_operacion_pago",
	   	tags={"Deudas Clientes"},
       	summary="Genera una operación de pago ONLINE que se debe registrar en el sistema",
        description="Se pasa como parámetros la colección de cupones de pago generadas a través 	del servicio resumen_pago contenidos en la candena comprobantesFact",
       	@OA\RequestBody(
       		request="ArrayComprobantes",
      		description="Listado de comprobantes a pagar",
      		required=true,
          	@OA\JsonContent(
          		type="array",
		        @OA\Items(
			    	@OA\Property(property="cupon_pago", 
			    		type="string",
			    		description="Corresponde con el cuppon de pago o id_comrpboante del sistema subyacente"),
			    	@OA\Property(property="id_operacion", 
			    		type="string", 
			    		description="Identificador de operación del Midleware, similar a lo que es el cupón de las tarjetas."),
			    	@OA\Property(property="fecha_vto", type="string"),
			    	@OA\Property(property="cod_concepto", type="string"),
			    	@OA\Property(property="desc_concepto", type="string"),
			    	@OA\Property(property="descripcion", type="string"),
					example = {"cupon_pago":"1245982",
						"id_operacion":"69522",
						"importe":"999.16",
						"fecha_vto":"2020-01-22 00:00:00",
						"cod_concepto":"1",
						"desc_concepto":"SERVICIOS P\u00c3\u009aBLICOS",
						"descripcion":"F.Nro:501181 Partida 4898: 1\/2018 1-2-3\/2019 3\/2018 0\/3 4-5-6\/2019 "
					}		    	
          		),
			)
       ),
       @OA\Response(
           response=200,
           description = "Retorna OK o un mensaje de error",
          	@OA\JsonContent(
          		@OA\Property(property="rta", 
          			type="string"),
          		@OA\Property(property="id_operacion_pago", 
          			type="string",
          			description="Identificar de la operación en el sistema subyacente",
          			example="345612"),
          	)
        )
   )
 */

$app->Post('/servicios/crear_operacion_pago', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/crear_operacion_pago:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

	$facturas= $this->sistema->crear_operacion_pago($parametros);

	$cantidad = count($facturas);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
							withAddedHeader('Cantidad-Registros', "$cantidad");			

	$myresponse->write(json_encode($facturas));
    return $myresponse;

});

/** 
 * @OA\Post(
		path="/backend/servicios/anular_operacion_pago",
	   	tags={"Deudas Clientes"},
       	summary="Anula una operación de pago ONLINE, posiblemente error en la operación con el gateway de pago ",
       	@OA\RequestBody(
       		request="Operacion",
      		description="Id del Comprobante de operación generado con la opción crear_oepracion_pago",
      		required=true,
          	@OA\JsonContent(
			    @OA\Property(property="id_operacion", 
			    	type="string"),
			    example = { "id_operacion":"14367"}
			)
       ),
       @OA\Response(
           response=200,
           description = "Retorna OK o un mensaje de error",
          	@OA\JsonContent(
          		@OA\Property(property="rta", 
          			type="string"),
          		@OA\Property(property="id_operacion_pago", 
          			type="string",
          			description="Identificar de la operación en el sistema subyacente",
          			example="345612"),
          	)
        )
   )
 */

$app->Post('/servicios/anular_operacion_pago', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/anular_operacion_pago:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

	$id_operacion = null;
	if(isset($parametros["id_operacion"]))
		$id_operacion=$parametros["id_operacion"];

	$rta =$this->sistema->anular_operacion_pago($id_operacion);


	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');			

	$myresponse->write(json_encode($rta));
    return $myresponse;

});


/** 
 * @OA\Post(
		path="/backend/servicios/Facturas",
	   	tags={"Deudas Clientes"},
       	summary="Lista de facturas de cuentas de cliente/contribuyentes",
        description="Se pasa como parámetros la colección de cuentas asociadas al cliente y retorna el listado de facturas de clientes",
       	@OA\RequestBody(
          	@OA\JsonContent(
				@OA\Property(property="nro_docuemnto", 
					type="string",
					),
				@OA\Property(property="email", 
					type="string",
					),
				@OA\Property(property="cuentas", 
          			type="array",
		            @OA\Items(
				    	@OA\Property(property="tipo_objeto", type="string"),
				    	@OA\Property(property="id_objeto", type="string"),
				    	@OA\Property(property="usuario", type="string"),
				    	@OA\Property(property="alias_cuenta", type="string"),     
				    	@OA\Property(property="id_cuenta", type="string"),     
				    	@OA\Property(property="tipo_cuenta", type="string"),     
				    	@OA\Property(property="desc_tipo_cuenta", type="string"),
				    	@OA\Property(property="nro_cuenta", type="string"),
				    	@OA\Property(property="descripcion", type="string"),          
				    	@OA\Property(property="responsable_pago", type="string"),          
				    	@OA\Property(property="id_persona", type="string"),          
				    	@OA\Property(property="enviar_mail", type="string",enum={"S", "N"}),          
				    	@OA\Property(property="pa_activo", type="string",enum={"S", "N"}),          
				    	@OA\Property(property="pa_fecha_desde", type="string"),          
				    	@OA\Property(property="pa_fecha_hasta", type="string"),
				    	@OA\Property(property="id_nro_cuenta", type="string"),          
						example = {"alias_cuenta":"CENTRO","id_cuenta":"4592","tipo_cuenta":"TCR1","nro_cuenta":"4898","desc_tipo_cuenta":"Partida","descripcion":"Partida 4898  ","responsable_pago":" ","id_persona":"427","enviar_mail":"S","pa_activo":"S","pa_fecha_desde":"2019-12-09 00:00:00","pa_fecha_hasta":"","id_nro_cuenta":"4592"}		    	
		     		),
		     	)
			)
       ),
       @OA\Response(
           response=200,
           description = "Retorna el detalle de la deuda de las cuentas o del usuario, Debe retornar los campos ordenados para hacer corte de control ",
          	@OA\JsonContent(
      			description="Listado de facturas de cuentas",
      			type="array",
	        	@OA\Items(
			    	@OA\Property(property="nro_factura", type="string"),
			    	@OA\Property(property="id_comprobante", type="string"),
			    	@OA\Property(property="descripcion_factura", type="string"),
			    	@OA\Property(property="fecha_1vto", type="string",example="2017-02-14 00:00:00"),
			    	@OA\Property(property="fecha_2vto", type="string",example="2017-02-20 00:00:00"),
			    	@OA\Property(property="importe_1vto", type="string",example="80.32"),
			    	@OA\Property(property="importe_2vto", type="string",example="81.05"),
			    	@OA\Property(property="tipo", type="string" , example="1"),
			    	@OA\Property(property="desc_tipo", type="string", example="Tasa\/Impuesto"),
			    	@OA\Property(property="estado", type="string",example="1"),
			    	@OA\Property(property="desc_estado", type="string",example= "Pagada"),
			    	@OA\Property(property="anio", type="string"),
			    	@OA\Property(property="impuesto", type="string"),
			    	@OA\Property(property="pagar", type="string",description="Indica si se puede pagar o no por medios de pago, por ejemplo las vencidas o aquellas que están en convenios, posiblemente no se puedan pagar directamente"),
			    	@OA\Property(property="cuenta", type="array",
			            @OA\Items(
					    	@OA\Property(property="tipo_objeto", type="string"),
					    	@OA\Property(property="id_objeto", type="string"),
					    	@OA\Property(property="usuario", type="string"),
					    	@OA\Property(property="alias_cuenta", type="string"),     
					    	@OA\Property(property="id_cuenta", type="string"),     
					    	@OA\Property(property="tipo_cuenta", type="string"),     
					    	@OA\Property(property="desc_tipo_cuenta", type="string"),
					    	@OA\Property(property="nro_cuenta", type="string"),
					    	@OA\Property(property="descripcion", type="string"),          
					    	@OA\Property(property="responsable_pago", type="string"),          
					    	@OA\Property(property="id_persona", type="string"),          
					    	@OA\Property(property="enviar_mail", type="string",enum={"S", "N"}),          
					    	@OA\Property(property="pa_activo", type="string",enum={"S", "N"}),          
					    	@OA\Property(property="pa_fecha_desde", type="string"),          
					    	@OA\Property(property="pa_fecha_hasta", type="string"),
					    	@OA\Property(property="id_nro_cuenta", type="string"),          	    	
			     		),
			     	),
			    	example = {
						"nro_factura": "316589",
						"id_comprobante": "660845",
						"descripcion_factura": "1\/2017",
						"fecha_1vto": "2017-02-14 00:00:00",
						"importe_1vto": "80.32",
						"importe_2vto": "81.05",
						"tipo": "1",
						"desc_tipo": "Tasa\/Impuesto",
						"desc_estado": "Pagada",
						"estado": "1",
						"anio": "2017",
						"impuesto": "SERVICIOS P\u00daBLICOS",
						"pagar": "N",
						"cuenta": {
						    "alias_cuenta": "CENTRO",
						    "id_cuenta": "4592",
						    "tipo_cuenta": "TCR1",
						    "nro_cuenta": "4898",
						    "desc_tipo_cuenta": "Partida",
						    "descripcion": "Partida 4898  ",
						    "responsable_pago": " ",
						    "id_persona": "427",
						    "enviar_mail": "S",
						    "pa_activo": "S",
						    "pa_fecha_desde": "2019-12-09 00:00:00",
						    "pa_fecha_hasta": "",
						    "id_nro_cuenta": "4592"
						}
					},
	     		)
			)
       )
   )
 */
$app->Post('/servicios/facturas', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/facturas:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

	$facturas = $this->sistema->get_facturas($parametros);

	$cantidad = count($facturas);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
							withAddedHeader('Cantidad-Registros', "$cantidad");			

	$myresponse->write(json_encode($facturas));
    return $myresponse;
});



/**
    @OA\Get(
       	path="/backend/servicios/reporte_factura",
       	tags={"Cuentas Clientes"},
       	summary="Retoorna un pdf con el reporte de la factura",
       	description="El reporte está codificadoen base64",
       	operationId="reporte_factura",
       	deprecated=false,
     		@OA\Parameter(
    			name="p_cadena_facturas",
     			in="query",
			    description="Identificar cadena de facturas separadas por #",
			    required=true,
			    explode=false,
			    ),
     		@OA\Parameter(
    			name="pdf",
     			in="query",
			    description="Identificar cadena de facturas separadas por #",
			    explode=false,
			    ),
        	@OA\Response(
            	response=200,
            	description="successful operation",
         		@OA\MediaType(
             		mediaType="application/pdf",
         		),
         	)
     )
    */

$app->Get('/servicios/reporte_factura', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/reporte_factura:'.json_encode($request->getQueryParams()));

	$parametros = $request->getQueryParams();

   	$archivo = $this->sistema->get_reporte_factura($parametros);

	$lineas= file_get_contents($archivo);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/pdf')->
					withAddedHeader('content-disposition', 'attachment; filename=archivo.pdf')->
					withAddedHeader('charset', 'utf-8;base64');

	if (isset($parametros["pdf"]))
		$myresponse->write($lineas);
	else
		$myresponse->write(base64_encode($lineas));
	
    return $myresponse;
});





/** 
 * @OA\Get(
		path="/backend/servicios/get_actividades",
	   	tags={"Comercios Contribuyentes"},
       	summary="Lista las actividades",
        description="Se deberían devolver las actividades activas o sobre las que se pueden realizar ddjj",
       @OA\Response(
           response=200,
           description = "Retorna el detalle de actividades",
          	@OA\JsonContent(
      			description="Listado de facturas de cuentas",
      			type="array",
	        	@OA\Items(
			    	@OA\Property(property="cod_actividad", type="string"),
			    	@OA\Property(property="descripcion", type="string"),
			    	@OA\Property(property="activo", type="string"),
					example =  {
        				"cod_actividad": "1",
        				"descripcion": "SERV Y VENTA INSUMOS INFORMATICOS",
        				"activo": "S",
        				"cod_grupo": "1"
    				},)		    	
	     		)
			)
       )
   )
 */
$app->get('/servicios/get_actividades', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/get_actividades:'.json_encode($request->getQueryParams()));
	$parametros = $request->getQueryParams();

	$datos = $this->sistema->get_actividades($parametros);

	$cantidad = count($datos);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
							withAddedHeader('Cantidad-Registros', "$cantidad");			

	$myresponse->write(json_encode($datos));
    return $myresponse;
});


/**
 *  @OA\Get(
       	path="/backend/servicios/valor_configuraciones",
       	tags={"Comercios Contribuyentes"},
       	summary="Consulta valores de configuración de declaraciones de comercios",
       	operationId="valor_configuraciones",
       	deprecated=false,
 		@OA\Parameter(
			name="campo",
 			in="query",
		    description="Identifica el campo de configuración a consultar",
		    required=true,
		    explode=false,
		    ),
    	@OA\Response(
 			response=200,
			description="successful operation",
      		@OA\JsonContent(
      			@OA\Property(property="valor", 
      				type="string",
      				example="012016",
      			)
      		)
		),
		@OA\Response(
			response=400,
			description="Invalid status value"
		),
		security={
			{"backend_auth": {"write:cuentas", "read:cuentas"}}}
    )
  */
$app->Post('/servicios/valor_configuraciones', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/valor_configuraciones:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

	$datos = $this->sistema->valor_configuraciones($parametros);

	$cantidad = count($datos);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
							withAddedHeader('Cantidad-Registros', "$cantidad");			

	$myresponse->write(json_encode($datos));
    return $myresponse;
});



/**
 *  @OA\Post(
       	path="/backend/servicios/tipos_ddjj",
       	tags={"Comercios Contribuyentes"},
       	summary="Consulta tipos de declaraciones de comercios",
       	operationId="tipos_ddjj",
       	deprecated=false,
       	@OA\RequestBody(
          	@OA\JsonContent(
          	    description = "Parmetros",
				type = "object"
			)
		),
    	@OA\Response(
 			response=200,
			description="successful operation",
      		@OA\JsonContent(
      			description = "Arreglo de tipos de DDJJ",
      			type = "array",
      			@OA\Items(
	      			@OA\Property(property="tipo_declaracion", type="string"),
	      			@OA\Property(property="descripcion", type="string"),
		      		example =  {
				        "tipo_declaracion": "1",
				        "descripcion": "DDJJ",
				    }
	      		),
      		),
		),
		security={
			{"backend_auth": {"write:cuentas", "read:cuentas"}}}
    )
  */
$app->Post('/servicios/tipos_ddjj', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/tipos_ddjj:'.$request->getBody()->getContents());

	$parametros = json_decode($request->getBody()->getContents(),true);

	$datos = $this->sistema->get_tipos_ddjj($parametros);

	$cantidad = count($datos);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
							withAddedHeader('Cantidad-Registros', "$cantidad");			

	$myresponse->write(json_encode($datos));
    return $myresponse;
});



/**
 *  @OA\Post(
       	path="/backend/servicios/declaraciones_juradas",
       	tags={"Comercios Contribuyentes"},
       	summary="Retorna las declaraciones juradas de un coemrcio",
       	operationId="declaraciones_juradas",
       	deprecated=false,
       	@OA\RequestBody(
          	@OA\JsonContent(
          		@OA\Property(property="filtro", 
          			type = "array",
					@OA\Items(
						description = "Filtro de busqueda de declaraciones juradas",
						example = {"id_comercio":"102"}
					)
          		),
          		@OA\Property(property="order_by", 
          			type = "array",
					@OA\Items(
						description = "Ordenamiento",
						example = {"fecha_vto":"ASC"}
					)
          		),
			)
		),
    	@OA\Response(
 			response=200,
			description="successful operation",
      		@OA\JsonContent(
      			description = "Arreglo de DDJJ",
      			type = "array",
      			@OA\Items(
	      			@OA\Property(property="nro_declaracion", type="string"),
	      			@OA\Property(property="valor", type="string"),
	      			@OA\Property(property="id_comercio", type="string"),
	      			@OA\Property(property="cuota", type="string"),
	      			@OA\Property(property="anio", type="string"),
	      			@OA\Property(property="importe", type="string"),
	      			@OA\Property(property="declaracion", type="string"),
	      			@OA\Property(property="rectificacion", type="string"),
	      			@OA\Property(property="saldo", type="string"),
	      			@OA\Property(property="estado", type="string"),
	      			@OA\Property(property="estado_format", type="string"),
	      			@OA\Property(property="id_factura", type="string"),
	      			@OA\Property(property="id_comprobante", type="string"),
		      		example =  {
				        "nro_declaracion": "654",
				        "rectificacion": "N",
				        "valor": "12000",
				        "id_comercio": "102",
				        "cuota": "6",
				        "anio": "2016",
				        "importe": "12000",
				        "saldo": "0",
				        "estado": "ANU",
				        "id_comprobante": null,
				        "estado_format": "Anulado",
				        "declaracion": "DDJJ",
				        "id_factura": null
				    }	      			
	      		),			    
      		)
		),
		security={
			{"backend_auth": {"write:cuentas", "read:cuentas"}}}
    )
  */
$app->Post('/servicios/declaraciones_juradas', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/declaraciones_juradas:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

	$filtro = array();
	$order_by = array();
	if(isset($parametros["filtro"])){
		$filtro = $parametros["filtro"];
	}
	if(isset($parametros["order_by"])){
		$order_by = $parametros["order_by"];
	}

   	$datos = $this->sistema->get_declaraciones_juradas($filtro,$order_by);

	$cantidad = count($datos);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
							withAddedHeader('Cantidad-Registros', "$cantidad");			

	$myresponse->write(json_encode($datos));
    return $myresponse;
});


/**
 *  @OA\Post(
       	path="/backend/servicios/anularDdjj",
       	tags={"Comercios Contribuyentes"},
       	summary="Anular DDJJ",
       	operationId="anularDdjj",
       	deprecated=false,
       	@OA\RequestBody(
          	@OA\JsonContent(
          		@OA\Property(property="nroDeclaracion", 
          			type = "string",
          		)
			)
		),
    	@OA\Response(
 			response=200,
			description="successful operation",
      		@OA\JsonContent(
          		@OA\Property(property="rta", 
          			type = "string",
          		),
          		example="OK"
      		)
		),
    	@OA\Response(
 			response=500,
			description="Error anulando DDJJ",
      		@OA\JsonContent(
          		@OA\Property(property="Error", 
          			type = "string",
          		),
          		example="El comprobante 3\/2019 tiene estado Pagada. No se puede anular"
      		)
		),
		security={{"backend_auth": {"write:cuentas", "read:cuentas"}}}
    )
 */
$app->Post('/servicios/anularDdjj', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/anularDdjj:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

	$rta= $this->sistema->anular_ddjj($parametros["nro_declaracion"]);

	if ($rta == 'OK') {
		$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
		$myresponse->write(json_encode(array('valor'=>$rta)));
	}else{
		$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
								withStatus(500);	
		$myresponse->write(json_encode(array('error'=>$rta)));
	}
    return $myresponse;
});


/**
 *  @OA\Post(
       	path="/backend/servicios/actividad_principal_comercio_x_id",
       	tags={"Comercios Contribuyentes"},
       	summary="Anular DDJJ",
       	operationId="actividad_principal_comercio_x_id",
       	deprecated=false,
       	@OA\RequestBody(
          	@OA\JsonContent(
          		@OA\Property(property="id_comercio", 
          			type = "string",
          		)
			)
		),
    	@OA\Response(
 			response=200,
			description="successful operation",
      		@OA\JsonContent(
          		@OA\Property(property="valor", 
          			type = "string",
          		),
          		example="456"
      		)
		),
    	@OA\Response(
 			response=500,
			description="Error",
      		@OA\JsonContent(
          		@OA\Property(property="Error", 
          			type = "string",
          		),
          		example="Mostrará un mensaje de error"
      		)
		),
		security={{"backend_auth": {"write:cuentas", "read:cuentas"}}}
    )
 */
$app->Post('/servicios/actividad_principal_comercio_x_id', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/actividad_principal_comercio_x_id:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

   	$rta= $this->sistema->get_actividad_principal_comercio_x_id($parametros["id_comercio"]);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});

$app->Post('/servicios/retornar_ddjj_ant_anterior', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/retornar_ddjj_ant_anterior:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

    $rta= $this->sistema->retornar_ddjj_ant_anterior($parametros["id_comercio"],$parametros["cod_actividad"],$parametros["tipo_declaracion"],$parametros["anio"],$parametros["cuota"]);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});

$app->Post('/servicios/retornar_impuesto_tipo_ddjj', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/retornar_impuesto_tipo_ddjj:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

    $rta= $this->sistema->retornar_impuesto_tipo_ddjj($parametros["id_comercio"],$parametros["tipo_declaracion"]);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});

$app->Get('/servicios/retornar_ddjj_def_anterior', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/retornar_ddjj_def_anterior:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

   	$rta= $this->sistema->retornar_ddjj_def_anterior($parametros["id_comercio"],$parametros["cod_actividad"],$parametros["tipo_declaracion"],$parametros["anio"],$parametros["cuota"]);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});


$app->Get('/servicios/retornar_fecha_calculo', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/retornar_fecha_calculo:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

   	$rta= $this->sistema->retornar_fecha_calculo($parametros["cod_impuesto"],$parametros["anio"],$parametros["cuota"]);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});



$app->Get('/servicios/retornar_ddjj_alicuota', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/retornar_ddjj_alicuota:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

    $rta= $this->sistema->retornar_ddjj_alicuota($parametros["id_comercio"], $parametros["cod_actividad"],$parametros["valor"],$parametros["fecha_calculo"]);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});



$app->Get('/servicios/retornar_ddjj_minimo', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/retornar_ddjj_minimo:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

   	$rta= $this->sistema->retornar_ddjj_minimo($parametros["id_comercio"], $parametros["cod_actividad"],$parametros["valor"],$parametros["fecha_calculo"]);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});

$app->Get('/servicios/retornar_ddjj_fijo', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/retornar_ddjj_fijo:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

   	$rta= $this->sistema->retornar_ddjj_fijo($parametros["id_comercio"], $parametros["cod_actividad"],$parametros["valor"],$parametros["fecha_calculo"]);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});

$app->Get('/servicios/calcular_ddjj_importe', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/calcular_ddjj_importe:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

    $rta= $this->sistema->calcular_ddjj_importe($parametros["id_comercio"], $parametros["cod_actividad"],$parametros["valor"],$parametros["alicuota"],$parametros["minimo"],$parametros["fecha_calculo"]);
	
	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});

$app->Get('/servicios/retornar_ddjj_en_carga', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/retornar_ddjj_en_carga:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

   	$rta= $this->sistema->retornar_ddjj_en_carga($parametros["id_comercio"], $parametros["cod_actividad"],$parametros["tipo_declaracion"],$parametros["anio"],$parametros["cuota"]);
	
	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});

$app->Get('/servicios/retornar_ddjj_pend_pago', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/retornar_ddjj_pend_pago:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

   	$rta= $this->sistema->retornar_ddjj_pend_pago($parametros["id_comercio"], $parametros["cod_actividad"],$parametros["tipo_declaracion"],$parametros["anio"],$parametros["cuota"]);
	
	$myresponse = $response->withAddedHeader('Content-Type', 'application/json');
	$myresponse->write(json_encode(array('valor'=>$rta)));
    return $myresponse;
});



$app->Post('/servicios/agregar_ddjj', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/agregar_ddjj:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

   	$datos= $this->sistema->agregar_ddjj($parametros);
	
	$cantidad = count($datos);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
							withAddedHeader('Cantidad-Registros', "$cantidad");			

	$myresponse->write(json_encode($datos));
    return $myresponse;
});



/** 
 * @OA\Post(
		path="/backend/servicios/proveedores_facturas",
	   	tags={"Proveedores"},
       	summary="Lista de facturas de cuentas de proveedores",
        description="Se pasa como parámetros la colección de cuentas asociadas al proveedor y retorna el listado de facturas de proveedores",
       	@OA\RequestBody(
          	@OA\JsonContent(
				@OA\Property(property="tipo_docuemnto", 
					type="string",
					),
				@OA\Property(property="nro_docuemnto", 
					type="string",
					),
				@OA\Property(property="email", 
					type="string",
					),
				@OA\Property(property="cuentas", 
          			type="array",
		            @OA\Items(
				    	@OA\Property(property="tipo_objeto", type="string"),
				    	@OA\Property(property="id_objeto", type="string"),
				    	@OA\Property(property="usuario", type="string"),
				    	@OA\Property(property="alias_cuenta", type="string"),     
				    	@OA\Property(property="id_cuenta", type="string"),     
				    	@OA\Property(property="tipo_cuenta", type="string"),     
				    	@OA\Property(property="desc_tipo_cuenta", type="string"),
				    	@OA\Property(property="nro_cuenta", type="string"),
				    	@OA\Property(property="descripcion", type="string"),          
				    	@OA\Property(property="responsable_pago", type="string"),          
				    	@OA\Property(property="id_persona", type="string"),          
				    	@OA\Property(property="enviar_mail", type="string",enum={"S", "N"}),          
				    	@OA\Property(property="pa_activo", type="string",enum={"S", "N"}),          
				    	@OA\Property(property="pa_fecha_desde", type="string"),          
				    	@OA\Property(property="pa_fecha_hasta", type="string"),
				    	@OA\Property(property="id_nro_cuenta", type="string"),	
		     		),
		     	)
			)
       ),
       @OA\Response(
           response=200,
           description = "Retorna el detalle de las facturas de proveedores",
          	@OA\JsonContent(
      			description="Listado de facturas de proveedores",
      			type="array",
	        	@OA\Items(
			    	@OA\Property(property="idFactura", type="string"),
			    	@OA\Property(property="fecha", type="string",example="2017-02-14 00:00:00"),
			    	@OA\Property(property="anio", type="string"),
			    	@OA\Property(property="expedientes", type="string"),
			    	@OA\Property(property="importe", type="string" ),
			    	@OA\Property(property="saldo", type="string" ),
			    	@OA\Property(property="estado", type="string" ),
			    	@OA\Property(property="ordenespago", type="array",
			            @OA\Items(
					    	@OA\Property(property="id", type="string"),
					    	@OA\Property(property="ordenPago", type="string"),

			     		),
			    	),
					example = {"nrofactura":"B 000200101426",
						"idfactura":"24",
						"fecha":"2015-01-06 00:00:00",
						"anio":"2015",
						"expediente":"\/\/",
						"importe":"77015.49",
						"saldo":"77015.49",
						"estado":"Cargada",
						"ordenespago":{"id":15,
										"ordenPago":10
									}
					},)		    	
	     		)
			)
       )
   )
 */

$app->Post('/servicios/proveedores_facturas', function (Request $request, Response $response, array $args) {

	$this->logger->debug('/servicios/proveedores_facturas:'.$request->getBody()->getContents());
	$parametros = json_decode($request->getBody()->getContents(),true);

   	$datos = $this->sistema->proveedores_facturas($parametros);
	
	$cantidad = count($datos);

	$myresponse = $response->withAddedHeader('Content-Type', 'application/json')->
							withAddedHeader('Cantidad-Registros', "$cantidad");			

	$myresponse->write(json_encode($datos));
    return $myresponse;
});

$app->Post('/backend/importar', function (Request $request, Response $response, array $args) {

	$parametros = $request->getParsedBody();

	$this->logger->debug('/backend/importar '.json_encode($parametros));

	$options="";
	$error_params = false;
	if( !isset($parametros["entidad"])){
		$response->write("Falta parametro 'entidad= una de personas, cuentas, cuentas_personas, deudas, parametros, referencias'");	
		$error_params = true;
	}
	$entidad= $parametros["entidad"];

	if( !isset($parametros["campos"])){
		$response->write("Falta parametro 'campos=field_1,field_2,,field_5,...'");	
		$error_params = true;
	}
	if(isset($parametros["options"])){
		$options=$parametros["options"];
	}
	if( $error_params)
		return $response;

	$id_empresa=1;
	if( strcmp("T" , $options ) === 0 ){
		$result=SlimBackend::truncate_data($id_empresa , $entidad );
		if($result!=="OK"){
				$response->write($result);
				return $response;
		}		
	}

	$campos=explode(',', $parametros["campos"]);

	$directory = $this->get('upload_directory');

    $uploadedFiles = $request->getUploadedFiles();
 
     // handle multiple inputs with the same key
    foreach ($uploadedFiles as $uploadedFile) {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);

           	$result = SlimBackend::import_data( $id_empresa,$entidad,
           		$directory."/".$filename,$campos,$options,',');
			if($result["resultado"]!=="OK"){
					$response->write("Error importando datos:".print_r($result,true));
					return $response;
			}else		
            	$response->write("Entidad: $entidad Archivo: $filename Insertados:".$result["inserts"]);
        }
    }
    return $response;
});


