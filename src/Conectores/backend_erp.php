<?php

namespace Backend\Conectores;
use \Backend\SlimBackend;

class backend_erp implements backend_servicio
{
    public function get_cuentas($filtro){ 


        $consulta = new \Backend\Modelos\Erp\Clientes(SlimBackend::Backend());

        $condicion = [];

        if ( isset($filtro) &&
             isset($filtro["nro_documento"]) ) {
            $condicion["PERSONAS.CUIT"] = $filtro["nro_documento"];
        }


        if ( isset($filtro) &&
             isset($filtro["mail"]) ) {
            $condicion["CLIENTES.EMAIL"]=$filtro["mail"];
        }

        if(sizeof($condicion)<1) return array();


        $join = ["[><]PERSONAS"=>["CUIT"=>"CUIT" ]];
        $campos = ["CLIENTES.ID_EMPRESA","CLIENTES.NRO_SUCURSAL","CLIENTES.NRO_CUENTA"];
 
        $datos =  $consulta->selectj($join,$campos,$condicion);

        $consulta->logger->debug('backend_erp:get_cuentas:'.print_r($consulta->db->log(),true));

        $cuentas = array_map(
                function($row) { return array("tipo_objeto"=>"TCC", "id_objeto"=>$row['ID_EMPRESA']."-".$row['NRO_SUCURSAL']."-".$row['NRO_CUENTA'])  ; },
                $datos);
        
        return $cuentas;
    }

    public function get_cuentas_x_objetos($objetos){

        $consulta = new \Backend\Modelos\Erp\Clientes(SlimBackend::Backend());

        $cuentas=[];

        foreach ($objetos as $key => $value) {
            if( preg_match("/-/",$value["id_objeto"])==1 ) { 
                list($id_empresa, $nro_sucursal, $nro_cuenta) = preg_split("/-/",$value["id_objeto"]);

                $join = ["[><]PERSONAS"=>["CUIT"=>"CUIT" ]];
                $campos = ["CLIENTES.ID_EMPRESA","CLIENTES.NRO_SUCURSAL","CLIENTES.NRO_CUENTA",
                           "PERSONAS.NOMBRE(RESPONSABLE)","PERSONAS.NOMBRE(DESCRIPCION)",
                           "CLIENTES.CUIT(ID_PERSONA)"];

                $condicion["CLIENTES.ID_EMPRESA"]=$id_empresa;
                $condicion["CLIENTES.NRO_SUCURSAL"]=$nro_sucursal;
                $condicion["CLIENTES.NRO_CUENTA"]=$nro_cuenta;

                $datos =  $consulta->selectj($join,$campos,$condicion);

                $consulta->logger->debug('backend_erp:get_cuentas_x_objetos'.print_r($consulta->db->log(),true));
                $consulta->logger->debug('backend_erp:get_cuentas_x_objetos'.print_r($datos,true));


                if(isset($datos[0]) && isset($datos[0]['ID_EMPRESA'])  ){
                    $cuentas[$key]["alias_cuenta"]=$objetos[$key]["alias_cuenta"] ?? null;
                    $cuentas[$key]["id_cuenta"]=$datos[0]['ID_EMPRESA']."-".$datos[0]['NRO_SUCURSAL']."-".$datos[0]['NRO_CUENTA'];
                    $cuentas[$key]["tipo_cuenta"]=$objetos[$key]["tipo_objeto"];
                    $cuentas[$key]["nro_cuenta"]=$datos[0]['ID_EMPRESA']."-".$datos[0]['NRO_SUCURSAL']."-".$datos[0]['NRO_CUENTA'];
                    $cuentas[$key]["desc_tipo_cuenta"]="Cuenta Cobrar";
                    $cuentas[$key]["descripcion"]=$datos[0]["DESCRIPCION"];
                    $cuentas[$key]["responsable_pago"]=$datos[0]["RESPONSABLE"];
                    $cuentas[$key]["id_persona"]=$datos[0]["ID_PERSONA"];
                    $cuentas[$key]["enviar_mail"]="N";
                    $cuentas[$key]["pa_activo"]="N";
                    $cuentas[$key]["pa_fecha_desde"]=null;
                    $cuentas[$key]["pa_fecha_hasta"]=null;          
                }
            }
        }
        return $cuentas;
    }

