DECLARE
  C_ID_EMPRESA NUMBER := 30000000001;
  c_dni number := 20000000;
  l_rta varchar2(1024);
  tiene_cta number;
  b_cuenta boolean;
  l_dni number;
  l_id_persona number;
  l_id_cuenta varchar2(200);
  l_id_comprobante number:=0;
  descripcion varchar2(200);
  l_cod_concepto varchar2(100);
  l_desc_concepto varchar2(100);
  desc_comercio varchar2(1024);
  n_com number;
BEGIN
    --
    --
    FOR PER IN 1..100 LOOP
        l_id_persona := c_dni+PER;
        iNSERT INTO BK_PERSONAS (ID_EMPRESA, ID_PERSONA, NOMBRE, MAIL, TIPO_DOCUMENTO, NRO_DOCUMENTO )  
        VALUES (  C_ID_EMPRESA,l_id_persona,'Persona '||per , 'persona_'||per||'@gmail.com',1,l_id_persona);
        b_cuenta := false;
        for com in 1..4 loop
            if com=1 then
                desc_comercio := 'Panaderia San Carlos';
                descripcion := 'Panificados';
            elsif com= 2 then
                desc_comercio := 'Pinturería Del Centro';
                descripcion := 'Pinturas';
            elsif com= 3 then           
                desc_comercio := 'Ferretería Luisito';
                descripcion := 'Herramientas';
            else
                desc_comercio := 'Gomería Central';
                descripcion := 'Servicio de reparación';
            end if;
            --
            tiene_cta := round(dbms_random.value(0,1));
            --
            l_id_cuenta := com||'-'||l_id_persona;
            if tiene_cta=1 then
                --dbms_output.put_line('Persona '|| per||' tiene cuenta en comercio :'||com||' '||tiene_cta);
                insert into bk_cuentas(ID_EMPRESA, ID_CUENTA, TIPO_CUENTA, NRO_CUENTA, DESCRIPCION1, DESCRIPCION2, DESCRIPCION3 )
                values( c_id_empresa,l_id_cuenta,1,l_id_persona,desc_comercio ||' '|| 'Persona '||per,null,null);  
                --
                insert into bk_personas_cuentas(ID_EMPRESA, ID_PERSONA, ID_CUENTA)
                values( c_id_empresa ,l_id_persona, l_id_cuenta );            
                b_cuenta:=true;
                --
                -- Popular Deudas
                --
                for meses in 1..20 loop
                    -- Meto deudas aleatorias
                    if( round(dbms_random.value(0,1)) = 1 )then
                        l_id_comprobante := l_id_comprobante+1;
                        l_cod_concepto:=1;
                        l_desc_concepto:= 'Ticket';
                        insert into bk_deudas(ID_EMPRESA, ID_COMPROBANTE, ID_CUENTA, IMPORTE, FECHA_VTO, COD_CONCEPTO, DESC_CONCEPTO, DESCRIPCION)
                        values( c_id_empresa, l_id_comprobante,l_id_cuenta,round(dbms_random.value(1,1500),2 ), add_months(trunc(sysdate),-meses),
                        l_Cod_concepto,l_Desc_concepto, descripcion||' Vto:'||to_char(add_months(trunc(sysdate),-meses),'dd/mm/YY'));
                    end if;
                end loop;
            end if;             
        end loop;
        if( not b_cuenta) then
            --
            n_com := round(dbms_random.value(1,4));
            --
            if n_com=1 then
                desc_comercio := 'Panaderia San Carlos';
            elsif n_com= 2 then
                desc_comercio := 'Pinturería Del Centro';
            elsif n_com= 3 then           
                desc_comercio := 'Ferretería Luisito';
            else
                desc_comercio := 'Gomería Central';
            end if;
            --
            l_id_cuenta := n_com||'-'||l_id_persona;            
            --
            --dbms_output.put_line('Persona '|| per||' tiene cuenta en comercio :'||com||' '||tiene_cta);
            insert into bk_cuentas(ID_EMPRESA, ID_CUENTA, TIPO_CUENTA, NRO_CUENTA, DESCRIPCION1, DESCRIPCION2, DESCRIPCION3 )
            values( c_id_empresa,l_id_cuenta,1,l_id_persona,desc_comercio ||' '|| 'Persona '||per,null,null);  
            --
            insert into bk_personas_cuentas(ID_EMPRESA, ID_PERSONA, ID_CUENTA)
            values( c_id_empresa ,l_id_persona, l_id_cuenta );            
            --
            for meses in 1..20 loop
                -- Meto deudas aleatorias
                if( round(dbms_random.value(0,1)) = 1 )then
                    l_id_comprobante := l_id_comprobante+1;
                    l_cod_concepto:=1;
                    l_desc_concepto:= 'Ticket';
                    insert into bk_deudas(ID_EMPRESA, ID_COMPROBANTE, ID_CUENTA, IMPORTE, FECHA_VTO, COD_CONCEPTO, DESC_CONCEPTO, DESCRIPCION)
                    values( c_id_empresa, l_id_comprobante,l_id_cuenta,round(dbms_random.value(1,1500),2 ), add_months(trunc(sysdate),-meses),
                    l_Cod_concepto,l_Desc_concepto, descripcion||' Vto:'||to_char(add_months(trunc(sysdate),-meses),'dd/mm/YY'));
                end if;
            end loop;
            --
        end if;
    END LOOP;
    /*
    for com in 1..4 loop
        for i in 1..100 loop
            dbms_output.put_line(i);
        end loop;
    loop;
   */
END;

bk_parametros 

select * from bk_empresas

30000000001


update bk_cuentas set nro_cuenta=id_cuenta  where id_empresa=30000000001 

update bk_deudas  set descripcion = descripcion|| ' Vto:'|| to_char(fecha_vto,'dd/mm/yy') where id_empresa=30000000001 

select * from bk_cuentas where id_empresa=30000000001 and id_cuenta like '%-20000001'

select * from bk_personas_cuentas where id_empresa=30000000001 and id_persona=20000001

select * from bk_personas a , bk_personas_cuentas b
where a.tipo_documento=1
and a.nro_documento=20000001
and a.id_empresa=30000000001 
and a.id_Empresa=b.id_empresa
and a.id_persona=b.id_persona



select * from bk_deudas where id_empresa=30000000001 

SELECT "BK_CUENTAS"."ID_EMPRESA","BK_CUENTAS"."NRO_CUENTA","BK_CUENTAS"."TIPO_CUENTA" FROM "BK_PERSONAS" INNER JOIN "BK_PERSONAS_CUENTAS" ON "BK_PERSONAS"."ID_PERSONA" = "BK_PERSONAS_CUENTAS"."ID_PERSONA" AND "BK_PERSONAS"."ID_EMPRESA" = "BK_PERSONAS_CUENTAS"."ID_EMPRESA" INNER JOIN "BK_CUENTAS" ON "BK_PERSONAS_CUENTAS"."ID_EMPRESA" = "BK_CUENTAS"."ID_EMPRESA" AND "BK_PERSONAS_CUENTAS"."ID_CUENTA" = "BK_CUENTAS"."ID_CUENTA" WHERE "BK_CUENTAS"."ID_EMPRESA" = '30000000001' AND "BK_PERSONAS"."TIPO_DOCUMENTO" = '1' AND "BK_PERSONAS"."NRO_DOCUMENTO" = '20000001'

s