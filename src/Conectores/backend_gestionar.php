<?php	
use SIUToba\rest\rest;

class backend_gestionar implements backend_servicio
{

	public function get_cuentas($filtro){
		return dao_cuentas::get_cuentas_x_persona($filtro);
	}

    public function get_cuentas_x_objetos($array_cuentas){
    	return dao_cuentas::get_cuentas_x_objetos( $array_cuentas);
    }

	public function get_cuenta($objeto){
		return dao_cuentas::get_cuenta($objeto);
	}

	public function get_declaraciones_juradas($filtro, $order_by){
		return dao_comercios::get_declaraciones_juradas($filtro, $order_by);
	}

	public function get_actividades($filtro){
		return dao_comercios::get_actividades($filtro);
	}

	public function valor_configuraciones($campo){
		return dao_varios::valor_configuraciones($campo);
	}

	public function get_actividad_principal_comercio_x_id($id_comercio){
		return dao_comercios::get_actividad_principal_comercio_x_id($id_comercio);
	}

	public function get_tipos_ddjj($filtro){
		return dao_comercios::get_tipos_ddjj($filtro);
	}

	public function retornar_impuesto_tipo_ddjj($id_comercio, $tipo_declaracion){
		return dao_comercios::retornar_impuesto_tipo_ddjj($id_comercio, $tipo_declaracion);
	}

	public function retornar_fecha_calculo($cod_impuesto, $anio, $cuota){
		return dao_comercios::retornar_fecha_calculo($cod_impuesto, $anio, $cuota);
	}

	public function retornar_ddjj_alicuota($id_comercio, $cod_actividad, $valor, $fecha_calculo){
		return dao_comercios::retornar_ddjj_alicuota($id_comercio, $cod_actividad, $valor, $fecha_calculo);
	}

	public function retornar_ddjj_minimo($id_comercio, $cod_actividad, $valor, $fecha_calculo){
		return dao_comercios::retornar_ddjj_minimo($id_comercio, $cod_actividad, $valor, $fecha_calculo);
	}

	public function retornar_ddjj_en_carga($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota){
		return dao_comercios::retornar_ddjj_en_carga($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota);
	}

	public function retornar_ddjj_pend_pago($id_comercio, $cod_actividad, $tipo_declaracion, $anio_raw, $cuota_raw){
		return dao_comercios::retornar_ddjj_pend_pago($id_comercio, $cod_actividad, $tipo_declaracion, $anio_raw, $cuota_raw);
	}

	public function retornar_ddjj_def_anterior($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota){
		return dao_comercios::retornar_ddjj_def_anterior($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota);
	}

	public function retornar_ddjj_importe($nro_declaracion){
		return dao_comercios::retornar_ddjj_importe($nro_declaracion);
	}

	public function retornar_ddjj_ant_anterior($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota){
		return dao_comercios::retornar_ddjj_ant_anterior($id_comercio, $cod_actividad, $tipo_declaracion, $anio, $cuota);
	}

	public function retornar_ddjj_fijo($id_comercio, $cod_actividad, $valor, $fecha_calculo){
		return dao_comercios::retornar_ddjj_fijo($id_comercio, $cod_actividad, $valor, $fecha_calculo);
	}

	public function calcular_ddjj_importe($id_comercio, $cod_actividad, $valor, $alicuota, $minimo, $fecha_calculo){
		return dao_comercios::calcular_ddjj_importe($id_comercio, $cod_actividad, $valor, $alicuota, $minimo, $fecha_calculo);
	}


	public function anular_ddjj($nro_declaracion){
		return dao_comercios::anular_ddjj($nro_declaracion);
	}

	public function agregar_ddjj($datos){
		try{
			toba::db()->abrir_transaccion();
			$rta= dao_comercios::insertar_ddjj($datos, false);
			if($rta["rta"]=="OK"){
				$nro_declaracion = $rta["valor"];
				$rta2 = dao_comercios::confirmar_ddjj($nro_declaracion,false);
				if($rta2=="OK"){
					toba::db()->cerrar_transaccion();

					$ddjj= dao_comercios::get_declaracion_jurada_x_nro($nro_declaracion);

					return array("rta"=>"OK",'nroDeclaracion' => $nro_declaracion, 'idComprobante' => $ddjj['id_comprobante'], 'idFactura' => $ddjj['id_factura']);					
				}else{
					toba::db()->abortar_transaccion();
					return array('error' => $rta2);
				}
			}else{
				toba::db()->abortar_transaccion();
				return array('error' => $rta["rta"]);
			}
		}catch(Exception $e){			
			toba::db()->abortar_transaccion();
			try{$error= $e->getMessage();}catch(Exception $ex){$error="ERROR_INTERNO";}
			return array('error' => $error);
		}
	}