    public function consulta_deuda($parametros){
        if( isset($parametros['tipoDeuda']))
            $tipoDeuda= $parametros['tipoDeuda'];
        else
            $tipoDeuda='tod';

        if (isset($parametros["nro_documento"]))
            $nro_cuit= $parametros["nro_documento"];
        else
            $nro_cuit= -1;     

        $mensaje_error = "";

        $consulta = new \Backend\Modelos\Erp\Facturas(SlimBackend::Backend());

        $join = ["[><]CLIENTES"=>["ID_EMPRESA"=>"ID_EMPRESA",
                                  "NRO_SUCURSAL"=>"NRO_SUCURSAL",
                                  "NRO_LEGAJO"=>"NRO_LEGAJO"],
                "[><]PERSONAS"=>["CLIENTES.CUIT"=>"CUIT"]
            ];

        $campos = ["CLIENTES.CUIT(cont_id)",
                   "FACTURAS_CLIENTE.ID_EMPRESA",
                   "FACTURAS_CLIENTE.NRO_SUCURSAL",
                   "FACTURAS_CLIENTE.ID_FACTURA",
                   "CLIENTES.CUIT",
                   "CLIENTES.NRO_CUENTA",
                   "FACTURAS_CLIENTE.SALDO_IMPAGO(deu_capital)",
                   "PERSONAS.NOMBRE(cont_desc1)",
                   "PERSONAS.NOMBRE",
                   "FACTURAS_CLIENTE.FECHA_GENERACION(deu_vto)",
                   "TIPO_FACTURA"=>\Medoo\Medoo::raw("DECODE(FACTURAS_CLIENTE.TIPO_FACTURA,'Z','C',FACTURAS_CLIENTE.TIPO_FACTURA)"),
                   "NRO_FACTURA"=>\Medoo\Medoo::raw("TO_CHAR(MOD(FACTURAS_CLIENTE.NRO_FACTURA,10000000),'00000000')")
                 ];

        $condicion["FACTURAS_CLIENTE.ESTADO"]=["CON","PAR"];
        $condicion["CLIENTES.CUIT"]=$nro_cuit;
        $condicion["ORDER"]=["PERSONAS.NOMBRE"=>"ASC",
                             "CLIENTES.NRO_CUENTA"=>"ASC",
                             "FACTURAS_CLIENTE.ID_FACTURA"=>"ASC",
                             "FACTURAS_CLIENTE.FECHA_VENCIMIENTO"=>"DESC"];


        $deuda = null;
        $prox = NULL;

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
        if( $tipoDeuda === "deuda" || $tipoDeuda === "todo"){
            $condicion["FACTURAS_CLIENTE.FECHA_VENCIMIENTO[<]"]= \Medoo\Medoo::raw('SYSDATE+30');
            $datos = $consulta->selectj($join,$campos,$condicion);
    
            $deuda = array_map(
                    function($row) { return array_merge($row,
                            ["cont_desc2"=>"Cuit:".$row['CUIT'],
                             "cue_id"=>$row['ID_EMPRESA']."-".$row['NRO_SUCURSAL']."-".$row['NRO_CUENTA'],
                             "cue_desc1"=>$row['NOMBRE']."/".$row['NRO_CUENTA'],
                             "cue_desc2"=>$row['ID_EMPRESA']."-".$row['NRO_SUCURSAL']."-".$row['NRO_CUENTA'],
                             "imp_id"=>1,
                             "imp_desc1"=>"Periodo",
                             "per_id"=> 1,
                             "deu_id"=>$row['ID_EMPRESA']."-".$row['ID_FACTURA'],
                             "deu_desc1"=> "Fac. ".$row['TIPO_FACTURA']." ".$row['NRO_FACTURA']." Vto".$row['deu_vto'],
                             "deu_recargo"=>0
                            ]
                        )  ; 
                    },
                    $datos);


            $consulta->logger->debug('backend_erp:consulta_deuda'.print_r($consulta->db->log(),true));
            $consulta->logger->debug('backend_erp:consulta_deuda'.print_r($deuda,true));
        }
        if( $tipoDeuda === "prox" || $tipoDeuda === "todo"){
            $condicion["FACTURAS_CLIENTE.FECHA_VENCIMIENTO[>=]"]= \Medoo\Medoo::raw('SYSDATE+30');
            $prox = $consulta->selectj($join,$campos,$condicion); 
            $consulta->logger->debug('backend_erp:consulta_deuda'.print_r($consulta->db->log(),true));
            $consulta->logger->debug('backend_erp:consulta_deuda'.print_r($prox,true));

        }
        $array_rta=array();
        
        if(isset($deuda))
            $array_rta["deuda"]=$deuda;
        if(isset($prox))
            $array_rta["prox"]=$prox;

        return $array_rta;
    }    

    public function resumen_pago($id_comprobantes, $fecha_actualizacion){
      try{

        //
        // Recorrer facturas y sumarlas
        // [{"id":"1-0-41"},{"id":"1-0-54"},{"id":"1-0-42"}]
        $consulta = new \Backend\Modelos\Erp\Facturas(SlimBackend::Backend());

        $campos = ["SALDO_IMPAGO",
                   "FECHA_VENCIMIENTO"=>\Medoo\Medoo::raw("TO_CHAR(FECHA_VENCIMIENTO,'yyyy-mm-dd HH24:MI:SS')")];
        $total = 0;
        $comprobantes=[];
        foreach ($id_comprobantes as $key => $value) {
          list($id_empresa,$id_factura) =preg_split("/-/",$value["id"]);

          $condicion["FACTURAS_CLIENTE.ID_EMPRESA"]=$id_empresa;
          $condicion["FACTURAS_CLIENTE.ID_FACTURA"]=$id_factura;
          $factura = $consulta->select($campos,$condicion); 
          $total += $factura[0]["SALDO_IMPAGO"];
          $comprobantes[] = ["id_comprobante"=>$value["id"],
                              "total"=>$factura[0]["SALDO_IMPAGO"],
                              "fecha_vto"=>$factura[0]["FECHA_VENCIMIENTO"]
                            ];

        }

        return array("rta" => "OK", 
              "comprobantesFact" => $id_comprobantes,
              "comprobantes" => $comprobantes,
              "total" => $total,
              "max_fecha_vto" => $fecha_actualizacion);
      } catch (Exception $e) {
        return array("rta" => "Error", 
              "error" => $e->get_message() );
      }
    }
    
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


    public function crear_operacion_pago($comprobantes){
        return array("resultado"=>'NO_IMPLEMENTADO');
    }

    public function anular_operacion_pago($id_operacion)
        { return array("resultado"=>'NO_IMPLEMENTADO');}


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