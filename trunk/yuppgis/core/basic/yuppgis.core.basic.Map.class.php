<?php

YuppLoader::load('yuppgis.core.basic', 'DataLayer');

class Map  extends PersistentObject {

	function __construct($args = array('name' => ''), $isSimpleInstance = false){
		
		$this->setWithTable("map");
		$this->addAttribute("name", Datatypes::TEXT);
		$this->addAttribute("visualization_json", Datatypes::TEXT);
		$this->addHasMany("layers", "DataLayer");
		
		parent :: __construct($args, false);				
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $element
	 */
	function addLayer($layer){		
		$this->addToLayers($layer);
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $key
	 */
	function removeLayer($layer){
		$this->removeFromLayers($layer);
	}

	public static function listAll(ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return PersistentObject::listAll($params);
	}
	
	public static function findBy(Condition $condition, ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return PersistentObject::findBy($condition, $params);
	}
	
	public static function get($id) {
		self :: $thisClass = __CLASS__;
		return PersistentObject :: get($id);
	}
}

?>