    public function buscar_cuenta($filtro){
		return dao_cuentas::get_cuenta_filtro($filtro);
	}

	public function resumen_pago($id_comprobantes, $fecha_actualizacion){
		try{
			$mensaje_error="Error generar_facturas_x_id_comprobantes";
			toba::db()->abrir_transaccion();
			foreach ($id_comprobantes as $key => $value) {
				$sql= "INSERT INTO RE_TMP_DEUDAS_WEB
						(id_comprobante)
						VALUES (".$value['id'].");";

				dao_varios::ejecutar_sql($sql, false);
			}

			$sql = "SELECT concat_all( 'select a.id_comprobante FROM re_tmp_deudas_web a, re_facturas b WHERE a.id_comprobante = b.id_comprobante AND b.fecha_2vto>=trunc(sysdate)','#') facturas from dual";

			$fila= toba::db()->consultar_fila($sql);

			if( isset($fila["facturas"]) ){
				$rta ="OK";
				$comprobantesFact = "#".$fila["facturas"]."#";

			}else{  
				$sql= "BEGIN :resultado:= pkg_facturas.generar_facturas_x_ids(to_date(:fecha_actualizacion, 'yyyy-mm-dd'), :comprobantesFact); END;";

				$parametros = array (array(  'nombre' => 'fecha_actualizacion',
											'tipo_dato' => PDO::PARAM_STR,
											'longitud' => 20,
											'valor'=>$fecha_actualizacion),
										array(  'nombre' => 'comprobantesFact',
											'tipo_dato' => PDO::PARAM_STR,
											'longitud' => 4000,
											'valor'=> ''),
										array(  'nombre' => 'resultado',
											'tipo_dato' => PDO::PARAM_STR,
											'longitud' => 4000,
											'valor' => '')
										);

				$resultado = toba::db()->ejecutar_store_procedure($sql, $parametros);

				if (strcasecmp($resultado[2]['valor'], 'OK') <> 0){
					toba::db()->abortar_transaccion();
					throw new toba_error($resultado[2]['valor']);
				}
				$rta = $resultado[2]['valor'];
				$comprobantesFact = $resultado[1]['valor'];
			}
			toba::db()->cerrar_transaccion();

			// Tomar el total de las facturas generadas y la máxima fecha de vto.
			$sql= "SELECT F.id_comprobante, f.importe_1vto total, f.fecha_2vto fecha_vto, IMP.COD_IMPUESTO COD_CONCEPTO, IMP.DESCRIPCION DESC_CONCEPTO,
					'F.Nro:'||F.NRO_FACTURA||' '|| pkg_varios.significado_dominio('RE_TIPO_CUENTA',CTA.TIPO_CUENTA)||' '|| CTA.NRO_CUENTA||':'||pkg_facturas.armar_descripcion_factura(F.ID_COMPROBANTE, 'N') DESCRIPCION
				FROM RE_FACTURAS f, RE_COMPROBANTES_CUENTA CC, RE_IMPUESTOS IMP,RE_CUENTAS CTA
				WHERE F.ID_COMPROBANTE=CC.ID_COMPROBANTE
				  and CC.COD_IMPUESTO=IMP.COD_IMPUESTO
				  AND CC.ID_CUENTA=CTA.ID_CUENTA
				  AND instr('$comprobantesFact', '#'||f.id_comprobante||'#')>0";

			$comprobantes =toba::db()->consultar($sql);

			$sql= "SELECT sum(importe_1vto) total, max(f.fecha_2vto) fecha_vto
				FROM RE_FACTURAS f
				WHERE instr('$comprobantesFact', '#'||f.id_comprobante||'#')>0";
			$datos= toba::db()->consultar_fila($sql);

			return array("rta" => $rta, 
						"comprobantesFact" => $comprobantesFact,
						"comprobantes" => $comprobantes,
						"total" => $datos["total"],
						"max_fecha_vto" => $datos["fecha_vto"]);
		} catch (Exception $e) {
			return array("rta" => "Error", 
						"error" => $e->get_message() );
		}
	}


