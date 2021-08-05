<?php

namespace Backend\Conectores;
use \Backend\SlimBackend;

class backend_clubonline implements backend_servicio
{

    public static function CallAPI($id_empresa,$method,$service,$data , $bool_file = false){

    $logger = \Backend\SlimBackend::Backend()->logger;

    $api_url =\Backend\SlimBackend::getParametros($id_empresa,"api_url",0,100,false)[0]["valor"];
    

    $user_id = null;//\Backend\SlimBackend::getParametros($id_empresa,"user_id",0,100,false)[0]["valor"];

    $user_password = null;//\Backend\SlimBackend::getParametros($id_empresa,"user_password",0,100,false)[0]["valor"];

    $hash_key_id = null;
    $fila =\Backend\SlimBackend::getParametros($id_empresa,"api_hash_key_id",0,100,false);
    if(sizeof($fila)>0)
        $hash_key_id = $fila[0]["valor"];

    if( $bool_file ){
        $file_name = tempnam(sys_get_temp_dir(), "download_file");

        $file_d = fopen($file_name, "w");
    }
    $url_servicio = $api_url.$service;
    if( $method === "GET" and isset($data)) {
        if(is_array($data)) 
            $url_servicio = $api_url.$service."?".http_build_query($data);
        else
            $url_servicio = $api_url.$service."?".$data;
    }
    $curl = curl_init($url_servicio);


    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    if(isset($user_id)){
      curl_setopt($curl, CURLOPT_USERPWD, "$user_id:$user_password");
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $hdr_options=['Content-Type: application/json'];
    if(isset($hash_key_id))
        $hdr_options[] = 'Hash-Key-Id: '.$hash_key_id;
    curl_setopt($curl, CURLOPT_HTTPHEADER, $hdr_options);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $datos = str_replace("\":null" , "\": \"\"",json_encode(array_a_utf8($data)));

    switch ($method) {
        case "GET":
            curl_setopt($curl, CURLOPT_POSTFIELDS, $datos );
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            break;
        case "POST":
            curl_setopt($curl, CURLOPT_POSTFIELDS, $datos );
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_POSTFIELDS, $datos);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            break;
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE"); 
            curl_setopt($curl, CURLOPT_POSTFIELDS, $datos);
            break;
    }

    if( $bool_file ){
        curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt ($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_FILE, $file_d);        
        $response = curl_exec($curl);
        fclose($file_d);
        $res_data=$file_name;
        if( $response === false){
            $res_data=null;
        }
    }else{
        $response = curl_exec($curl);
        $res_data = array_a_utf8(json_decode(str_replace("\": null" , "\": \"\"",$response),true));
    }
    /* Check for 404 (file not found). */
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    // Check the HTTP Status code
    $logger->debug("server_rest::CallApi:".$url_servicio);
    $logger->debug("server_rest::method:".$method);
    $logger->debug("server_rest::data:".$datos);
    $logger->debug("server_rest::httpCode:".$httpCode);
    $logger->debug("server_rest::response raw:".$response);
    if( $bool_file ){
        $logger->debug("self::response:".$res_data);        
    }
    else 
        $logger->debug("self::response:".json_encode($res_data));
    curl_close($curl);
    return array("httpCode"=>$httpCode , "response"=>$res_data);
  }

