# Slim-backend
Maqueta de Backend para usar la Oficina Virtual. Este backend es llamado por el midleware de Oficina Virtual.

## Características
Instala una maqueta con un servidor completo de requerimientos del midleware de Oficina Virtual. Tiene el código para conectarse a los siguientes sistemas

* Gestionar : Sistemas de Ingresos y Gastos desarrollado por Intervan
* Creditos  : Sistenemas de Gestión de Créditos desarrollado por Intervan
* Aguas     : SIstemas de Gestión de Créditos desarrollado por Intervan
* Demo      : Esquema de tablas simple de ejemplo de desarrollo de esta maqueta

El objetivo de la maqueta es independizar y abstraer los accesos de la Oficina Virtual al sistema subyacente  de manera que en en ésta, se puedan establecer reglas de negocio propias de cada instalación.

## Requerimientos
* PHP 7 o superior 
* composer
* MySQL si desea instalar el esquema Demo
* Apache 
* Swagger para ver documentación de servicios

## Instalación
```bash
git co https://github.com/intervanAR/slim-backend.git

composer install

```

Si instala el esquema Demo, deberá crear las tablas en la base de datos con el siguiente script de composer

```bash

composer crear-tablas

```

### Configuración

Copiar el archivo instalacion\settings-template.php como instalacion\settings.php y configurar la conexión a la base de datos y el sistema con el que trabaja la maqueta.

Copiar el archivo instalacion\backend-template.conf como instalacion\backend.conf y configurar correctamente el directorio de apache donde se encontrará el backend.

Luego incluir este archivo en el archivo httpd.conf de apache de la siguiente manera

```
Include "C:/proyectos/slim-backend/instalacion/backend.conf"
```

## Documentación de Servicios
Para la documentación de los servicios, parámetros y testing, se debe utilizar la herramienta swagger

### Instalación de Swagger


Crear en el directorio /path/to/your/project/public/web/ el archivo swagger.html con el siguiente código:

```html
<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@3.12.1/swagger-ui.css" >
    <link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16" />
    <style>
      html
      {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
      }
      *,
      *:before,
      *:after
      {
        box-sizing: inherit;
      }

      body
      {
        margin:0;
        background: #fafafa;
      }
    </style>
  </head>

  <body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@3.12.1/swagger-ui-standalone-preset.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@3.12.1/swagger-ui-bundle.js"></script>
    <script>
    window.onload = function() {
      // Begin Swagger UI call region
        console.log(window.location.pathname);
      const ui = SwaggerUIBundle({
        url: window.location.protocol + "//" + window.location.hostname + "/backend/api_docs",
        dom_id: '#swagger-ui',
        deepLinking: true,
          presets: [
              SwaggerUIBundle.presets.apis,
              SwaggerUIStandalonePreset
          ],
          layout: "StandaloneLayout"
      })
      // End Swagger UI call region
      window.ui = ui
    }
  </script>
  </body>
</html>
```



## Pasos para implementar un nuevo conector

