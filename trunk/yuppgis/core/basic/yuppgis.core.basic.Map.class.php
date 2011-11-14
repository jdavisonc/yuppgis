<?php

YuppLoader::load('yuppgis.core.basic', 'DataLayer');

/**
 * Clase que representa un Mapa.
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class Map extends PersistentObject {

	function __construct($args = array('name' => ''), $isSimpleInstance = false){
		$this->setWithTable("map");
		$this->addAttribute("name", Datatypes::TEXT);
		$this->addAttribute("visualization_json", Datatypes::TEXT);
		$this->addHasMany("layers", "DataLayer");
		
		parent :: __construct($args, $isSimpleInstance);				
	}

	function addLayer($layer){		
		$this->addToLayers($layer);
	}

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