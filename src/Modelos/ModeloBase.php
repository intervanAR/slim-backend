<?php

namespace Backend\Modelos;

abstract class ModeloBase{
	protected $container;

	public function __construct( $container ){
		$this->container = $container;
	}
	public function  __get($property){
		if($this->container->{$property})
			return $this->container->{$property};
	}

	abstract public function getSource();


	public function selectj($joins,$columns,$where=[]){
		return $this->container->db->select($this->getSource(),$joins,$columns,$where);
	}

	public function select ( $columns,$where=[] ){
		return $this->container->db->select($this->getSource(),$columns,$where);
	}

	public function insert ( $data=[]){
		return $this->container->db->insert($this->getSource(),$data);
	}

	public function update ( $data=[] ,$where=[] ){
		if( empty($where))
			return 0;

		return $this->container->db->update($this->getSource(),$data,$where );
	}

	public function delete ( $where=[] ){
		if( empty($where))
			return 0;

		return $this->container->db->delete($this->getSource(),$where );
	}

}