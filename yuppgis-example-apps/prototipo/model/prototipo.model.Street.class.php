<?php

YuppLoader::load( "yuppgis.core.persistent", "GISPersistentObject" );

class Street extends GISPersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {
		
		$this->setWithTable("prototipo_street");

		$this->addAttribute("name", Datatypes :: TEXT);
		$this->addAttribute("data", GISDatatypes :: LINESTRING);
		
		parent :: __construct($args, $isSimpleInstance);
	}

	public static function listAll(ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject::listAll($params);
	}
	
	public static function findBy(Condition $condition, ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject::findBy($condition, $params);
	}
	

	public static function get($id) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject :: get($id);
	}
	
}
?>