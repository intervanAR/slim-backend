bk_empresas

declare 
     l_id_empresa number := 30631557364;
     l_nombre varchar2(1000);
     familia number :=1000000;
     l_id_comprobante number := 1;
     l_vto date;
begin
    for idx in 1..100  loop
        insert into bk_cuentas(id_empresa,id_cuenta,tipo_cuenta,nro_cuenta,descripcion ) 
        values (l_id_empresa, familia , 'SOC', familia , 'Grupo Familiar '||familia);
        --
        for familiar in 1..4 loop
            if( mod(familiar,4) = 1 )then
                l_nombre := 'Esposa '||familia;
            elsif( mod(familiar,4) = 2 )then 
                l_nombre := 'Esposo '||familia;
            elsif( mod(familiar,4) = 3 )then 
                l_nombre := 'Hija '||familia;
            else             
                l_nombre := 'Hijo '||familia;
            end if;
            insert into bk_personas ( id_empresa , id_persona ,nombre,tipo_documento,nro_documento )
                values(l_id_empresa,familia+familiar , l_nombre,1,familia+familiar );
            insert into bk_personas_cuentas(id_empresa,id_persona,id_cuenta) 
            values( l_id_empresa,familia+familiar,familia );    
        end loop;
        --
        l_vto = to_DAte('15/01/2017','dd/mm/yyyy');
        for deuda in 1..30 loop
            --
            insert into bk_Deudas(ID_EMPRESA, ID_COMPROBANTE, ID_CUENTA, IMPORTE, FECHA_VTO, COD_CONCEPTO, DESC_CONCEPTO, DESCRIPCION, ESTADO) values
            (l_id_empresa , l_id_comprobante,familia ,ROUND(DBMS_RANDOM.VALUE(5,9))*100 , l_Vto , 'CTA','Cuota Soccial','Cuota Social',null)
            l_vto = add_months(l_vto,1);
            l_id_comprobante=l_id_comprobante+1; 
        end loop;
        familia := familia+10;   
    end loop;
end;

