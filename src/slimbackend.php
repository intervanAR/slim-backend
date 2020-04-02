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

        $consulta = new \Backend\Modelo\Empresas( self::$App->getcontainer() );

        $condicion=array( "HASH_ID"=> $hash_key_id );

        $campos = ["ID_EMPRESA"];
 
        $datos =  $consulta->select($campos,$condicion);
        self::$App->getcontainer()->logger->debug('getEmpresaByHash'.print_r(self::$App->getcontainer()->db->log(),true));        
        
        if( sizeof($datos)<1) return -1;
        return $datos[0]["ID_EMPRESA"];
   }



    public static function getEmpresa( $request  ){
        if( $request->getAttribute('token') === null ) return -1;

        $consulta = new \Backend\Modelo\Usuarios( self::$App->getcontainer() );

        $condicion=array( "ID_USUARIO"=> $request->getAttribute('token')["user_id"]);

        $campos = ["ID_EMPRESA"];
 
        $datos =  $consulta->select($campos,$condicion);

        if( sizeof($datos)<1) return -1;
        return $datos[0]["ID_EMPRESA"];
    }

    public static function truncate_data($id_empresa,$entity  ){
        
        $cuentas_personas=false;
        $deudas=false;
        if( strcmp("personas", $entity)==0 ){
            $consulta = new Modelos\Personas( self::$App->getcontainer() );
            $cuentas_personas = true;                
        }elseif ( strcmp("cuentas", $entity)==0 ){
            # code...
            $consulta = new Modelos\Cuentas( self::$App->getcontainer() );
            $cuentas_personas = true;
            $deudas = true;                
        }elseif ( strcmp("cuentas_personas", $entity)==0 ){
            # code...
            $consulta = new Modelos\CuentasPersonas( self::$App->getcontainer() );
        }elseif ( strcmp("deudas", $entity)==0 ){
            # code...
            $consulta = new Modelos\Deudas( self::$App->getcontainer() );
        }elseif ( strcmp("referencias", $entity)==0 ){
            # code...
            $consulta = new Modelos\Referencias( self::$App->getcontainer() );
        }elseif ( strcmp("parametros", $entity)==0 ){
            # code...
            $consulta = new Modelos\Parametros( self::$App->getcontainer() );
        }
        if( $deudas ){
            $c_deudas = new Modelos\Deudas( self::$App->getcontainer() );
            $c_deudas->delete(["ID_EMPRESA"=>$id_empresa]);
            if( $c_deudas->error() ){
                return "Error truncado deudas dep.".print_r($c_cpers->getDb()->error(),true);
            }
        }

        if( $cuentas_personas ){
            $c_cpers = new Modelos\PersonasCuentas( self::$App->getcontainer() );
            $c_cpers->delete(["ID_EMPRESA"=>$id_empresa]);
            if( $c_cpers->error() ){
                return "Error truncado personas_cuentas dep.".print_r($c_cpers->getDb()->error(),true);
            }
        }

        $consulta->delete(["ID_EMPRESA"=>$id_empresa]);
        if( $consulta->error() ){
            return "Error truncado personas_cuentas dep.".print_r($consulta->getDb()->error(),true);
        }
        return"OK";
    }

    public static function import_data($id_empresa,$entity,$filename,$campos,$options,$delimiter  ){
        $logger = self::$App->getcontainer()->logger;

        $merge = ( strcmp('M', $options)==0 );

        /* Recorrer los campos */        
        $refs=[];
        $idx=0;
        foreach ($campos as $key => $campo) {
                # code...
            if(sizeof($campo)>0){
                $refs[$campo]=$idx;
            }
            $idx++;
        }    

        $logger->debug("id_empresa: $id_empresa ,entity= $entity ,filename: $filename ,options= $options ,delimiter= $delimiter ");

        $logger->debug("refs:".print_r($refs,true));


        if( strcmp("personas", $entity)==0 ){
            $consulta = new Modelos\Personas( self::$App->getcontainer() );
            // 
            $pk = array(["ID_PERSONA"=>$refs["ID_PERSONA"]] );
        }elseif ( strcmp("cuentas", $entity)==0 ){
            # code...
            $consulta = new Modelos\Cuentas( self::$App->getcontainer() );
        }elseif ( strcmp("cuentas_personas", $entity)==0 ){
            # code...
            $consulta = new Modelos\CuentasPersonas( self::$App->getcontainer() );
        }elseif ( strcmp("deudas", $entity)==0 ){
            # code...
            $consulta = new Modelos\Deudas( self::$App->getcontainer() );
        }elseif ( strcmp("referencias", $entity)==0 ){
            # code...
            $consulta = new Modelos\Referencias( self::$App->getcontainer() );
        }elseif ( strcmp("parametros", $entity)==0 ){
            # code...
            $consulta = new Modelos\Parametros( self::$App->getcontainer() );
        }

        $inserts = 0;
        if (($gestor = fopen($filename, "r")) !== FALSE) {
            while (($datos = fgetcsv($gestor,0, $delimiter)) !== FALSE) {
                $logger->debug("datos:".print_r($datos,true));

                $fila = ["ID_EMPRESA"=>$id_empresa];
                foreach ($refs as $campo => $idx) {
                        # code...
                        $fila[$campo] = $datos[$idx];
                }    
                $logger->debug("fila:".print_r($fila,true));
                
                $consulta->insert($fila);
                $logger->debug('getEmpresaByHash'.print_r($consulta->getDB()->log(),true));
                if( $consulta->error() )
                        return ["resultado"=>"Error insertando fila",
                                 "mensaje"=>"Error insertando fila".print_r($consulta->getDB()->error(),true),
                                 "inserts" =>$inserts]; 

                $inserts++;

            }
            fclose($gestor);
        }
        return ["resultado"=>"OK",
                             "mensaje"=>"",
                             "inserts" =>$inserts];

    }


}
?>