    public function get_cuentas($parametros){ 
      $logger = \Backend\SlimBackend::Backend()->logger;

      $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

      $taxId = \Backend\SlimBackend::getParametros($id_empresa,"taxId",0,100,false)[0]["valor"];
      $privateKey = \Backend\SlimBackend::getParametros($id_empresa,"privateKey",0,100,false)[0]["valor"];

      if (isset($parametros["nro"]))
          $nro_documento= $parametros["nro"];
      elseif (isset($parametros["nro_documento"]))
          $nro_documento= $parametros["nro_documento"];
      else
          $nro_documento= -1;     


      $data = ["taxId"  => $taxId,
               "privateKey" =>$privateKey,
               "personalId" => $nro_documento+0 ];

      $rta = self::CallAPI( $id_empresa , "POST", "ClubMember" , $data);

 /*
 {[{"tipo_cuenta":"SERV","nro":"1300700400000","tipo_objeto":"SERV","id_objeto":"1300700400000","id_empresa":"1","id_sucursal":"13"}]}
 */
      $datos = [];
      if($rta["httpCode"]===200){
        $datos[] = array( "tipo_cuenta"=> "SOC" , "nro"=>$rta["response"]["personalId"],"tipo_objeto"=>"SOC","id_objeto"=>$rta["response"]["personalId"] ); 
      }
      return $datos;
    }

    public function get_cuentas_x_objetos($objetos){
      $logger = \Backend\SlimBackend::Backend()->logger;

      $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

      $taxId = \Backend\SlimBackend::getParametros($id_empresa,"taxId",0,100,false)[0]["valor"];
      $privateKey = \Backend\SlimBackend::getParametros($id_empresa,"privateKey",0,100,false)[0]["valor"];

      $cuentas=[];
      foreach ($objetos as $key => $value) {
        $data = ["taxId"  => $taxId,
                 "privateKey" =>$privateKey,
                 "personalId" => $value["id_objeto"]+0 ];

        $rta = self::CallAPI( $id_empresa , "POST", "ClubMember" , $data);

        if($rta["httpCode"]===200){
          $cuentas[$key]["alias_cuenta"]=$objetos[$key]["alias_cuenta"] ?? null;
          $cuentas[$key]["id_cuenta"]=$rta["response"]["idClubMember"];
          $cuentas[$key]["tipo_cuenta"]=$objetos[$key]["tipo_objeto"];
          $cuentas[$key]["nro_cuenta"]=$rta["response"]["personalId"];
          $cuentas[$key]["desc_tipo_cuenta"]="Socio";
          $cuentas[$key]["descripcion"]=$rta["response"]["firstName"]." ".$rta["response"]["lastName"];
          $cuentas[$key]["responsable_pago"]=$rta["response"]["firstName"]." ".$rta["response"]["lastName"];
          $cuentas[$key]["id_persona"]=$rta["response"]["personalId"];
          $cuentas[$key]["enviar_mail"]="N";
          $cuentas[$key]["pa_activo"]="N";
          $cuentas[$key]["pa_fecha_desde"]=null;
          $cuentas[$key]["pa_fecha_hasta"]=null;          
        }
      }
      return $cuentas;
    }

    public function consulta_deuda($parametros){
      $logger = \Backend\SlimBackend::Backend()->logger;

      $logger->debug( "consulta_deuda".json_encode($parametros));

      $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

      $taxId = \Backend\SlimBackend::getParametros($id_empresa,"taxId",0,100,false)[0]["valor"];
      $privateKey = \Backend\SlimBackend::getParametros($id_empresa,"privateKey",0,100,false)[0]["valor"];

      if( isset($parametros['tipoDeuda']))
          $tipoDeuda= $parametros['tipoDeuda'];
      else
          $tipoDeuda='tod';


      $deudas=[];
      foreach ($parametros["cuentas"] as $key => $cuenta) {
              # code...
        $data = ["taxId"  => $taxId,
                 "privateKey" =>$privateKey,
                 "personalId" => $cuenta["nro_cuenta"]+0 ];

        $filas=[];

        $rta = self::CallAPI( $id_empresa , "POST", "BalanceComposition" , $data);

        if($rta["httpCode"]===200){
          $filas=$rta["response"];  

          $nro_cuenta= $cuenta["nro_cuenta"]+0;
          $deuda = array_map(
                  function($row) use($nro_cuenta) 
                  { 
                    $vto = substr($row["dueDate"],6,4)."-".
                                      substr($row["dueDate"],3,2)."-".
                                      substr($row["dueDate"],0,2);
                    $cod_concepto=1;
                    $desc_concepto="Cuota Social";
                    $comp_data = array( "id"=>$row["idCreditDueDate"],
                                        "monto"=>$row['balance'],
                                        "cod_concepto"=>$cod_concepto,
                                        "desc_concepto"=>$desc_concepto,
                                        "descripcion"=>$row["description"]);
                    return                 
                          ["cont_id"=>$row["clubMember"] ,
                           "cont_desc1"=>$row["clubMember"],
                           "cont_desc2"=>"",
                           "cue_id"=> $nro_cuenta,
                           "cue_desc1"=>$row["clubMember"],
                           "cue_desc2"=>"",
                           "imp_id"=>$cod_concepto,
                           "imp_desc1"=>$desc_concepto,
                           "per_id"=> 1,
                           "deu_id"=>$comp_data,
                           "deu_desc1"=> $row["description"],
                           "deu_vto"=>$vto,
                           "deu_capital"=> $row['balance'],
                           "deu_recargo"=>0
                          ]; 
                  },
                  $filas);

        }
        $deudas = array_merge($deudas , $deuda);
      }

      $mensaje_error = "";
      $prox = null; 

      $array_rta=array();
        
      if(isset($deuda))
          $array_rta["deuda"]=$deudas;
      if(isset($prox))
          $array_rta["prox"]=$prox;

      return $array_rta;
    }    

