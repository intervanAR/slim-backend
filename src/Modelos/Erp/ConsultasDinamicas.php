<?php

namespace Backend\Modelos\Erp;


class ConsultasDinamicas extends \Backend\Modelos\ModeloBase
{
	public function getSource(){
		return 'GE_CONSULTAS';
	}

	public function ejecutar( $reporte,$parametros) {
		$this->container->logger->debug('ConsultasDinamicas reporte'.$reporte);
		$consultas = $this->select ("QUERY",array("REPORTE"=>$reporte));
		$this->container->logger->debug('ConsultasDinamicas'.print_r($this->container->db->log(),true));		
		$query = stream_get_contents($consultas[0]);	

		// Reemplazar Parámetros
		if(isset($parametros)){
			foreach ($parametros as $parametro => $valor) {
				$query= str_replace("[".$parametro."]", $valor, $query);
			}
		}
		$this->container->logger->debug('ConsultasDinamicas.ejecutar:'.$query);
		$datos = $this->db->pdo->query($query)->fetchAll();
 
		return $datos;	
	}
}