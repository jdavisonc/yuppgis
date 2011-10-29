<?php

YuppLoader::load( "yuppgis.core.persistent", "GISPersistentObject" );
YuppLoader::load( "yuppgis.core.basic", "Observer" );

class MMedico extends GISPersistentObject implements Observer {

	function __construct($args = array (), $isSimpleInstance = false) {

		$this->setWithTable("prototipo_medico");

		$this->addAttribute("nombre", Datatypes :: TEXT);
		$this->addAttribute("zonas", GISDatatypes :: MULTIPOLYGON);
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

	public static function findBy(Condition $condition, ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject::findBy($condition, $params);
	}

	public static function get($id) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject :: get($id);
	}

	public function notify($sender, $params){

		$event = $params["method"];

		$file = 'events.txt';
		 
		$event = get_class( $sender).'::'.$sender->getId().'::'.$params["method"]."\r\n";
		
		file_put_contents($file, $event, FILE_APPEND);
	}
}

?>