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


    public static function backend(){
        return self::$App->getcontainer();
    }
  
	private function __construct()
	{
    }

}
?>