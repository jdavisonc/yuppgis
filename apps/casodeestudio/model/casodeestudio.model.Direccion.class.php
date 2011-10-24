<?php

YuppLoader::load( "yuppgis.core.persistent", "GISPersistentObject" );

class Direccion extends GISPersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {

		$this->setWithTable("direccion");

		$this->addAttribute("direccion", Datatypes::TEXT);
		$this->addAttribute("tipo", Datatypes::TEXT);
		$this->addAttribute("telefono", Datatypes::TEXT);
		$this->addAttribute("barrio", Datatypes::TEXT);
		$this->addAttribute("ciudad", Datatypes::TEXT);
		$this->addAttribute("departamento", Datatypes::TEXT);

		$this->addConstraints("direccion", array(
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

	public static function get($id) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject :: get($id);
	}
	
}

?>