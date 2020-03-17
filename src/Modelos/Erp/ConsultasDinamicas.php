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

		$rta = array();
		$l_filas = 0;

		$pdo= $this->db->pdo;
		$stmt = $pdo->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
		$stmt->execute();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT)) {
		  // Si es la 1er fila => creo el grupo
			foreach ($row as $key => $value) {
					if( isset($row[$key]) && is_resource($row[$key])) {
							$row[$key] = stream_get_contents($row[$key]);
					}
			}	
			$rta[]=$row;
			$l_filas++;
		}
		return $rta;	
	}
}