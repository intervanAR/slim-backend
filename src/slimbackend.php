<?php
/**
 * Clase que brinda las puertas de acceso al núcleo Rest de Gestion
 */

namespace Backend;



class SlimBackend
{	
    public static $App=null;


    public static function setBackend($theApp){
        self::$App = $theApp;
    }


    public static function Backend(){
        return self::$App->getcontainer();
    }
  
	private function __construct()
	{
    }

    public static function getEmpresaByHash( $hash_key_id ){       

        $consulta = new Modelos\Clubonline\Empresas( self::$App->getcontainer() );

        $condicion=array( "HASH_ID"=> $hash_key_id );

        $campos = ["ID_EMPRESA"];
 
        $datos =  $consulta->select($campos,$condicion);
        self::$App->getcontainer()->logger->debug('getEmpresaByHash'.print_r(self::$App->getcontainer()->db->log(),true));        
        
        if( sizeof($datos)<1) return -1;
        return $datos[0]["ID_EMPRESA"];
   }

    public static function getParametros( $id_empresa,$parametro,$perms_desde,$perms_hasta,$indexed){
        $consulta = new Modelos\Clubonline\Parametros( self::$App->getcontainer() );

        $condicion["ID_EMPRESA"] = $id_empresa;

        if (strpos($parametro,"%")!==false) { 
            $condicion["CODIGO[~]"] = $parametro;
        }else{
            $condicion["CODIGO"] = $parametro;
        }

        if(!$indexed){
            $campos=["ID_EMPRESA(id_empresa)","CODIGO(codigo)","VALOR(valor)","OBSERVACIONES(observaciones)"];
        }else{
            $campos=["CODIGO" => ["ID_EMPRESA(id_empresa)","CODIGO(codigo)","VALOR(valor)","OBSERVACIONES(observaciones)"]];
        }
        $datos =  $consulta->select($campos,$condicion);

        self::$App->getcontainer()->logger->debug('getParametros'.print_r(self::$App->getcontainer()->db->log(),true));        

        return $datos;
    }

}
?>