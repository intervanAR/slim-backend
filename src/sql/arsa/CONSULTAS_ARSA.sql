select * from cobros_facturas a, facturas b 
where a.id_empresa=1 and a.id_sucursal=0 and a.id_REcaudador=30
and a.id_empresa_factura=b.id_Empresa
and a.id_sucursal_factura=b.id_sucursal
and a.cod_iva=b.cod_iva
and a.nro_factura=b.nro_factura
and b.cuenta=0400536300000


select * from facturas a , Detalles_facturas b 
where a.id_empresa=1 and a.id_sucursal=4 and a.cod_iva='CF' and a.nro_Factura between 906570 and 906576 
and a.id_empresa=b.id_empresa
and a.id_sucursal=b.id_sucursal
and a.cod_iva=b.cod_iva
and a.NRO_FACTURA=b.nro_factura

1-CF-906570 A 1-CF-906576


select * from deudas where id_empresa=1 and id_sucursal=4 and cuenta=400536300000 and id_deuda=40




FECHA    ID_OPERACION    ID_CUPON    IMPORTE    FECHA_VTO    COD_CONCEPTO    DESC_CONCEPTO    DESCRIPCION
05/06/2020 12:44:32 p.m.    6585    1-4-906567-CF    2556,56    05/06/2020            Factura 1-CF-906567
05/06/2020 12:44:32 p.m.    6585    1-4-906568-CF    355,1    05/06/2020            Factura 1-CF-906568
05/06/2020 12:44:32 p.m.    6585    1-4-906569-CF    6029,34    05/06/2020            Factura 1-CF-906569
05/06/2020 01:18:16 p.m.    6604    1-4-906570-CF    177,54    05/06/2020        	Factura 1-CF-906570
05/06/2020 01:33:36 p.m.	6613	1-4-906571-CF	88,77	05/06/2020			Factura 1-CF-906571
05/06/2020 01:37:24 p.m.	6616	1-4-906572-CF	44,4	05/06/2020			Factura 1-CF-906572
05/06/2020 01:38:57 p.m.	6618	1-4-906573-CF	22,2	05/06/2020			Factura 1-CF-906573
05/06/2020 01:40:39 p.m.	6620	1-4-906574-CF	11,09	05/06/2020			Factura 1-CF-906574
05/06/2020 01:46:23 p.m.	6621	1-4-906575-CF	5,56	05/06/2020			Factura 1-CF-906575
05/06/2020 01:49:11 p.m.	6624	1-4-906576-CF	2,75	05/06/2020			Factura 1-CF-906576
08/06/2020 09:18:27 a.m.	7108	1-4-906590-CF	1,4	08/06/2020			Factura 1-CF-906590
08/06/2020 10:02:38 a.m.	7351	1-4-901346-CF	344,25	11/06/2020			Factura Cupon1-CF-901344

select * from detalles_facturas where id_empresa=1 and id_sucursal=4 and nro_Factura=906568 and cod_iva='CF'

340.12


SELECT * FROM DEUDAS WHERE ID_EMPRESA=1 AND ID_SUCURSAL=4 AND CUENTA=400536300000 AND id_deuda=40

SELECT * FROM DEUDAS WHERE ID_EMPRESA=1 AND ID_SUCURSAL=4 AND fecha_generacion='30-mar-2020' and  pagado='N'

680.22

select * from facturas where id_empresa=1 and id_sucursal=4 and fecha_1vto='13-may-2020' --and pagado='N'77



SELECT * FROM COBROS_FACTURAS WHERE ID_EMPRESA=1 AND 400647800000


select * from cobros_facturas a, facturas b 
where a.id_empresa=1 and a.id_sucursal=0 and a.id_REcaudador=30
and a.id_empresa_factura=b.id_Empresa
and a.id_sucursal_factura=b.id_sucursal
and a.cod_iva=b.cod_iva
and a.nro_factura=b.nro_factura
--and b.cuenta=101387100200


13517832


exec dbms_output.put_line(PKG_BACKEND .ANULAR_COBRO(1,13517875));


select * from facturas where id_empresa=1 and id_sucursal=4 and cuenta= 400647800000 order by nro_Factura desc


select * from CUENTAS_CORRIENTES where  id_empresa=1 and id_sucursal=4 and cuenta= 400647800000 and nro_movimiento>=28 

SELECT * FROM DEUDAS where  id_empresa=1 and id_sucursal=4 and cuenta= 400647800000  

UPDATE DEUDAS SET PAGADO='N' where  id_empresa=1 and id_sucursal=4 and cuenta= 400647800000 AND ID_dEUDA=25  


delete from CUENTAS_CORRIENTES where  id_empresa=1 and id_sucursal=4 and cuenta= 400647800000 and nro_movimiento>=28

delete from cancelaciones where  id_empresa=1 and id_sucursal=4 and cuenta= 400647800000 and nro_movimiento>=28


select * from cancelaciones where  id_empresa=1 and id_sucursal=4 and cuenta= 400647800000 and nro_movimiento>=28

delete from notas_De_debito where id_empresa=1 and id_sucursal=4 and nro_ndd in (   387893 , 387894 , 387895 , 387896, 387897 , 387898, 387900)

