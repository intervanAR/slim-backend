<?php	
namespace Backend\Conectores;
use \Backend\SlimBackend;


use Backend\Modelos\Agua\Cuentas;
use Backend\Modelos\Agua\Deudas;
use Backend\Modelos\Agua\CuotasConvenio;
use Backend\Modelos\Agua\DeudaTmp;
use Backend\Modelos\Agua\FacturaTmp;
use Backend\Modelos\Agua\Factura;
use Backend\Modelos\ReportePDF;

class backend_arsa extends backend_aguas
{
    public static function reporteFacturas($params) {
        
        if(!isset($params["p_cadena_facturas"]) && !isset($params["id_comprobante"])) return "";
        $consulta = new Factura(SlimBackend::Backend());


    	$database = $consulta->db;
    	$logger = $consulta->logger;

        // create new PDF document
        $pdf = new ReportePDF('images/frente_arsa.jpg' , 210 , 297 , PDF_PAGE_ORIENTATION, "mm", array(0 => 210, 1 => 297 ) /* PDF_PAGE_FORMAT*/, true, 'UTF-8', false);

        // set document information
        $pdf->SetTitle('Ticket');
        $pdf->SetSubject('Factura');
        $pdf->SetKeywords('Oficina Virtual, Gestionar, Intervan');


            
        $consulta = new Factura(SlimBackend::Backend());

        if(isset($params["p_cadena_facturas"])){
            $facturas = preg_split("/#/",substr($params["p_cadena_facturas"],1,-1));
        }
        elseif(isset($params["id_comprobante"])){
            $facturas = [$params["id_comprobante"]];
        }

        foreach ($facturas as $key => $factura) {
        	# code...
            $database->pdo->beginTransaction();

			$sth = $database->pdo->prepare("select fac.id_empresa 
				    ,fac.id_sucursal
				    ,cue.cuenta
				    ,fac.nro_factura
				    ,fac.cod_iva
				    ,fac.comentario1
				    ,fac.comentario2
				    ,fac.comentario3
				    ,case when fac.NRO_FACt_cupon1 is not null then 
                          to_char(cup1.fecha_1vto ,'DD/MM/YYyy') 
				    	  else to_char(fac.fecha_1vto,'DD/MM/YYyy')  end  fecha_1vto_txt
				    ,case when fac.NRO_FACt_cupon2 is not null 
                          then to_char(cup2.fecha_1vto ,'DD/MM/YYyy') 
				    	  else to_char(fac.fecha_2vto,'DD/MM/YYyy')  end  fecha_2vto_txT
				    ,case when fac.NRO_FACt_cupon1 is not null 
				    	then cup1.importe_1vto + cup1.iva_1vto + cup1.ley25413 
				    	else fac.importe_1vto + fac.iva_1vto + fac.ley25413 
				    	end  total_1vto
				    ,case when fac.NRO_FACt_cupon2 is not null 
				    	then cup2.importe_1vto + cup2.iva_1vto + cup2.ley25413 
				    	else fac.importe_2vto + fac.iva_2vto + fac.ley25413_2
				    	end  total_2vto
				    ,fac.importe_1vto + fac.iva_1vto + fac.ley25413 total_fac
				    ,fac.ley25413
				    ,fac.anulada
				    ,fac.fecha_emision
                    ,to_char(fac.fecha_emision ,'DD/MM/YY') fecha_emision_txt
				    ,PKG_COD_BARRAS.CODIFICA_2_DE_5(fac.cod_barra) COD_BARRA
				    ,case when fac.NRO_FACt_cupon1 is not null then 
                          PKG_COD_BARRAS.CODIFICA_2_DE_5(CUP1.cod_barra)   
				    	  else PKG_COD_BARRAS.CODIFICA_2_DE_5(fac.cod_barra)  end  COD_bARRA1
				    ,case when fac.NRO_FACt_cupon2 is not null then 
                          PKG_COD_BARRAS.CODIFICA_2_DE_5(CUP2.cod_barra)   
				    	  else PKG_COD_BARRAS.CODIFICA_2_DE_5(fac.cod_barra)  end  COD_bARRA2
				    ,case when fac.NRO_FACt_cupon1 is not null 
                          then CUP1.cod_barra  
				    	  else FAC.COD_BARRA  end  COD_bARRA1_NRO
				    ,case when fac.NRO_FACt_cupon2 is not null 
                          then CUP2.cod_barra  
				    	  else FAC.COD_BARRA  end  COD_bARRA2_NRO
				    ,fac.nombre_usuario apellido_nombre
				    ,fac.cuit
				    ,cue.TIPO_FACTURACION
				    ,cue.id_medidor
				    ,cue.nro_compartido
				    ,iva.descripcion iva
				    ,iva.detalle_factura iva_detalle_factura
				    ,fac.tipo_servicio
				    ,ts.descripcion servicio
				    ,tf.descripcion tipo_facturacion_des
				    ,( SELECT RV_MEANING
				                    FROM CG_REF_CODES
				                    WHERE RV_DOMAIN = 'CATEGORIA_USUARIO'
				                    AND RV_LOW_VALUE = TS.IMPRIME_FACTURA
				                   ) categoria
				    ,cue.cod_localidad                  
				    ,cal_inm.descripcion inmueble_calle
				    ,cue.inmueble_nro
				    ,cue.inmueble_piso
				    ,cue.inmueble_dto
				    ,fac.postal_calle_nro postal_calle
				    ,fac.postal_piso
				    ,fac.postal_dto
				    ,loc.descripcion localidad
				    ,pro.descripcion provincia
				    ,fac.POSTAL_COD_POSTAL     codigo_postal
				    ,'D:'||catastro_dto||' '||
				    'C:'||catastro_circunscrip ||' '||
				    'S:'||catastro_seccion||' '||
				    'M:'||catastro_manzana ||' '||
				    'L:'||catastro_lote_letra ||' '||
				    'UF:'||catastro_uf ||' .' datos_catastrales,
                    emp.descripcion nombre_empresa,        
                    'Nuestras oficinas atienden al público de 8 a 13hs en '||pkg_modelos.direccion_localidad_sucursal(fac.id_empresa,fac.id_sucursal,cue.cod_localidad)||' Tel. '||
  pkg_modelos.telefono_localidad_sucursal(fac.id_empresa,fac.id_sucursal,cue.cod_localidad) telefono
				from facturas fac
				  ,cuentas cue
				  ,calles cal_inm
				  ,localidades loc
				  ,provincias pro
				  ,tipos_iva iva
				  ,tipos_servicios ts
				  ,tipos_facturacion tf
                  ,empresas emp
                  ,facturas cup1
                  ,facturas cup2
				where  fac.id_empresa=emp.id_empresa
                and    fac.id_empresa   = :id_empresa
				and    fac.id_sucursal  = :id_sucursal
                and    fac.nro_factura=:nro_factura
                and    fac.cod_iva = :cod_iva
				and    cue.cuenta       = fac.cuenta
				and    cue.id_empresa   = fac.id_empresa
				and    cue.id_sucursal  = fac.id_sucursal
				and   cue.inmueble_cod_calle = cal_inm.cod_calle
				and   loc.cod_localidad = fac.postal_cod_localidad
				and   pro.cod_provincia = fac.postal_cod_provincia
				and   iva.cod_iva = fac.cod_iva
				and   iva.fecha_vigencia IN(     SELECT MAX(fecha_vigencia)
				                                                         FROM TIPOS_IVA TI
				                                                         WHERE TI.COD_IVA = fac.COD_IVA
				                                                    )
				and   fac.tipo_servicio = ts.tipo_servicio
				and   cue.tipo_facturacion = tf.tipo_facturacion
				and   fac.ID_EMPRESA=cup1.id_empresa(+)
				and   fac.ID_SUCURSAL=cup1.id_sucursal(+)
				and fac.NRO_FACt_cupon1=cup1.nro_factura(+)
				and fac.COD_IVA=cup1.cod_iva(+)
				and fac.ID_EMPRESA=cup2.id_empresa(+)
				and fac.ID_SUCURSAL=cup2.id_sucursal(+)
				and fac.NRO_FACt_cupon2=cup2.nro_factura(+)
				and fac.COD_IVA=cup2.cod_iva(+)
				");

			list( $id_empresa,$id_sucursal,$nro_factura,$cod_iva) =
					preg_split("/-/",$factura);
			$logger->debug(" reporteFactura $id_empresa , $id_sucursal , $nro_factura , $cod_iva "); 
			$sth->bindParam(':id_empresa', $id_empresa, \PDO::PARAM_INT);
			$sth->bindParam(':id_sucursal', $id_sucursal, \PDO::PARAM_INT);
			$sth->bindParam(':nro_factura', $nro_factura, \PDO::PARAM_INT);
			$sth->bindParam(':cod_iva', $cod_iva, \PDO::PARAM_STR);

			if( !$sth->execute()  ){
				$logger->debug( "backend_aguas:reporteFactura 1 error".print_r($sth->errorInfo(),true));
                $database->pdo->rollback();
				return "backend_aguas:reporteFactura 1 error".print_r($sth->errorInfo(),true);
	        }

            $row = $sth->fetchAll()[0];

            //
            // Verificar la factura Original
            //
            $sth = $database->pdo->prepare("begin :original := pkg_backend.datos_Factura(
                        :id_empresa,
                        :id_sucursal,
                        :cod_iva,
                        :nro_factura,
                        :cuenta,
                        :cp_tipo_deuda,    
                        :cp_nro_medidor,
                        :cp_estado_ant,
                        :cp_fecha_lect_ant,
                        :cp_estado_act,
                        :cp_fecha_lect_act,
                        :cp_consumo,
                        :cp_cons_per_ant,
                        :cp_cons_anio_ant, 
                        :cp_promedio,
                        :cp_compartido,
                        :cp_prox_vto,
                        :cp_periodo_facturacion,
                        :cp_tipo_facturacion,
                        :cp_servicio_des,
                        :cp_tipo_servicio
                        );
                  end;");

            $sth->bindParam(':id_empresa', $id_empresa, \PDO::PARAM_INT);
            $sth->bindParam(':id_sucursal', $id_sucursal, \PDO::PARAM_INT );
            $sth->bindParam(':cod_iva', $cod_iva, \PDO::PARAM_STR );
            $sth->bindParam(':nro_factura',$nro_factura, \PDO::PARAM_INT);
            $sth->bindParam(':cuenta',$row["CUENTA"], \PDO::PARAM_INT);
            $sth->bindParam(':cp_tipo_deuda',$cp_tipo_deuda, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_nro_medidor',$cp_nro_medidor, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_estado_ant',$cp_estado_ant, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_fecha_lect_ant',$cp_fecha_lect_ant, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_estado_act',$cp_estado_act, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_fecha_lect_act',$cp_fecha_lect_act, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_consumo', $cp_consumo, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_cons_per_ant', $cp_cons_per_ant, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_cons_anio_ant', $cp_cons_anio_ant, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_promedio', $cp_promedio, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_compartido',$cp_compartido, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_prox_vto',$cp_prox_vto, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_periodo_facturacion', $cp_periodo_facturacion, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_tipo_facturacion', $cp_tipo_facturacion, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_servicio_des', $cp_servicio_des, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':cp_tipo_servicio', $cp_tipo_servicio, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );
            $sth->bindParam(':original', $original, \PDO::PARAM_STR || \PDO::PARAM_INPUT_OUTPUT ,100 );


            if( !$sth->execute()  ){
                $logger->debug( "backend_aguas:reporteFactura 1.8 ( $id_empresa, $id_sucursal ) error".print_r($sth->errorInfo(),true));
                $database->pdo->rollback();
                return "backend_aguas:reporteFactura 1.8 error".print_r($sth->errorInfo(),true);
            }

            //
            // Calcular el detalle de la factura
            //

            $sth = $database->pdo->prepare("CALL pkg_facturacion.generar_renglones2(
                                :id_empresa
                                ,:id_sucursal
                                ,null
                                ,:p_conjunto_facturas)");

            $p_conjunto_facturas = "#".$nro_factura."#";
            $sth->bindParam(':id_empresa', $id_empresa, \PDO::PARAM_INT);
            $sth->bindParam(':id_sucursal', $id_sucursal, \PDO::PARAM_INT );
            $sth->bindParam(':p_conjunto_facturas',$p_conjunto_facturas , \PDO::PARAM_STR );

            if( !$sth->execute()  ){
                $logger->debug( "backend_aguas:reporteFactura 2 ( $id_empresa, $id_sucursal, $p_conjunto_facturas ) error".print_r($sth->errorInfo(),true));
                $database->pdo->rollback();
                return "backend_aguas:reporteFactura 2 error".print_r($sth->errorInfo(),true);
            }

            //
            // Consultar le lado derecho 
            //
            $sth = $database->pdo->prepare("select all 
                        id_empresa
                        ,id_sucursal
                        ,cod_iva
                        ,nro_factura
                        ,concepto concepto
                        ,capital capital
                        ,interes interes
                        ,iva iva
                        ,decode(concepto,'Créd/Desc/Bonif',iva,0) iva_credito
                        ,decode(concepto,'Créd/Desc/Bonif',0,iva) iva_debito
                        ,orden  orden 
                        ,total total
                        ,neto_int_2_vto neto_int_2_vto
                        ,iva_int_2_vto iva_int_2_vto
                        ,total_2_vto total_2_vto
                        from tmp_deuda2
                        where USUARIO =USERENV('sessionid')
                        AND  pkg_facturacion.zona_factura2(id_empresa,
                                                                                  id_sucursal,
                                                                                  cod_iva,
                                                                                  nro_factura,
                                                                                  orden,
                                                                                  USUARIO) = 'DER'
                        and de_donde ='FAC'
                        and id_empresa=:id_empresa
                        and id_sucursal=:id_sucursal
                        and nro_factura=:nro_factura
                        and cod_iva=:cod_iva
                        order by orden
                        ");
            $sth->bindParam(':id_empresa', $id_empresa, \PDO::PARAM_INT);
            $sth->bindParam(':id_sucursal', $id_sucursal, \PDO::PARAM_INT);
            $sth->bindParam(':nro_factura', $nro_factura, \PDO::PARAM_INT);
            $sth->bindParam(':cod_iva', $cod_iva, \PDO::PARAM_STR);

            if( !$sth->execute()  ){
                $logger->debug( "backend_aguas:reporteFactura 3 error".print_r($sth->errorInfo(),true));
                $database->pdo->rollback();
                return "backend_aguas:reporteFactura 3 error".print_r($sth->errorInfo(),true);
            }

            $filasDerecha = $sth->fetchAll();
            $logger->debug( "backend_aguas:reporteFactura 3,5 filasDerecha:".print_r($filasDerecha,true));
            //
            // Consultar le lado izquierdo 
            //
            $sth = $database->pdo->prepare("select all 
                        id_empresa
                        ,id_sucursal
                        ,cod_iva
                        ,nro_factura
                        ,concepto concepto
                        ,capital capital
                        ,interes interes
                        ,iva iva
                        ,decode(concepto,'Créd/Desc/Bonif',iva,0) iva_credito
                        ,decode(concepto,'Créd/Desc/Bonif',0,iva) iva_debito
                        ,orden  orden 
                        ,total total
                        ,neto_int_2_vto neto_int_2_vto
                        ,iva_int_2_vto iva_int_2_vto
                        ,total_2_vto total_2_vto
                        from tmp_deuda2
                        where USUARIO =USERENV('sessionid')
                        AND  pkg_facturacion.zona_factura2(id_empresa,
                                                                                  id_sucursal,
                                                                                  cod_iva,
                                                                                  nro_factura,
                                                                                  orden,
                                                                                  USUARIO) = 'IZQ'
                        and de_donde ='FAC'
                        and id_empresa=:id_empresa
                        and id_sucursal=:id_sucursal
                        and nro_factura=:nro_factura
                        and cod_iva=:cod_iva
                        order by orden
                        ");
            $sth->bindParam(':id_empresa', $id_empresa, \PDO::PARAM_INT);
            $sth->bindParam(':id_sucursal', $id_sucursal, \PDO::PARAM_INT);
            $sth->bindParam(':nro_factura', $nro_factura, \PDO::PARAM_INT);
            $sth->bindParam(':cod_iva', $cod_iva, \PDO::PARAM_STR);

            if( !$sth->execute()  ){
                $logger->debug( "backend_aguas:reporteFactura 4 error".print_r($sth->errorInfo(),true));
                $database->pdo->rollback();
                return "backend_aguas:reporteFactura 4 error".print_r($sth->errorInfo(),true);
            }

            $filasIzquierda = $sth->fetchAll();

            $database->pdo->rollback();
 
            // add a page
            $pdf->AddPage();

            $importe_iva = 0;

            // define barcode style
            $style = array(
                'position' => '',
                'align' => 'L',
                'stretch' => false,
                'fitwidth' => true,
                'cellfitalign' => '',
                'border' => false,
                'hpadding' => 'auto',
                'vpadding' => 'auto',
                'fgcolor' => array(0,0,0),
                'bgcolor' => false, //array(255,255,255),
                'text' => true,
                'font' => 'helvetica',
                'fontsize' => 8,
                'stretchtext' => 4
            );

            $comentario = $row["COMENTARIO1"]." ".
                            $row["COMENTARIO2"]." ".
                            $row["COMENTARIO3"];

            if(strlen($comentario)>750){
                $comentario = substr($comentario,1,750)."...";
            }
            $data =[
                    ["font-family"=>"times" , "font-size"=>14 ],
                    ["text-x"=>128 , "text-y"=>9,"text"=>utf8_encode($row["APELLIDO_NOMBRE"])],
                    
                    ($original==="N") ?
                      ["text-x"=>140 , "text-y"=>63,"text"=>"LIQUIDACION DE DEUDA"] :
                      ["text-x"=>140 , "text-y"=>63,"text"=>utf8_encode($cp_periodo_facturacion)]
                    ,

                    ["font-family"=>"times" , "font-size"=>11 ],
                    ["text-x"=>128 , "text-y"=>15,"text"=>utf8_encode($row["POSTAL_CALLE"])],
                    ["text-x"=>128 , "text-y"=>20,"text"=>utf8_encode($row["POSTAL_PISO"])],
                    ["text-x"=>128 , "text-y"=>25,"text"=>utf8_encode($row["POSTAL_DTO"])],
                    ["text-x"=>128 , "text-y"=>30,"text"=>utf8_encode($row["LOCALIDAD"])],
                    ["text-x"=>128 , "text-y"=>35,"text"=>utf8_encode($row["PROVINCIA"])],
                    ["text-x"=>128 , "text-y"=>40,"text"=>utf8_encode($row["CODIGO_POSTAL"])],
                    ["font-family"=>"times" , "font-size"=>10 ],
                    ["multi-x"=>160 , "multi-y"=>25,"multi-text"=>utf8_encode($row["IVA"]),
                    "multi-al"=>"C","muli-w"=>40,"multi-h"=>10],
                    ["multi-x"=>160 , "multi-y"=>36,"multi-text"=>utf8_encode($row["CATEGORIA"]),
                    "multi-al"=>"C","muli-w"=>40,"multi-h"=>15],
                    ($cod_iva==="MT"|| $cod_iva==="RI") ? 
                    ["multi-x"=>160 , "multi-y"=>56,"multi-text"=>utf8_encode($row["CUIT"]),
                    "multi-al"=>"C","muli-w"=>40,"multi-h"=>15]
                    : [],
                    ["font-family"=>"courier" , "font-size"=>10 ],
                    ["text-x"=>55 , "text-y"=>45,"text"=>"FACTURA:".$id_empresa."-".$cod_iva."-".$nro_factura],
                    ["font-family"=>"times" , "font-size"=>12 ],
                    ["text-x"=>40 , "text-y"=>63,"text"=>"CUENTA:".$row["CUENTA"]],
                    ["font-family"=>"times" , "font-size"=>10 ],
                    ["text-x"=>107 , "text-y"=>68,"text"=>utf8_encode("(".$row["TIPO_SERVICIO"].")".$row["SERVICIO"])],
                    ["font-family"=>"times" , "font-size"=>11 ],
                    ["text-x"=>22 , "text-y"=>74,"text"=>utf8_encode($row["INMUEBLE_CALLE"]." Nro:".$row["INMUEBLE_NRO"])],
                    ["text-x"=>22 , "text-y"=>79,"text"=>utf8_encode($row["INMUEBLE_PISO"])],
                    ["text-x"=>60 , "text-y"=>79,"text"=>utf8_encode("Sistema ".$row["TIPO_FACTURACION_DES"])],
                    ["text-x"=>22 , "text-y"=>86,"text"=>utf8_encode($row["INMUEBLE_DTO"])],
                    ["text-x"=>42 , "text-y"=>89,"text"=>utf8_encode($row["DATOS_CATASTRALES"])],
                    ["font-family"=>"times" , "font-size"=>10 ],                    
                    ["multi-x"=>3 , "multi-y"=>132,"multi-text"=>utf8_encode($row["TELEFONO"]),
                    "multi-al"=>"L","multi-w"=>80,"multi-h"=>10],

                    ($original==="S") ?
                        ["multi-x"=>3 , "multi-y"=>140,"multi-text"=>utf8_encode($comentario),
                        "multi-al"=>"L","multi-w"=>100,"multi-h"=>60] : [] ,
                    ["font-family"=>"times" , "font-size"=>12, "font-style"=>"B" ],
                    ["text-x"=>108 , "text-y"=>140,"text"=>"Importe IVA"],
                    ["text-x"=>108 , "text-y"=>144,"text"=>"Ley Nacional N° 25413"],
                    ["multi-x"=>170 , "multi-y"=>144,"multi-text"=>$row["LEY25413"],
                    "multi-al"=>"R","multi-w"=>30,"multi-h"=>10],

                    ["font-family"=>"times" , "font-size"=>8, "font-style"=>"BI" ],
                    ($cod_iva!=="MT"&& $cod_iva!=="RI") ? 
                     ["text-x"=>108 , "text-y"=>150,"text"=>"El IVA discriminado NO puede computarse como crédito fiscal"]
                    : [],
                    ["font-family"=>"times" , "font-size"=>11, "font-style"=>"BI" ],
                    ["text-x"=>150 , "text-y"=>192,"text"=>"Fecha emisión ".$row["FECHA_EMISION_TXT"]],
                ];

            if($original==="S"){
                $estado_ant = "Fecha Estado Anterior: $cp_fecha_lect_ant Est. Anterior: $cp_estado_ant";
                $estado_act = "Fecha Estado Actual: $cp_fecha_lect_act  Est. Actual: $cp_estado_act";
                $prox_vto = "Fecha Próximo Vto: $cp_prox_vto  m3 Consumo: $cp_consumo";
                $promedio = "Promedio Medidor m3: $cp_promedio";
                $cons_ba = "Consumo Bimestre Anterior: $cp_cons_per_ant";
                $cons_baa = "Consumo Bimestre Año Anterior: $cp_cons_anio_ant";


                $data = array_merge($data,[                    
                    ["font-family"=>"times" , "font-size"=>10, "font-style"=>"" ],                    
                    ["multi-x"=>5 , "multi-y"=>93,"multi-text"=>$estado_ant,
                    "multi-al"=>"L","multi-w"=>100,"multi-h"=>9],                
                    ["multi-x"=>5 , "multi-y"=>98,"multi-text"=>$estado_act,
                    "multi-al"=>"L","multi-w"=>100,"multi-h"=>9],                
                    ["multi-x"=>5 , "multi-y"=>103,"multi-text"=>$prox_vto,
                    "multi-al"=>"L","multi-w"=>100,"multi-h"=>9],                
                    ["multi-x"=>5 , "multi-y"=>108,"multi-text"=>$promedio,
                    "multi-al"=>"L","multi-w"=>100,"multi-h"=>9],                
                    ["multi-x"=>5 , "multi-y"=>113,"multi-text"=>$cons_ba,
                    "multi-al"=>"L","multi-w"=>100,"multi-h"=>9],                
                    ["multi-x"=>5 , "multi-y"=>118,"multi-text"=>$cons_baa,
                    "multi-al"=>"L","multi-w"=>100,"multi-h"=>9],                
                  ]);
            }

            $data = array_merge($data,[                    
                    ["font-family"=>"times" , "font-size"=>13 , "font-style"=>"B" ],
                    ["text-x"=>184 , "text-y"=>130,"text"=>$row["TOTAL_FAC"]],
                  ]);


            $x=0;
            $y=223; 
            $data = array_merge($data,[                    
                    ["font-family"=>"courier" , "font-size"=>10 ],
                    ["text-x"=>$x+51 , "text-y"=>$y,"text"=>"Factura ".$id_empresa."-".$cod_iva."-".$nro_factura],
                    ["font-family"=>"times" , "font-size"=>12 ],
                    ["text-x"=>$x+57 , "text-y"=>$y+5,"text"=>$row["CUENTA"]],
                    ["font-family"=>"times" , "font-size"=>11 ],
                    ["text-x"=>$x+41 , "text-y"=>$y+29,"text"=>$row["FECHA_1VTO_TXT"]],
                    ["text-x"=>$x+83 , "text-y"=>$y+29,"text"=>$row["TOTAL_1VTO"]],
                    ["bc1d-x"=>$x , "bc1d-y"=>$y+35,"bc1d-text"=>$row["COD_BARRA1_NRO"],
                      "bc1d-w"=>105 , "bc1d-h"=>20,"bc1d-r"=>0.4 ,"bc1d"=>"I25","bc1d-s"=>$style]
                  ]);

            $x=102;
            $y=223; 
            $data = array_merge($data,[                    
                    ["font-family"=>"courier" , "font-size"=>10 ],
                    ["text-x"=>$x+51 , "text-y"=>$y,"text"=>"Factura ".$id_empresa."-".$cod_iva."-".$nro_factura],
                    ["font-family"=>"times" , "font-size"=>12 ],
                    ["text-x"=>$x+57 , "text-y"=>$y+5,"text"=>$row["CUENTA"]],
                    ["font-family"=>"times" , "font-size"=>11 ],
                    ["text-x"=>$x+41 , "text-y"=>$y+29,"text"=>$row["FECHA_2VTO_TXT"]],
                    ["text-x"=>$x+83 , "text-y"=>$y+29,"text"=>$row["TOTAL_2VTO"]],
                    ["bc1d-x"=>$x , "bc1d-y"=>$y+35,"bc1d-text"=>$row["COD_BARRA2_NRO"],
                      "bc1d-w"=>105 , "bc1d-h"=>20,"bc1d-r"=>0.4 ,"bc1d"=>"I25","bc1d-s"=>$style]
                  ]);
        $iva =0;

        $x = 105;
        if($original==="N"){
            $y = 78;
            $data = array_merge($data,[
                    ["font-family"=>"times" , "font-size"=>12 ],
                    ["text-x"=>121 , "text-y"=>74,"text"=>"Concepto            Original               Interés        Iva"],
                ]);

        }
        else{
            $y = 73;
        }
        $data = array_merge($data,[                    
                    ["font-family"=>"courier" , "font-size"=>9 ],
                ]);
        foreach ($filasDerecha as $key => $fila) {
            if($original==="N"){
                $data = array_merge($data,[                    
                    ["multi-x"=>$x , "multi-y"=>$y,"multi-text"=>utf8_encode($fila["CONCEPTO"]),
                    "multi-al"=>"L","multi-w"=>40,"multi-h"=>9],                
                    ["multi-x"=>$x+40 , "multi-y"=>$y,"multi-text"=>$fila["CAPITAL"],
                    "multi-al"=>"R","multi-w"=>22,"multi-h"=>9],                
                    ["multi-x"=>$x+63 , "multi-y"=>$y,"multi-text"=>$fila["INTERES"],
                    "multi-al"=>"R","multi-w"=>15,"multi-h"=>9],                
                    ["multi-x"=>$x+79 , "multi-y"=>$y,"multi-text"=>$fila["IVA"],
                    "multi-al"=>"R","multi-w"=>15,"multi-h"=>9],                
                ]);
            }else{
                $data = array_merge($data,[ 
                    ["multi-x"=>$x , "multi-y"=>$y,"multi-text"=>utf8_encode($fila["CONCEPTO"]),
                    "multi-al"=>"L","multi-w"=>75,"multi-h"=>9],                
                    ["multi-x"=>$x+80 , "multi-y"=>$y,"multi-text"=>$fila["CAPITAL"],
                    "multi-al"=>"R","multi-w"=>22,"multi-h"=>9],                
                ]);                
            }

            $y = $y + 5;
            $iva += $fila["IVA"];
        }

        $x = 5;
        $y = 140;
        $data = array_merge($data,[                    
                    ["font-family"=>"courier" , "font-size"=>9 ],
                ]);

        foreach ($filasIzquierda as $key => $fila) {
            $data = array_merge($data,[                    
                    ["multi-x"=>$x , "multi-y"=>$y,"multi-text"=>utf8_encode($fila["CONCEPTO"]),
                    "multi-al"=>"L","multi-w"=>40,"multi-h"=>9],                
                    ["multi-x"=>$x+40 , "multi-y"=>$y,"multi-text"=>$fila["CAPITAL"],
                    "multi-al"=>"R","multi-w"=>22,"multi-h"=>9],                
                    ["multi-x"=>$x+63 , "multi-y"=>$y,"multi-text"=>$fila["INTERES"],
                    "multi-al"=>"R","multi-w"=>15,"multi-h"=>9],                
                    ["multi-x"=>$x+79 , "multi-y"=>$y,"multi-text"=>$fila["IVA"],
                    "multi-al"=>"R","multi-w"=>15,"multi-h"=>9],                
            ]);
            $y = $y + 5;
            $iva += $fila["IVA"];
        }

        $data = array_merge($data,[                    
            ["font-family"=>"times" , "font-size"=>12, "font-style"=>"B" ],
            ["multi-x"=>170 , "multi-y"=>140,"multi-text"=>$iva,
            "multi-al"=>"R","multi-w"=>30,"multi-h"=>10],
        ]);

        $pdf->print($data);

        $pdf->setImg('images/dorso_arsa.jpg' );
        // add a page
        $pdf->AddPage();

	}

        $file_name = tempnam(sys_get_temp_dir(), "download_file");

        //Close and output PDF document
        $pdf->Output($file_name, 'F');
    
        //$lineas= base64_encode(file_get_contents($file_name));

        return $file_name;
    }
}
?>