    public function resumen_pago($id_comprobantes, $fecha_actualizacion){
      try{

        $total = 0;
        $comprobantes=[];
        $comprobantes_id=[];        
        foreach ($id_comprobantes as $key => $value) {
          $total = $total + $value["id"]["monto"];
          $comprobantes[] = ["id_comprobante"=>$value["id"]["id"],
                              "total"=>$value["id"]["monto"],
                              "fecha_vto"=>$fecha_actualizacion,
                              "cod_concepto"=>$value["id"]["cod_concepto"],
                              "desc_concepto"=>$value["id"]["desc_concepto"],
                              "descripcion"=>$value["id"]["descripcion"],
                            ];
          $comprobantes_id[]=array("id"=>$value["id"]["id"]);
        }

        return array("rta" => "OK", 
              "comprobantesFact" => $comprobantes_id,
              "comprobantes" => $comprobantes,
              "total" => $total,
              "max_fecha_vto" => $fecha_actualizacion);
      } catch (Exception $e) {
        return array("rta" => "Error", 
              "error" => $e->get_message() );
      }
    }
    
    public function crear_operacion_pago($comprobantes){
      $logger = \Backend\SlimBackend::Backend()->logger;

      $logger->debug( "crear_operacion_pago".json_encode($comprobantes));

      $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

      $taxId = \Backend\SlimBackend::getParametros($id_empresa,"taxId",0,100,false)[0]["valor"];
      $privateKey = \Backend\SlimBackend::getParametros($id_empresa,"privateKey",0,100,false)[0]["valor"];

      $id_operaciones=[];

      $data=[
        "taxId"=>$taxId,
        "privateKey"=>$privateKey
      ];

      foreach ($comprobantes["comprobantes"] as $key => $comprobante) {

        $id_operaciones[]=$comprobante["id_comprobante"];
        
        $data["credits"][]=["idCreditDueDate"=>$comprobante["id_comprobante"]+0];

      }

      $rta = self::CallAPI( $id_empresa , "POST", "LockCredits" , $data);

      if($rta["httpCode"]===200){
        return array("rta" => "OK", 'id_operacion_pago' => $id_operaciones);  
      }
      return array("rta" => "Error", 'id_operacion_pago' => -1,"message"=>$rta["response"]);
      
    }