delete from notas_De_debito where id_empresa=1 and id_sucursal=4 and nro_ndd in (   387893 , 387894 , 387895 , 387896, 387897 , 387898, 387899)


select * from facturas where id_empresa=1 and id_sucursal=4 and cuenta= 400647800000 and nro_factura>=906625 order by nro_Factura desc

update facturas set pagada='N' , anulada='S' where id_empresa=1 and id_sucursal=4 and cuenta= 400647800000 and nro_factura>=906625 
 
select * from facturas where id_empresa=1 and id_sucursal=4 and nro_factura=906625

select * from detalles_Facturas where id_empresa=1 and id_sucursal=4 and nro_factura=906625


select * from facturas where id_empresa=1 and id_sucursal=4 and cuenta= 400647800000 order by nro_Factura desc

select *
from DETALLES_FACTURAS
where id_empresa=1 and id_sucursal=4 and cuenta= 400647800000  
and nro_factura= 904239

904240

select *
from DETALLES_FACTURAS df--, FACTURAS f
where id_empresa=1 and id_sucursal=4 and nro_factura=904239 and cod_iva='CF'

and id_origen= 25

and nro_factura not in (904240, 904239, 904241)



select nvl(max('S'),'N') from facturas fac, detalles_facturas dfac 
where fac.id_empresa=dfac.id_empresa
and fac.id_sucursal=dfac.id_sucursal
and fac.cod_iva=dfac.cod_iva
and fac.NRO_FACTURA=dfac.nro_factura
and DFAC.id_empresa=1 and FAC.id_sucursal=4 and DFAC.NRO_Factura= 904239 and DFAC.cod_iva='CF' AND DFAC.CUOTA=0 AND DFAC.ORIGEN='DEU' AND DFAC.ID_ORIGEN=25
and  exists ( select 1 from DETALLES_FACTURAS DCUP, FACTURAS CUP,cobros_facturas cob 
WHERE dcup.id_empresa=dfac.id_empresa
and dcup.id_sucursal=dfac.id_sucursal
and dcup.cod_iva=dfac.cod_iva
and dcup.cuenta=dfac.cuenta
and dcup.CUOTA=DFAC.CUOTA
AND DCUP.ORIGEN=dfac.origen
and dcup.id_origen=dfac.id_origen
and dcup.id_empresa=cup.id_empresa
and dcup.id_sucursal=cup.id_sucursal
and dcup.cod_iva=cup.cod_iva
and dcup.nro_factura=cup.nro_factura
and pkg_facturacion.FACTURA_ORIGINAL(cup.id_empresa,
      cup.id_sucursal,
      cup.cod_iva,
      cup.nro_factura)='N'
and cup.pagada='S' 
and cob.id_empresa_Factura=cup.id_empresa
and cob.id_sucursal_factura=cup.id_sucursal
and cob.cod_iva=cup.cod_iva
and cob.nro_factura=cup.nro_factura)


select + from 

select * from DETALLES_FACTURAS DCUP, FACTURAS CUP --, COBROS_facturas cob
WHERE dcup.id_empresa=1
and dcup.id_sucursal=4
and dcup.cod_iva='CF'
and dcup.cuenta=400647800000
and dcup.CUOTA=0
AND DCUP.ORIGEN='DEU'
and dcup.id_origen=25
and dcup.id_empresa=cup.id_empresa
and dcup.id_sucursal=cup.id_sucursal
and dcup.cod_iva=cup.cod_iva
and dcup.nro_factura=cup.nro_factura
and pkg_facturacion.FACTURA_ORIGINAL(cup.id_empresa,
      cup.id_sucursal,
      cup.cod_iva,
      cup.nro_factura)='N'
and cup.pagada='S' 
and cob.id_empresa_Factura=cup.id_empresa
and cob.id_sucursal_factura=cup.id_sucursal
and cob.cod_iva=cup.cod_iva
and cob.nro_factura=cup.nro_factura 

R Table		R Columns	FK Name	Table	R Constraint	R Type	Columns
FACTURAS		ID_EMPRESA, ID_SUCURSAL, COD_IVA, NRO_FACTURA	COB_FAC_FURA_FK	COBROS_FACTURAS	FURA_PK	PK	ID_EMPRESA_FACTURA, ID_SUCURSAL_FACTURA, COD_IVA, NRO_FACTURA
	FACTURAS	ID_EMPRESA, ID_SUCURSAL, COD_IVA, NRO_FACTURA	FURA_CUPON1_FK	FACTURAS	FURA_PK	PK	ID_EMPRESA, ID_SUCURSAL, COD_IVA, NRO_FACT_CUPON1
	FACTURAS	ID_EMPRESA, ID_SUCURSAL, COD_IVA, NRO_FACTURA	FURA_CUPON2_FK	FACTURAS	FURA_PK	PK	ID_EMPRESA, ID_SUCURSAL, COD_IVA, NRO_FACT_CUPON2
	CESPS	ID	FURA_CESP_FK	FACTURAS	CESP_PK	PK	ID
	CUENTAS	ID_EMPRESA, ID_SUCURSAL, CUENTA	FURA_CUE_FK	FACTURAS	CUE_PK	PK	ID_EMPRESA, ID_SUCURSAL, CUENTA
	LOCALIDADES	COD_PROVINCIA, COD_LOCALIDAD	FURA_LOC_FK	FACTURAS	LOC_PK	PK	POSTAL_COD_PROVINCIA, POSTAL_COD_LOCALIDAD
	TIPOS_SERVICIOS	TIPO_SERVICIO	FURA_TIP_SER_FK	FACTURAS	TIP_SER_PK	PK	TIPO_SERVICIO
	SUCURSALES	ID_EMPRESA, NRO_SUCURSAL	FURA_SUC_FK	FACTURAS	SUC_PK	PK	ID_EMPRESA, ID_SUCURSAL
