
--
-- ov_ADHESIONES  (Table) 
--


CREATE TABLE OV_ADHESIONES
(
  ID_EMPRESA                NUMBER(8)           DEFAULT 1,
  ID_SUCURSAL               NUMBER(4),
  CUENTA                    NUMBER(15),
  TIPO_ADHESION  VARCHAR2(240 BYTE),
  FECHA_DESDE    DATE                           NOT NULL,
  FECHA_HASTA    DATE,
  USUARIO_ALTA   VARCHAR2(240 BYTE),
  USUARIO_BAJA   VARCHAR2(240 BYTE),
  DATOS          CLOB
)
LOGGING 
NOCOMPRESS 
NOCACHE
NOPARALLEL
MONITORING;


--
-- ADH_PK  (Index) 
--
CREATE UNIQUE INDEX ADH_PK ON OV_ADHESIONES
(ID_EMPRESA, ID_SUCURSAL,CUENTA, TIPO_ADHESION, FECHA_DESDE)
LOGGING
NOPARALLEL;


-- 
-- Foreign Key Constraints for Table OV_ADHESIONES 
-- 
ALTER TABLE OV_ADHESIONES ADD (
  FOREIGN KEY (ID_EMPRESA, ID_SUCURSAL, CUENTA) 
 REFERENCES CUENTAS (ID_EMPRESA,ID_SUCURSAL,CUENTA));

insert into cg_Ref_codes( rv_domain,rv_low_Value,rv_meaning) values( 'TIPO_ADHESIONES','1','Factura sin Papel');

insert into cg_Ref_codes( rv_domain,rv_low_Value,rv_meaning) values( 'TIPO_ADHESIONES','2','Débito Automático');

commit;

