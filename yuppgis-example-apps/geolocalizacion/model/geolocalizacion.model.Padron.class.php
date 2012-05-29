<?php

YuppLoader::load( "yuppgis.core.persistent", "GISPersistentObject" );

class Padron extends GISPersistentObject {
	
	function __construct($args = array (), $isSimpleInstance = false) {

		$this->setWithTable("padron");

		$this->addAttribute("numero", Datatypes::LONG_NUMBER);
		$this->addAttribute("calle", Datatypes::TEXT);
		$this->addAttribute("padron", Datatypes::TEXT);
		$this->addAttribute("ubicacion", GISDatatypes::POINT);

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