    public function confirmar_operacion_pago($id_operacion){

      $logger = \Backend\SlimBackend::Backend()->logger;

      $logger->debug( "confirmar_operacion_pago".json_encode($id_operacion));

      $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

      $taxId = \Backend\SlimBackend::getParametros($id_empresa,"taxId",0,100,false)[0]["valor"];
      $privateKey = \Backend\SlimBackend::getParametros($id_empresa,"privateKey",0,100,false)[0]["valor"];

      $id_operaciones=[];

      $data=[
        "taxId"=>$taxId,
        "privateKey"=>$privateKey
      ];

      foreach ($id_operacion as $key => $comprobante) {

        $id_operaciones[]=$comprobante;
        
        $data["credits"][]=["idCreditDueDate"=>$comprobante+0];

      }

      $rta = self::CallAPI( $id_empresa , "POST", "CollectCredits" , $data);

      if($rta["httpCode"]===200){
        return array("rta" => "OK", 'id_operacion_pago' => $id_operaciones);  
      }
      return array("rta" => "Error", 'id_operacion_pago' => -1,"message"=>$rta["response"]);
      
    }

    public function anular_operacion_pago($id_operacion){

      $logger = \Backend\SlimBackend::Backend()->logger;

      $logger->debug( "anular_operacion_pago".json_encode($id_operacion));

      $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

      $taxId = \Backend\SlimBackend::getParametros($id_empresa,"taxId",0,100,false)[0]["valor"];
      $privateKey = \Backend\SlimBackend::getParametros($id_empresa,"privateKey",0,100,false)[0]["valor"];

      $id_operaciones=[];

      $data=[
        "taxId"=>$taxId,
        "privateKey"=>$privateKey
      ];

      foreach ($id_operacion as $key => $comprobante) {

        $id_operaciones[]=$comprobante;
        
        $data["credits"][]=["idCreditDueDate"=>$comprobante+0];

      }

      $rta = self::CallAPI( $id_empresa , "POST", "UnlockCredits" , $data);

      if($rta["httpCode"]===200){
        return array("rta" => "OK", 'id_operacion_pago' => $id_operaciones);  
      }

      return array("rta" => "Error", 'id_operacion_pago' => -1,"message"=>$rta["response"]);
    }

    private function get_datos_impresion($seleccion){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }


