<?php 

namespace Backend\Conectores;

interface backend_servicio
{
    /**
     */
    //TODO Borrar public function get_tipos_imponibles(); 
    public function get_declaraciones_juradas($filtro, $order_by);

    public function get_actividades($filtro);

    public function valor_configuraciones($campo);

    public function get_actividad_principal_comercio_x_id($id_comercio);

    public function get_tipos_ddjj($filtro);

    public function retornar_impuesto_tipo_ddjj($id_comercio, $tipo_declaracion);

    public function retornar_fecha_calculo($cod_impuesto, $anio, $cuota);

    public function retornar_ddjj_alicuota($id_comercio, $cod_actividad, $valor, $fecha_calculo);

    public function retornar_ddjj_minimo($id_comercio, $cod_actividad, $valor, $fecha_calculo);

    public function retornar_ddjj_en_carga($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota);

    public function retornar_ddjj_pend_pago($id_comercio, $cod_actividad, $tipo_declaracion, $anio_raw, $cuota_raw);

    public function retornar_ddjj_def_anterior($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota);

    public function retornar_ddjj_ant_anterior($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota);

    public function retornar_ddjj_fijo($id_comercio, $cod_actividad, $valor, $fecha_calculo);

    public function calcular_ddjj_importe($id_comercio, $cod_actividad, $valor, $alicuota, $minimo, $fecha_calculo);

    public function anular_ddjj($nro_declaracion);

    public function buscar_cuenta($filtro);

    public function get_cuentas($filtro);

    public function resumen_pago($id_comprobantes, $fecha_actualizacion);

    public function crear_operacion_pago($comprobantes);

    public function confirmar_operacion_pago($id_operacion);

    public function anular_operacion_pago($id_operacion );
    
    public function consulta_deuda($parametros);

    // Servicios de facturas
    public function get_facturas($filtro);

    public function alta_debito_automatico($parametros);

    public function baja_debito_automatico($parametros);

    public function alta_factura_electronica($parametros);

    public function baja_factura_electronica($parametros);

    public function get_reporte_factura($parametros);

    public function get_consulta_dinamica($reporte,$parametros);
    
}

?>