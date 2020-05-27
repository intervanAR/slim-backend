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
        $res_data = json_decode(utf8_to_array(str_replace("\": null" , "\": \"\"",$response)),true);
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

    public function get_cuentas($filtro){ 
        return [];
    }

    public function get_cuentas_x_objetos($objetos){
        return [];
    }

    public function consulta_deuda($parametros){
      $logger = \Backend\SlimBackend::Backend()->logger;

      $logger->debug( "consulta_deuda".json_encode($parametros));

      $id_empresa = \Backend\SlimBackend::Backend()->settings['id_empresa'];

      $club = \Backend\SlimBackend::getParametros($id_empresa,"club_id",0,100,false)[0]["valor"];

      if( isset($parametros['tipoDeuda']))
          $tipoDeuda= $parametros['tipoDeuda'];
      else
          $tipoDeuda='tod';

      if (isset($parametros["nro_documento"]))
          $nro_documento= $parametros["nro_documento"];
      else
          $nro_documento= -1;     

      $logger->debug( "consulta_deuda" );

 

      $data = ["idCompany"  => $club+0,
               "personalId" => $nro_documento+0 ];

      $filas = self::CallAPI( $id_empresa , "POST", "BalanceComposition" , $data)["response"];

      $deuda = array_map(
              function($row) use($club,$nro_documento) 
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
                       "cue_id"=> $club."-".$nro_documento,
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


        $mensaje_error = "";
        $prox = null; 


            /*

                {"CONT_ID":"30672920554",
                  "ID_EMPRESA":"1","NRO_SUCURSAL":"0","ID_FACTURA":"41",
                  "DEU_CAPITAL":"107.69",
                  "CONT_DESC1":"ACCEDER S.R.L",
                  "DEU_VTO":"29-SEP-04",
                  "TIPO_FACTURA":"A",
                  "NRO_FACTURA":"17"
                }


        select cuit as CONT_ID,* 
                    RAZON_SOCIAL AS CONT_desc1,* 
                   'Cuit '|| TO_CHAR(CUIT) AS CONT_DESC2,
                   id_cuenta as cue_id,
                   Fondo ||' '|| id_cuenta as cue_desc1,
                   '' as cue_desc2,
                   '1' as imp_id,
                   tipo_credito as imp_desc1,
                   nro_cuota as PER_ID ,
                   'Cta:'||nro_cuota as PER_DESC1,
                   id_deuda as DEU_ID ,
                   'Cta:'||nro_cuota ||' Vto:'||TO_CHAR(vencimiento,'dd/mm/yy') deu_desc1,
                   vencimiento as DEU_VTO,
                   nvl(capital,0) as deu_capital,
                   nvl(intereses,0)+nvl(gastos,0)+nvl(intereres_act,0) deu_recargo
                   from backend_det_deuda   
                  where id_session = '$nro_cuit'
                  and tipo_deuda='A VENCER'
                  order by CONT_DESC1,CUE_DESC1,IMP_DESC1,DEU_VTO DESC        
        */
        $array_rta=array();
        
        if(isset($deuda))
            $array_rta["deuda"]=$deuda;
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


      $id_operaciones=[];

      $data=[];

      foreach ($comprobantes["comprobantes"] as $key => $comprobante) {

        $id_operaciones[]=$comprobante["id_comprobante"];
        
        $data[]=["idCreditDueDate"=>$comprobante["id_comprobante"]+0,"response"=>"true"];

      }

      $rta = self::CallAPI( $id_empresa , "POST", "CollectionsResponse" , $data);

      if($rta["httpCode"]===200){
        return array("rta" => "OK", 'id_operacion_pago' => $id_operaciones);  
      }
      return array("rta" => "Error", 'id_operacion_pago' => -1,"message"=>$rta["response"]);
      
    }

    public function confirmar_operacion_pago($id_operacion){

      return array("rta" => "OK");

    }

    public function anular_operacion_pago($id_operacion)
        { return array("rta"=>'OK');}


    public function get_reporte_factura($parametros){
        return array("resultado"=>'NO_IMPLEMENTADO');
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



    public function get_facturas($filtro){
        return array("resultado"=>'NO_IMPLEMENTADO');
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
}
?>