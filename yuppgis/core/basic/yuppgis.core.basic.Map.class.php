<?php

YuppLoader::load('yuppgis.core.basic', 'Layer');

class Map  extends PersistentObject {

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
		
		$this->setWithTable("map");
		
		$this->addAttribute("name", Datatypes::TEXT);
		$this->addHasMany("layers", "Layer");
		
		$args = array('id'=> $id, 'name' =>$name, 'layers' => array());
		parent :: __construct($args, false);				
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