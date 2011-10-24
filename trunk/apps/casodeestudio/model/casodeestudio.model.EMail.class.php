<?php

YuppLoader::load( "yuppgis.core.persistent", "GISPersistentObject" );

class EMail extends GISPersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {

		$this->setWithTable("email");

		$this->addAttribute("email", Datatypes::TEXT);
		$this->addAttribute("tipo", Datatypes::TEXT);

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