Para implementar un nuevo conector se debe:
1. [Implementar](#implementar-nuevo-conector) una clase que implemente la interfase src\Conectores\backend_servicio.
2. [Modificar](#modificar-container) el container,  src\dependencies.php para que se cree una instancia del nuevo conector.
3. [Modificar](#modificar-configuración) archivo de configuracion settings.php indicando la conexión a la base de datos y el nuevo sistema.

### Implementar Nuevo Conector
Para esto será necesario crear una nueva clase dentro del directorio src\Conectores que implemente la interfase backend_servicio.

Primero se deberá tener en cuenta que sercicios se quiere implementar. 

Si desea implementar asociación automática de cuentas a usuarios deberá implementar la función `get_cuentas($filtro)`.

Si quiere usar el servicio de asociación de cuentas por parte del usuario, deberá implementar la función `buscar_cuenta($filtro)`.

Si quiere poder consultar deuda, actualizar la deuda e imprimir un reporte de deuda actualizada, deberpa implementar las funciones  `consulta_deuda($parametros)`, `resumen_pago($parametros)`,`resumen_pago($parametros)` y `get_reporte_factura($parametros)`.

Si quiere agregar pagos, deberá también implementar `crear_operacion_pago($comprobantes)`,
`anular_operacion_pago($id_operacion )`.
    
Para acceder a los datos, utiliza el framework *[Medoo][1]*. Aunque es posible reemplazarlo por otro ORM.


Los modelos se representan en directorios dentro de la carpeta src/Modelos/*nuevo_sistema*/, heredando de la clase src/Modelos/ModeloBase.php e implementando el método source

Ejemplo: modelo para acceder a la tabla FACTURAS
```php 
class Facturas extends \Backend\Modelos\ModeloBase
{
	public function getSource(){
		return 'FACTURAS_CLIENTE';
	}
} 
```

Para acceder al conteiner donde tendremos los objetos entre otros, la base y el logger, podemos accederlo a traves de la clase SlimBackend::Backend().

Por ejemplo en un conector si queremos acceder a una clase que representa el modelo para hacer una consulta o tener acceso al logger, lo hacemos de la siguiente manera

```php
    public function consulta_deuda($parametros){
        
        ...

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
                   "FACTURAS_CLIENTE.TIPO_FACTURA",
                   "FACTURAS_CLIENTE.NRO_FACTURA"];

        $condicion["FACTURAS_CLIENTE.ESTADO"]=["CON","PAR"];
        $condicion["CLIENTES.CUIT"]=$nro_cuit;
        $condicion["ORDER"]=["PERSONAS.NOMBRE"=>"ASC",
                             "CLIENTES.NRO_CUENTA"=>"ASC",
                             "FACTURAS_CLIENTE.ID_FACTURA"=>"ASC",
                             "FACTURAS_CLIENTE.FECHA_VENCIMIENTO"=>"DESC"];

    	$datos = $consulta->selectj($join,$campos,$condicion);
    
    	// Acceso al logger.
        $consulta->logger->debug('backend_erp:consulta_deuda'.print_r($consulta->db->log(),true));

        ...
    
```


[1]: https://medoo.in/

### Modificar Container

Agregar en el archivo src\dependencies.php la construcción del nuevo conector de backend NUEVO_BACKEND:

```php
	$container['sistema'] = function ($c){
		$setting = $c->get('settings')['sistema'];
        if ($setting === 'gestionar') {
            $backend = new \Backend\Conectores\backend_gestionar();
		} elseif ($setting === 'aguas') {
            $backend = new \Backend\Conectores\backend_aguas();
        } elseif ($setting === 'creditos') {
            $backend = new \Backend\Conectores\backend_creditos();
        } elseif ($setting === 'erp') {
            $backend = new \Backend\Conectores\backend_erp();
        } elseif ($setting === 'NUEVO_BACKEND') {
            $backend = new \Backend\Conectores\CLASE_NUEVO_BACKEND();
        } else {
            throw new Exception("El parámatro sistema=$sistema definido en instalacion/settings:.php es incorrecto");
		}
		return $backend;
	};	
```

### Modificar Configuración
En el archivo instalacion\settings.php establecer en la sección sistema el nombre NUEVO_BACKEND

```php
				'authentication' =>["basic" =>[ 
										"path"=>["/test","/api_docs","/*"],
										"ignore"=>["/test"],
										"realm"=>"BackendRealm",
										"secure" => false,
										"users" => [
												"usuario" =>"password",
											]											
										] /* ,
									"firebase" =>[ 
										"path"=>"/servicios",
										"projectId"=>"dinahuapi-intervan",
										"keyCache"=>__DIR__.'/cache',
										"timeoutCache"=>3600,					
										]*/ ],
				"sistema" => "NUEVO_BACKEND"
```

### Funciones a implementar en un conector nuevo

Listado de funciones y parámetros de la interfase servicios_backend
Los datos numéricos tienen el formato  nnnnn.dd , sin separador de miles y el . como separador decimal
Las  fechas tienen el formato YYYY-MM-DD

**get_cuentas( $parametros ) **

servicios/cuentas_x_usuario
Servicio que se invoca para tratar de asociar por primera vez o cuentas nuevas. 
Acá se debe aplicar reglas de negocio si permite asociar cuentas por dni, por mail. el tipo y nro de documento es lo que carga el usuario en la OV y no está verificado.

$parametros =  ["mail":"ddirazar@gmail.com" siempre se envía, cuando ingresa, se registra o se actualiza el perfil del usuario    
                "tipo_documento":"1"    opcional, se manda cuando se actualiza en el perfil   
                "nro_documento":"20000000"  opcional, se manda cuando se actualiza en el perfil  
              ]

retorna: Arreglo de cuentas que se pueden asociar automaticamente   
    [         
        ["tipo_objeto": "TCR1",    Es el tipo de cuenta      
        "id_objeto": "1253",       Es el nro de cuenta
       "id_cuenta": "1189"      "Es el identificador interno      
         ],      
        [  ...      ],     

        ...   
    ]


**get_cuentas_x_objetos($parametros);**

servicios/cuentas_x_objetos

Servicio que retorna los datos de las cuentas. se utiliza para desplegar las cuentas cuando en la OV se despliega la opción CUENTAS. La colección de cuentas que retorna este servicio será la que se utiliza para los demás servicios de deudas o facturas.

$parametros[ 
       ["tipo_objeto":"TCR1",    Tipo de cuenta    
        "id_objeto":"1253",         Nro de cuenta    
         "alias_cuenta": ""],         Alias asignada por el usuario en la Ov   
        [ ...],   
        ...
    ]

Retorno  [  0: [ alias_cuenta": "",   El alias enviada en el parametro
                "id_cuenta": "1189",   identificadr de cuenta interno
                "tipo_cuenta": "TCR1",   Tipo de cuenta
                "nro_cuenta": "1253",   nro  de cuenta
                "desc_tipo_cuenta": "Partida",     Descripción de tipo de cuenta
                "descripcion": "Partida 1253 MESSI ALEJO",   Descripción de la cuenta
                "responsable_pago": "MESSI ALEJO",    Responsable de pago
                "id_persona": "852",         Identificador interno del responsable de pago
                "enviar_mail": "N",          N:  Impresion en papel    S: Boleta sin Papel
                 "pa_activo": "N",             Pago/debito automatico activo  
               "pa_fecha_desde": "",       Fecha de inicio de pago/debito automatico 
              "pa_fecha_hasta": "",       Fecha in de pago/debito automatico
                "id_nro_cuenta": "1189"   Nro de cuenta si fuera distinta
                ],  
          1: [....],
          ...] 

 
**consulta_deuda($parametros);**

servicios/consulta_deuda Consulta la deuda separada en deuda o proximos vencimientos, de las cuentas pasadas como argumentos.

$parametros:    [  tipoDeuda":"todo",       "nro_documento":"20173258381",        "mail":"ddirazar@gmail.com",       "cuentas":[           0: [ "alias_cuenta": "",   El alias enviada en el parametro
                "id_cuenta": "1189",   identificadr de cuenta interno
                "tipo_cuenta": "TCR1",   Tipo de cuenta
                "nro_cuenta": "1253",   nro  de cuenta
                "desc_tipo_cuenta": "Partida",     Descripción de tipo de cuenta
                "descripcion": "Partida 1253 MESSI ALEJO",   Descripción de la cuenta
                "responsable_pago": "MESSI ALEJO",    Responsable de pago
                "id_persona": "852",         Identificador interno del responsable de pago
                "enviar_mail": "N",          N:  Impresion en papel    S: Boleta sin Papel
                 "pa_activo": "N",             Pago/debito automatico activo  
                "pa_fecha_desde": "",       Fecha de inicio de pago/debito automatico 
                "pa_fecha_hasta": "",       Fecha in de pago/debito automatico
                "id_nro_cuenta": "1189"   Nro de cuenta si fuera distinta
               ],          1: [....],
            ...        ]   ]

Retorna:[ "deuda":[  [          "id_cuenta":"1748",                 identificador interno de la cuenta          
                  "cont_id":"2037",                     identificador de contribiyente          
                  "cont_desc1":"ARTIGAU NICOLAS RAUL",     descripcion de contribuyente          
                  "cont_desc2":"CUIT-CUIL 20333877288",         descripcion 2 de contribuyente            
                  "cue_id":"1748",                       identificador de la cuenta               
                  "cue_desc1":"Partida 1831",    descripcion de la cuenta            
                  "cue_desc2":"JULIO A. ROCA N\u00b0 176",      descripcion 2 de la cuenta         
                  "imp_id":"1",                             impuesto/concepto         
                  "imp_desc1":"SERVICIOS P\u00daBLICOS",     descripcion impuesto/conpceto           
                  "per_id":"1#2019",                 identificador de periodo       
                  "per_desc1":"Deuda 2019",    descripcion de periodo              
                  "deu_id":"862081",                 identificador ÚNICO de la deuda  * ver nota           
                  "deu_desc1":"0\/3 V:26\/04\/19",        descripcion de la deuda           
                  "deu_vto":"2019-04-26 00:00:00",      vto de la deuda           
                  "deu_capital":"1250",               capital original de la deuda             
                  "deu_recargo":"972.18",          importe de intereses. El capital + recaro es el total de lo que se debe a hoy            
                  "id_factura":""],                        identificador del comprobante original o de liquidación           ....         ], 
               "prox":[...]]
**NOTA, si los comprobantes se duplican entre las cuentas, construir un comprobante que sea la concatenación unica de la cuenta y el comprobante.**




**resumen_pago($id_comprobantes, $fecha_actualizacion);**
servicios/resumen_pago Este servicio se encarga de realizar de calcular los comprobantes de pago de una o varias cuentas.
parámetros$id_comprobantes : [ [ "id":"1282210" ] , identificador único de la deuda, corresponde con el campo 
                        "deu_id" de consulta de deuda.                                    
                        ["id":"1282206"] , ...                                ],
                        $fecha_actualizacion: Fecha que el usuario quisiera abonar en formato YYYY-MM-DD


Respuesta[  "rta": "OK",         campo con ok o algún mensaje de error  
             "comprobantesFact": "#1285583#1285584#1285585#", Cadena de comprobantes generados separados por ##        "comprobantes": [         
                    [ "id_comprobante": "1285583",             identificador de comprobante de pago generado 
                       "total": "2139.27",                                Total de ese comprobante
                       "fecha_vto": "2022-11-16 00:00:00",   Fecha de vto de ese comprobante
                       "cod_concepto": "8",                           codigo de concepto
                       "desc_concepto": "prueba 2",             Descripcion de concepto
                       "descripcion": "F.Nro:512165 Partida 1253: 1-2-3\/Conv.3176 " descripción del comprobante. va al gateway de pago como detalle para que se muestre en el cupón de pago                   
                      ],                                                 
                    [ "id_comprobante": "1285584",                                                         "total": "1017.33",                                                          "fecha_vto": "2022-11-16 00:00:00",                                                           "cod_concepto": "1",                                                           "desc_concepto": "SERVICIOS P\u00daBLICOS",                                                            "descripcion": "F.Nro:512166 Partida 1253: 6\/2019 1\/2020 "       ]                                      
                    [...]      ,"total":4750.65,   importe total de todos los comprobantes      
                    "max_fecha_vto":"2022-11-16"       Fecha de vto de todos los comprobantes ( tomar el menor de todos) ]


**crear_operacion_pago($parametros);**
/servicios/crear_operacion_pago
Este servicio se utiliza para registrar los cobros de los comprobantes generados con el servicio anterior. El servicio debería registrar un cobro por cada comprobante que se paga ( ej se pueden hacer un pago con tarjeta de varias cuentas)

$parametros: [ "medio_pago":"1", Siempre envía 1 ya que hay un gateway de pago genérico.
              "comprobantes":[ ["id_operacion":"13208",  operacin de la OV, en la que se pagó este comprobante
                                  "cupon_pago":"1285583", se corresponde con el comprobante
                                  "id_comprobante":"1285583",     Los restantes campos son los devueltos en el servicio anterior                                                      "importe":"2139.27",  "fecha_vto":"2022-11-16 00:00:00",
                                  "cod_concepto":"8",                                                       
                                  "desc_concepto":"prueba 2",                                                      "descripcion":"F.Nro:512165 Partida 1253: 1-2-3\/Conv.3176 "
                                  ],                                                       
                                ["id_operacion":"13208",                                                         
                                "id_comprobante":"1285584",
                                "cupon_pago":"1285584",
                                "importe":"1017.33", 
                                "fecha_vto":"2022-11-16 00:00:00",
                                cod_concepto":"1","desc_concepto":"SERVICIOS P\u00daBLICOS",                           "descripcion":"F.Nro:512166 Partida 1253: 6\/2019 1\/2020 "
                                ]                                                         
                                [...]               
                                ,"fecha_pago":"2022-11-16",  FEcha en la que efectivamente se confirmó el pago( ej en DEBIN/EFECTIVO )               
                                "version":2,   Valor fijo 2                
                                "gp_notif":[...] Depende del gateway de pago. Es un arreglo.  ]  

Respuesta[  "rta": "OK",     OK o mensaje de error   
             "id_operacion_pago": [         listado de cobros generados en el sistema               
              [ "nro_cobro": "27847",         cobro generado en el sistema                 
                "cupon_pago": "1285583",             es el cupon enviado                  
                "resultado": "OK"                es el resultado de la oepración de registración de ese cobro,
                ],                 
              [...]      
             ]    
        ]

**confirmar_operacion_pago($parametros);**
en estos casos debe retornar siempre 
Respuesta ["rta":"OK"]

**anular_operacion_pago($parametros)en estos casos debe retornar siempre **
Respuesta ["rta":"OK"]

**get_facturas($parametros);**
/servicios/consulta_facturas
Este servicio retorna el listado de comprobantes originales/de liquidación que se podrian pagar. Se utiliza en muchos casos para pago anual o pago de saldo anual
$parametros:[ "tipo" : "facturas" ,  puede ser facturas , pago_anual,todo                       
              "tipo_documento": Tipo de documento del usuario"                       
              "nro_documento": Nro de documento del usuario",                       
              "mail": Mail del usuario,                                  
              "cuentas":[  
                  0: [ "alias_cuenta": "",   El alias enviada en el parametro
                        "id_cuenta": "1189",   identificadr de cuenta interno
                        "tipo_cuenta": "TCR1",   Tipo de cuenta
                         "nro_cuenta": "1253",   nro  de cuenta
                                 "desc_tipo_cuenta": "Partida",     Descripción de tipo de cuenta
                                 "descripcion": "Partida 1253 MESSI ALEJO",   Descripción de la cuenta
                                 "responsable_pago": "MESSI ALEJO",    Responsable de pago
                                 "id_persona": "852",         Identificador interno del responsable de pago
                                 "enviar_mail": "N",          N:  Impresion en papel    S: Boleta sin Papel
                                 "pa_activo": "N",             Pago/debito automatico activo  
                                 "pa_fecha_desde": "",       Fecha de inicio de pago/debito automatico 
                                 "pa_fecha_hasta": "",       Fecha in de pago/debito automatico
                                 "id_nro_cuenta": "1189"   Nro de cuenta si fuera distinta
                              ], 
                       1: [....],
                        ...                  
                ]         
            ]
Retorna[   [  "nro_factura":"387088",  Nro de factura a mostrar     
                "id_comprobante":"850986",     Comprobante que se enviará si lo selecciona y da pagar o imprimir
                "descripcion_factura":"1\/2018",        Descripción de la factura      
                "fecha_1vto":"2018-03-01 00:00:00",   Vto de la factura      
                "importe_1vto":"272.78",                     importe 1er vto      
                "importe_2vto":"288.02",                     Importe 2do vto      
                "tipo":"1",        Tipo             
                "desc_tipo":"Tasa\/Impuesto",  Descripción del Tipo      
                "desc_estado":"Pagada",  Descripción del Estad      
                "estado":"1",   Codigo  del estado      
                "anio":"2018",  Año al que corresponde la factura ( Para usar en filtro)       
                "impuesto":"SERVICIOS P\u00daBLICOS",   impuesto de la factura        
                "pagar":"N",   Indica si permite pagar o no ( Ej. vencidas, a veces no se pueden pagar)       
                "tipo_fac":"facturas",  El tipo que se indicó en el requerimiento
                "cuenta":["alias_cuenta":"",               Datos de la cuenta ( que viajó en el requerimiento )                "id_cuenta":"1189",                
                            "tipo_cuenta":"TCR1",               
                            "nro_cuenta":"1253",                
                            "desc_tipo_cuenta":"Partida",                
                            "descripcion":"Partida 1253  XXXXX",                
                             "responsable_pago":"MESSI  ALEJO",                
                            "id_persona":"852",                
                            "enviar_mail":"N",                
                            "pa_activo":"N",               
                            "pa_fecha_desde":"",               
                            "pa_fecha_hasta":"",               
                            "id_nro_cuenta":"1189"            
                             ]      
            ],   
          [ ... ]
        ]

**get_reporte_factura($parametros)**

$Parametros:
   ["id_comprobante":"1282286"]  Identificador del comprobante retornado del servicio get_facturas

Retorna: nombre_archivo  :  nombre de un archivo temporal pdf que se enviará a la OV.
                            El archivo a continuación se elimina