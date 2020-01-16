<?php	
class backend_aguas implements backend_servicio
{

    public function get_tipos_imponibles() {
    	return [];
	}

    public function registrar_usuario($datos){
        return dao_usuarios_backend::agregar($datos);
    }
}
?>