LIQUIDACIONES		ID_EMPRESA, ID_SUCURSAL, ID_RECAUDADOR, NRO_LIQUIDACION	COB_FAC_LIQ_FK	COBROS_FACTURAS	LIQ_PK	PK	ID_EMPRESA, ID_SUCURSAL, ID_RECAUDADOR, NRO_LIQUIDACION


select c.id_empresa,c.id_sucursal,c.cod_iva,c.cuenta,c.cuota,c.origen,c.id_origen,c.detalle,count(1) ,sum(importe_neto+importe_iva) importe 
from cobros_Facturas a, facturas b  , detalles_facturas c
where a.id_empresa=1 and a.id_sucursal=0 and a.id_REcaudador=30 
and a.id_empresa_Factura=b.id_empresa
and a.id_sucursal_Factura=b.id_sucursal
and a.cod_iva=b.cod_iva
and a.nro_factura=b.nro_factura
and pkg_facturacion.FACTURA_ORIGINAL(b.id_empresa,
      b.id_sucursal,
      b.cod_iva,
      b.nro_factura)='N'
and b.tipo_comprobante='CUP'
and b.id_empresa=c.id_Empresa
and b.id_sucursal=c.id_sucursal
and b.cod_iva=c.cod_iva
and b.nro_factura=c.nro_factura 
group by c.id_empresa,c.id_sucursal,c.cod_iva,c.cuenta,c.cuota,c.origen,c.id_origen,c.detalle
having count(1)>1


select * from cobros_facturas where id_empresa=1   and id_cobro_factura=13475704


select * from cuentas_corrientes a where origen='COB' and id_origen>='12787612' and id_origen<'10000000000000' 
and not exists( select 1 from cobros_facturas where id_cobro_factura=a.id_origen)
and cuenta = 101387100200



                    SELECT   a.id_sucursal || '-' || a.cod_iva || '-' || a.nro_factura nro_factura,
                             a.id_empresa || '-' || a.id_sucursal|| '-' || a.nro_factura ||'-'||a.cod_iva id_comprobante,
                             'Servicio' descripcion_factura,
                             CASE
                                WHEN fecha_1vto >= TRUNC (SYSDATE)
                                   THEN fecha_1vto
                                WHEN fecha_2vto >= TRUNC (SYSDATE)
                                   THEN fecha_2vto
                                ELSE fecha_1vto
                             END fecha_1vto,
                             CASE
                                WHEN fecha_1vto >= TRUNC (SYSDATE)
                                   THEN importe_1vto + ley25413 + iva_1vto
                                WHEN fecha_2vto >= TRUNC (SYSDATE)
                                   THEN importe_2vto + ley25413_2 + iva_2vto
                                ELSE importe_1vto + ley25413 + iva_1vto
                             END importe_1vto,
                             a.tipo_servicio tipo, 
                             b.descripcion desc_tipo_servicio,
                             b.descripcion impuesto,
                             to_char(fecha_1vto,'yyyy') anio,
                             case when pagada='S' 
                                then 'Pagada'
                                when (select count(1) from deudas
                                        where id_empresa=c.id_empresa
                                          and id_sucursal=c.id_sucursal
                                          and cuenta=c.cuenta
                                          and id_deuda=c.id_origen
                                          and id_deuda=c.id_origen
                                          and pagado='S' ) > 0                                    
                                then 'Pagada'
                                else 'Impaga' 
                             end desc_estado,
                             CASE
                                WHEN pagada = 'N' AND fecha_2vto >= TRUNC (SYSDATE)
                                   THEN 'S'
                                ELSE 'N'
                             END pagar
                        FROM facturas a, tipos_servicios b, detalles_facturas c
                       WHERE a.tipo_servicio = b.tipo_servicio
                         AND a.id_empresa=c.id_empresa
                         and a.id_sucursal=c.id_sucursal
                         and a.cod_iva=c.cod_iva
                         and a.nro_factura=c.nro_factura
                         AND pkg_facturacion.factura_original (a.id_empresa,
                                                               a.id_sucursal,
                                                               a.cod_iva,
                                                               a.nro_factura
                                                              ) = 'S'
                         AND a.id_empresa=1
                         AND a.id_sucursal=1
                         AND a.cuenta=101387100200
                    ORDER BY fecha_1vto 
                    
                    
 

select * from deudas