    public function get_declaraciones_juradas($filtro, $order_by){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function get_actividades($filtro){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function valor_configuraciones($campo){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function get_actividad_principal_comercio_x_id($id_comercio){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function get_tipos_ddjj($filtro){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function retornar_impuesto_tipo_ddjj($id_comercio, $tipo_declaracion){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function retornar_fecha_calculo($cod_impuesto, $anio, $cuota){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function retornar_ddjj_alicuota($id_comercio, $cod_actividad, $valor, $fecha_calculo){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function retornar_ddjj_minimo($id_comercio, $cod_actividad, $valor, $fecha_calculo){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function retornar_ddjj_en_carga($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function retornar_ddjj_pend_pago($id_comercio, $cod_actividad, $tipo_declaracion, $anio_raw, $cuota_raw){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function retornar_ddjj_def_anterior($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function retornar_ddjj_importe($nro_declaracion){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function retornar_ddjj_ant_anterior($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function retornar_ddjj_fijo($id_comercio, $cod_actividad, $valor, $fecha_calculo){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function calcular_ddjj_importe($id_comercio, $cod_actividad, $valor, $alicuota, $minimo, $fecha_calculo){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function anular_ddjj($nro_declaracion){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function buscar_cuenta($filtro){
        return array("resultado"=>'NO_IMPLEMENTADO'); 
    }

    public function desasociar_cuenta($filtro){
        return array("resultado"=>'NO_IMPLEMENTADO'); 
    }

    public function get_facturas($filtro){

      $logger = \Backend\SlimBackend::Backend()->logger;

      $logger->debug( "get_facturas".json_encode($filtro));

      $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

      $taxId = \Backend\SlimBackend::getParametros($id_empresa,"taxId",0,100,false)[0]["valor"];
      $privateKey = \Backend\SlimBackend::getParametros($id_empresa,"privateKey",0,100,false)[0]["valor"];

      if (isset($filtro["nro_documento"]))
          $nro_documento= $filtro["nro_documento"];
      else
          $nro_documento= -1;     


      $data = ["taxId"  => $taxId,
               "privateKey" =>$privateKey,
               "personalId" => $nro_documento+0 ];


/*
[{"nro_factura":"241949", 
"id_comprobante":"489491",
"descripcion_factura":"5\/2017",
"fecha_1vto":"2017-11-24 00:00:00",
"importe_1vto":"229",
"importe_2vto":"230.13",
"tipo":"1",
"desc_tipo":"Tasa\/Impuesto",
"desc_estado":"Pagada",
"estado":"1",
"anio":"2017",
"impuesto":"TASA DESARROLLO URBANO Y SERV. RETRIBUIDOS",
"pagar":"N",
"cuenta":{"alias_cuenta":"plan anual","id_cuenta":"5862","tipo_cuenta":"TCR1","nro_cuenta":"3258","desc_tipo_cuenta":"Partida","descripcion":"Partida 3258 SANTIAGO QUIROGA","responsable_pago":"SANTIAGO QUIROGA","id_persona":"4724","enviar_mail":"N","pa_activo":"N","pa_fecha_desde":"","pa_fecha_hasta":"","id_nro_cuenta":"3258"}
}

 {
 "number": "0-10",
 "creditDate": "01/05/2020",
 "amount": 2500.0,
 "comments": "",
 "business": "Cuota Social",
 "collectionNumber": 6,
 "dueDate": "31/05/2020",
 "document": "Ticket",
 "clubMember": "Machado, Fernando",
 "description": "Cuota Social > May-20 > Grupo Familiar",
 "collectionDate": "29/05/2020"
 }

{
 "idCreditDueDate": 509859,
 "number": "0-12",
 "creditDate": "01/05/2020",
 "amount": 500.0,
 "punishmentAmount": 0,
 "balance": 500.0,
 "comments": "",
 "business": "Cuota Social",
 "dueDate": "31/05/2020",
 "document": "Ticket",
 "clubMember": "Machado, Fernando",
 "description": "Cuota Social > May-20 > Grupo Familiar",
 "status": "Pendiente"
 }

*/
      $rta = self::CallAPI( $id_empresa , "POST", "Credits" , $data);

      if($rta["httpCode"]===200){
        $datos=$rta["response"];  
      }else{
        $datos =[];
      }



      $facturas = array_map(
              function($row) use($nro_documento) 
              { 
                $vto = substr($row["dueDate"],6,4)."-".
                                  substr($row["dueDate"],3,2)."-".
                                  substr($row["dueDate"],0,2);
                return                 
                      ["nro_factura"=>$row["number"], 
                       "id_comprobante"=>$row["idCreditDueDate"],
                        "descripcion_factura"=>$row["description"],
                        "fecha_1vto"=>$vto,
                        "importe_1vto"=>$row["amount"],
                        "importe_2vto"=>$row["amount"],
                        "tipo"=>"1",
                        "desc_tipo"=>$row["business"],
                        "desc_estado"=>$row["status"],
                        "estado"=>"1",
                        "anio"=>substr($row["dueDate"],6,4),
                        "impuesto"=>$row["business"],
                        "pagar"=>"N",
                        "cuenta"=>[
                          "alias_cuenta"=>"",
                            "id_cuenta"=>$nro_documento,
                            "tipo_cuenta"=>"SOC",
                            "nro_cuenta"=>$nro_documento,
                            "desc_tipo_cuenta"=>"Socio",
                            "descripcion"=>$row["clubMember"],
                            "responsable_pago"=>$row["clubMember"],
                            "id_persona"=>$nro_documento,
                            "enviar_mail"=>"N",
                            "pa_activo"=>"N",
                            "pa_fecha_desde"=>"",
                            "pa_fecha_hasta"=>"",
                            "id_nro_cuenta"=>"0"
                        ]
                      ]; 
              },
              $datos);
      return $facturas; 

    }

    public function get_reporte_factura($parametros){
      $logger = \Backend\SlimBackend::Backend()->logger;

      $logger->debug( "get_facturas".json_encode($parametros));

      $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

      $taxId = \Backend\SlimBackend::getParametros($id_empresa,"taxId",0,100,false)[0]["valor"];
      $privateKey = \Backend\SlimBackend::getParametros($id_empresa,"privateKey",0,100,false)[0]["valor"];

      if (isset($parametros["id_comprobante"]))
          $id_comprobante= $parametros["id_comprobante"];
      else
          $id_comprobante= -1;     


      $data = ["taxId"  => $taxId,
               "privateKey" =>$privateKey,
               "idCreditDueDate" => $id_comprobante+0 ];

      $rta = self::CallAPI( $id_empresa , "POST", "PrintCredit" , $data,true);

      if($rta["httpCode"]===200){
        $datos=$rta["response"];  
      }else{
        $datos =null;
      }

      return $datos; 

    }


    public function alta_debito_automatico($parametros)
        { return array("resultado"=>'NO_IMPLEMENTADO');}

    public function baja_debito_automatico($parametros)
        { return array("resultado"=>'NO_IMPLEMENTADO');}

    public function alta_factura_electronica($parametros)
        { return array("resultado"=>'NO_IMPLEMENTADO');}

    public function baja_factura_electronica($parametros)
        { return array("resultado"=>'NO_IMPLEMENTADO');}

    public function get_consulta_dinamica($reporte,$parametros){

        $consulta = new \Backend\Modelos\Erp\ConsultasDinamicas(SlimBackend::Backend());

        return $consulta->ejecutar($reporte,$parametros);
    }

    public function proveedores_facturas($parametros)
        { return array("resultado"=>'NO_IMPLEMENTADO');}

    public function datos_socio($parametros){
      $logger = \Backend\SlimBackend::Backend()->logger;

      $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

      $taxId = \Backend\SlimBackend::getParametros($id_empresa,"taxId",0,100,false)[0]["valor"];
      $privateKey = \Backend\SlimBackend::getParametros($id_empresa,"privateKey",0,100,false)[0]["valor"];

      if (isset($parametros["nro_documento"]))
          $nro_documento= $parametros["nro_documento"];
      else
          $nro_documento= -1;     


      $data = ["taxId"  => $taxId,
               "privateKey" =>$privateKey,
               "personalId" => $nro_documento+0 ];

      $rta = self::CallAPI( $id_empresa , "POST", "ClubMember" , $data);

      $datos = [];
      if($rta["httpCode"]===200){
        $datos["nroSocio"]=$rta["response"]["number"];
        $datos["dni"]=$rta["response"]["personalId"];
        $datos["nombre"]=$rta["response"]["firstName"]." ".$rta["response"]["lastName"];
        $datos["categoria"]=$rta["response"]["condition"];
        $datos["fnacimiento"]=isset($rta["response"]["birthDate"])? $rta["response"]["birthDate"] :"";
        $datos["fingreso"]=isset($rta["response"]["addDate"])? $rta["response"]["addDate"] :"";
        if( $rta["response"]["doorAccess"])
            $datos["estado"]="Activo";
        else
            $datos["estado"]="Baja";
        $datos["mesesImpagos"]="-1";
        $datos["fotoUrl"] = $rta["response"]["photoUrl"];  
      }
      return $datos;
    }    


    public function obtener_parametros($parametro){
        $logger = \Backend\SlimBackend::Backend()->logger;

        $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

        $datos = \Backend\SlimBackend::getParametros($id_empresa,$parametro["parametro"],0,100,false);

        return $datos;
    }    



    public function establecer_parametro($parametro){
        
        $logger = \Backend\SlimBackend::Backend()->logger;

        $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

        $rta = \Backend\SlimBackend::setParametro($id_empresa,$parametro["parametro"]);

        return $rta;
    }
}
?>