		return dao_deudas::generar_facturas_x_id_comprobantes($id_comprobantes, $fecha_actualizacion);
	}

	public function crear_operacion_pago($comprobantes){
		return dao_cobros::crear_operacion_pago($comprobantes);
	}

    public function consulta_deuda($parametros){

		if (isset($parametros['cuentas']))
			$cuentas= $parametros['cuentas'];
		else
			$cuentas= null;

		if (isset($parametros['tipoDeuda']))
			$tipoDeuda= $parametros['tipoDeuda'];
		else
			$tipoDeuda= "todo";		

    	return dao_deudas::consulta_deuda($cuentas, $tipoDeuda);
    }

    public function get_facturas($parametros){
    	return dao_deudas::consulta_facturas($parametros);
    }

    public function alta_debito_automatico($parametros){ 
    	return dao_cuentas::alta_debito_automatico($parametros);}

    public function baja_debito_automatico($parametros){
    	return dao_cuentas::baja_debito_automatico($parametros);}

    public function alta_factura_electronica($parametros){
    	return dao_cuentas::alta_factura_electronica($parametros);}

    public function baja_factura_electronica($parametros){
    	return dao_cuentas::baja_factura_electronica($parametros);}


	public function anular_operacion_pago( $id_operacion ){
		$transaccion_abierta = true;
		try{
			toba::db()->abrir_transaccion();
			$transaccion_abierta = true;
		
			$cadena_cobros= dao_cobros::get_cadena_cobros_x_id_op($id_operacion);

			$rta_anular= dao_cobros::anular_operacion_caja($id_operacion, $cadena_cobros);
			toba::db()->cerrar_transaccion();
			$transaccion_abierta = false;
			return $rta_anular;
		}catch(Exception $e){
			if ($transaccion_abierta )
				toba::db()->abortar_transaccion();
			return array("rta"=>$e->getMessage());
		}
	}

    public function get_reporte_factura($parametros){

		rest::app()->logger->debug("backend_gestionar.get_reporte_factura()".json_encode($parametros));

    	list($reporte, $param) = $this->get_datos_impresion($parametros);

		rest::app()->logger->debug("get_reporte_factura() datos impresion $reporte".json_encode($param));

		//$reporte = new generador_reportes($reporte);
		$rep_param = array();

		return manejador_reportes::generar_archivo_pdf( $reporte , 
				NULL,NULL,$param);
    }

    private function get_datos_impresion($seleccion)
	{
		if( isset($seleccion["p_cadena_facturas"])){
			return [
				're_rep_imprime_facturas',
				 [array("nombre" => 'p_cadena_facturas', 
				                 "tipo" => 'S',
				                 "valor" => $seleccion["p_cadena_facturas"])
				 ]
			];
		}

		$factura = dao_deudas::get_factura_x_id($seleccion['id_comprobante']);

		// No es original?
		if ($factura['original'] !== 'S') {
			return [
				're_rep_imprime_facturas',
				 [array("nombre" => 'p_cadena_facturas', 
				                 "tipo" => 'S',
				                 "valor" => "#{$seleccion['id_comprobante']}#")
				]
			];
		}
		$detalles = dao_deudas::get_detalles_factura($seleccion);
		$comp = dao_actualizciones::get_comprobante_x_id(
			$detalles[0]['id_comprobante_cancela']);

		// Es convenio?
		if ($comp['tipo_comprobante'] == 3) {
			return [
				're_rep_imprime_facturas',
				 [array("nombre" => 'p_cadena_facturas', 
				                 "tipo" => 'S',
				                 "valor" => "#{$seleccion['id_comprobante']}#")
				]
			];
		}
		$deuda = dao_actualizciones::get_deuda_x_id($detalles[0]['id_comprobante_cancela']);

		// Es eventual?
		if (!is_null($deuda['cod_eventual'])) {
			return [
				're_rep_imprime_facturas',
				 [array("nombre" => 'p_cadena_facturas', 
				                 "tipo" => 'S',
				                 "valor" => "#{$seleccion['id_comprobante']}#")
				]
			];
		}
		// Para todo lo demas:
		$reporte = dao_varios::retornar_dato(
			'RE_IMPUESTOS',
			'REPORTE_INDIVIDUAL',
			'COD_IMPUESTO',
			$factura['cod_impuesto']
		);

		$cadena = '#';
		for ($i = 0; $i < count($detalles); ++$i) {
			$cadena .= "{$detalles[$i]['id_comprobante_cancela']}#";
		}

		return [
			$reporte,
				 [array("nombre" => 'p_cadena_comp', 
				                 "tipo" => 'S',
				                 "valor" => $cadena),
					array("nombre" => 'p_id_cuenta', 
				                 "tipo" => 'S',
				                 "valor" => $factura['id_cuenta'])
				]
		];
	}
	
    public function get_consulta_dinamica($reporte,$parametros){
        rest::app()->logger->debug("backend_gestionar.get_consulta_dinamica() $reporte:".json_encode($parametros));
        return dao_consultas_dinamicas::ejecutar_consulta($reporte,$parametros);
	}

	public function proveedores_facturas($parametros){
		return dao_afi::proveedores_facturas($parametros);	
	}
}
?>
