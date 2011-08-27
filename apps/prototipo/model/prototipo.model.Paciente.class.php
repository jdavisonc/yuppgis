<?php 

YuppLoader::load( "yuppgis.core.persistent",   "GISPersistentObject" );

class Paciente extends GISPersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {
		
		$this->setWithTable("prototipo_paciente");

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
		return GISPersistentObject :: listAll($params);
	}
	
	/*Acciones*/
		
	public static function averageAgeAction(){
		
	}
	
	public static function maleAction(){
		
	}

	public static function get($id) {
		self :: $thisClass = __CLASS__;
		return GISPersistentObject :: get($id);
	}
	
	/*Filtros*/
	
	public static function byNameFilter($param){
		
	}
	
	public static function byLastNameFilter($param){
		
	}

}

?>