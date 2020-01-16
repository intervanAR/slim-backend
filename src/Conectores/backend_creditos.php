<?php 
namespace Backend\Modelos;

class backend_creditos implements backend_servicio
{

    public function get_cuentas($filtro){
        return dao_creditos::get_cuentas_x_persona($filtro);
    }

    public function get_cuenta($cuenta)
        { array(resultado=>'NO_IMPLEMENTADO');}


    public function get_cuentas_x_objetos($array_cuentas){
        return dao_creditos::get_cuentas_x_objetos( $array_cuentas);
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
        return dao_creditos::consulta_deuda($nro_cuit,$tipoDeuda);
    }

    public function get_reporte_factura($parametros){

        rest::app()->logger->debug("backend_credtitos.get_reporte_factura()".json_encode($parametros));

        list($reporte, $param) = $this->get_datos_impresion($parametros);

        rest::app()->logger->debug("get_reporte_factura() datos impresion $reporte".json_encode($param));

        return manejador_reportes::generar_archivo_pdf( $reporte , 
                NULL,NULL,$param);
    }

    private function get_datos_impresion($seleccion)
    {
        if( isset($seleccion["p_cadena_facturas"])){
            return [
                'rep_chequera_pat',
                 [array("nombre" => 'p_cadena_facturas', 
                                 "tipo" => 'S',
                                 "valor" => $seleccion["p_cadena_facturas"])
                 ]
            ];
        }
        if( isset($seleccion["id_comprobante"])){
            return [
                'cre_fact_cli',
                 [array("nombre" => 'P_ID_COMPROBANTE', 
                                 "tipo" => 'S',
                                 "valor" => $seleccion["id_comprobante"])
                 ]
            ];
        }
    }

    /**
     */


    public function get_declaraciones_juradas($filtro, $order_by){}

    public function get_actividades($filtro){}

    public function valor_configuraciones($campo){}

    public function get_actividad_principal_comercio_x_id($id_comercio){}

    public function get_tipos_ddjj($filtro){}

    public function retornar_impuesto_tipo_ddjj($id_comercio, $tipo_declaracion){}

    public function retornar_fecha_calculo($cod_impuesto, $anio, $cuota){}

    public function retornar_ddjj_alicuota($id_comercio, $cod_actividad, $valor, $fecha_calculo){}

    public function retornar_ddjj_minimo($id_comercio, $cod_actividad, $valor, $fecha_calculo){}

    public function retornar_ddjj_en_carga($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota){}

    public function retornar_ddjj_pend_pago($id_comercio, $cod_actividad, $tipo_declaracion, $anio_raw, $cuota_raw){}

    public function retornar_ddjj_def_anterior($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota){}

    public function retornar_ddjj_importe($nro_declaracion){}

    public function retornar_ddjj_ant_anterior($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota){}

    public function retornar_ddjj_fijo($id_comercio, $cod_actividad, $valor, $fecha_calculo){}

    public function calcular_ddjj_importe($id_comercio, $cod_actividad, $valor, $alicuota, $minimo, $fecha_calculo){}

    public function anular_ddjj($nro_declaracion){}

    public function buscar_cuenta($filtro){}

    public function resumen_pago($id_comprobantes, $fecha_actualizacion){
        return dao_creditos::generar_facturas_x_id_comprobantes($id_comprobantes, $fecha_actualizacion);
    }

    public function crear_operacion_pago($comprobantes){}

    public function anular_operacion_pago($id_operacion)
        { array(resultado=>'NO_IMPLEMENTADO');}


    public function get_facturas($filtro){
        return dao_creditos::consulta_facturas($filtro);
    }

    public function alta_debito_automatico($parametros)
        { array(resultado=>'NO_IMPLEMENTADO');}

    public function baja_debito_automatico($parametros)
        { array(resultado=>'NO_IMPLEMENTADO');}

    public function alta_factura_electronica($parametros)
        { array(resultado=>'NO_IMPLEMENTADO');}

    public function baja_factura_electronica($parametros)
        { array(resultado=>'NO_IMPLEMENTADO');}

    public function get_consulta_dinamica($reporte,$parametros){
        rest::app()->logger->debug("backend_gestionar.get_consulta_dinamica() $reporte:".json_encode($parametros));
        return dao_consultas_dinamicas::ejecutar_consulta($repotet,$parametros);
    }

    public function proveedores_facturas($parametros)
        { return array(resultado=>'NO_IMPLEMENTADO');}
}
?>