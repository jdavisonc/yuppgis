<?php

YuppLoader::load( "yuppgis.core.persistent", "GISPersistentObject" );

class Persona extends GISPersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {

		$this->setWithTable("prototipo2DB_persona");
		$this->addAttribute("nombre", Datatypes :: TEXT);
		$this->addAttribute("posicion", GISDatatypes :: POINT);
		
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
	
	public static function findBy(Condition $condition, ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject::findBy($condition, $params);
	}
}

?>