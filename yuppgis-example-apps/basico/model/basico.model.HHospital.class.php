<?php

YuppLoader::load( 'yuppgis.core.persistent', 'GISPersistentObject' );

class HHospital extends GISPersistentObject {
	
	function __construct($args = array (), $isSimpleInstance = false) {
		
		$this->setWithTable("basico_hospital");

		$this->addAttribute("nombre", Datatypes :: TEXT);
		$this->addAttribute("ubicacion", GISDatatypes :: POINT);
		
		// Restricciones
		$this->addConstraints("nombre", array(
			Constraint::nullable(false),
			Constraint::blank(false)
		));

		parent :: __construct($args, $isSimpleInstance);
	}
	
	public static function listAll(ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject::listAll($params);
	}
	
	public static function get($id) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject :: get($id);
	}
	
}

?>