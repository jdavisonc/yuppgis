<?php

YuppLoader::load( "yuppgis.core.persistent", "GISPersistentObject" );

class Paciente extends GISPersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {

		$this->setWithTable("paciente");

		$this->addAttribute("nombre", Datatypes::TEXT);
		$this->addAttribute("apellido", Datatypes::TEXT);
		$this->addAttribute("sexo", Datatypes::TEXT);
		$this->addAttribute("fechaNacimiento", Datatypes::DATE);

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

	public static function get($id) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject :: get($id);
	}
	
}

?>