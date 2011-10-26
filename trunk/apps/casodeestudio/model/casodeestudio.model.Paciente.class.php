<?php

YuppLoader::load( "yuppgis.core.persistent", "GISPersistentObject" );

class Paciente extends GISPersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {

		$this->setWithTable("paciente");

		// Datos Personales
		$this->addAttribute("nombre", Datatypes::TEXT);
		$this->addAttribute("apellido", Datatypes::TEXT);
		$this->addAttribute("sexo", Datatypes::TEXT);
		$this->addAttribute("fechaNacimiento", Datatypes::DATE);
		$this->addAttribute("fechaFallecimiento", Datatypes::DATE);
		$this->addAttribute("telefono", Datatypes::TEXT);
		$this->addAttribute("email", Datatypes::TEXT);
		
		// Identificador - Cedula de Identidad
		$this->addAttribute("ci", Datatypes::TEXT);
		
		// Ubicacion
		$this->addAttribute("direccion", Datatypes::TEXT);
		$this->addAttribute("barrio", Datatypes::TEXT);
		$this->addAttribute("ciudad", Datatypes::TEXT);
		$this->addAttribute("departamento", Datatypes::TEXT);
		$this->addAttribute("ubicacion", GISDatatypes::POINT);

		$this->addConstraints("ci", array(
			Constraint::nullable(false),
			Constraint::blank(false)
		));
		$this->addConstraints("email", array(
			Constraint::email()
		));
		$this->addConstraints("sexo", array(
			Constraint::inList(array('M', 'F'))
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