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

