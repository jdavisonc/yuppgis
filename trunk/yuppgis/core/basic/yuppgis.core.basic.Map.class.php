<?php

class Map  extends GISPersistentObject {

	private  $id;
	private  $name;
	private  $layers;

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $id
	 * @param unknown_type $name
	 * @param unknown_type $indexAttribute
	 */
	function __construct($id,$name){
		$this->id=isset($id) ? uniqid() :$id;
		$this->name = isset($name) ? "".$this->id :$name;
		$this->layers = array();
				
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $element
	 */
	function addLayer($layer){	
		$this->$layers[$layer->getId()] = $layer;
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $key
	 */
	function removeElement($key){
		if ( array_key_exists($key, $this->$layers) ){
			unset($this->$layers[$key]);
		}
	}

	/**
	 * 
	 * Enter description here ...
	 */
	function getLayers(){
		return $this->$layers;
	}
	
	function toKML(){
		//TODO_GIS
	
	}
}

?>