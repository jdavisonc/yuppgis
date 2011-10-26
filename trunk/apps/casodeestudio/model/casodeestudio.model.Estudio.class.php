<?php

YuppLoader::load( "yuppgis.core.persistent", "GISPersistentObject" );

class Estudio extends GISPersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {

		$this->setWithTable("estudio");

		$this->addAttribute("codigo", Datatypes::TEXT);
		$this->addAttribute("nombre", Datatypes::TEXT);
		$this->addAttribute("fecha", Datatypes::DATETIME);
		$this->addAttribute("resultado", Datatypes::TEXT);
		
		$this->addConstraints("codigo", array(
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