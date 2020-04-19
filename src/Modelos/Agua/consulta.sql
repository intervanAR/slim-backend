select fac.id_empresa 
    ,fac.id_sucursal
    ,cue.cuenta
    ,fac.nro_factura
    ,fac.cod_iva
    ,fac.comentario1
    ,fac.comentario2
    ,fac.comentario3
    ,fac.fecha_1vto
    ,fac.fecha_2vto
    ,fac.ley25413
    ,fac.ley25413_2
    ,fac.fecha_2vto
    ,anulada
    ,fecha_emision
    ,PKG_COD_BARRAS.CODIFICA_2_DE_5(fac.cod_barra) COD_BARRA
    ,FAC.COD_BARRA COD_BARRA_NRO
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
    'UF:'||catastro_uf ||' .' datos_catastrales
from facturas fac
  ,cuentas cue
  ,calles cal_inm
  --,calles cal_pos
  ,localidades loc
  ,provincias pro
  ,tipos_iva iva
  ,tipos_servicios ts
  ,tipos_facturacion tf
  --,personas per
where  fac.id_empresa   = :p_id_empresa
and    fac.id_sucursal  = :p_id_sucursal
and    cue.cuenta       = fac.cuenta
and    cue.id_empresa   = fac.id_empresa
and    cue.id_sucursal  = fac.id_sucursal
and   cue.inmueble_cod_calle = cal_inm.cod_calle
--and   cue.postal_cod_calle = cal_pos.cod_calle
and   loc.cod_localidad = fac.postal_cod_localidad
and   pro.cod_provincia = fac.postal_cod_provincia
and   iva.cod_iva = fac.cod_iva
and   iva.fecha_vigencia IN(     SELECT MAX(fecha_vigencia)
                                                         FROM TIPOS_IVA TI
                                                         WHERE TI.COD_IVA = fac.COD_IVA
                                                    )
and   fac.tipo_servicio = ts.tipo_servicio
and   cue.tipo_facturacion = tf.tipo_facturacion
--and   cue.id_persona = per.id_persona



&p_and_fac &p_conjunto_factura


----------------------DETALLE ------------------------
select all 
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
order by orden


  pkg_facturacion.generar_renglones2(:p_id_empresa
  ,:p_id_sucursal
  ,null--p_cod_iva
  ,:p_conjunto_factura); 
  
  
select all 
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
order by orden



select all id_empresa 
,id_sucursal
,cod_iva
,nro_factura
,concepto concepto1
,capital capital1
,interes interes1
,iva iva2
,decode(concepto,'Créd/Desc/Bonif',iva,0) iva_credito2
,decode(concepto,'Créd/Desc/Bonif',0,iva) iva_debito2
,orden  orden1
,total total1
,neto_int_2_vto neto_int_2_vto1
,iva_int_2_vto iva_int_2_vto1
,total_2_vto total_2_vto1

from tmp_deuda2
where USUARIO =USERENV('sessionid')
AND  pkg_facturacion.zona_factura2(id_empresa,
                                                          id_sucursal,
                                                          cod_iva,
                                                          nro_factura,
                                                          orden,
                                                          USUARIO)  = 'IZQ'
and de_donde ='FAC'
order by orden