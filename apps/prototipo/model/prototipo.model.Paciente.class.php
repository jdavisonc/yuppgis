<?php

class Paciente extends PersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {
		
		$this->setWithTable("prototipo_paciente");

		$this->addAttribute("nombre", Datatypes :: TEXT);

	 	// Restricciones
		$this->addConstraints("nombre", array(
			Constraint::nullable(false),
			Constraint::blank(false)
		));

		parent :: __construct($args, $isSimpleInstance);
	}

	public static function listAll(ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return PersistentObject :: listAll($params);
	}
	
	/*Acciones*/
		
	public static function averageAgeAction(){
		
	}
	
	public static function maleAction(){
		
	}
	
	/*Filtros*/
	
	public static function byNameFilter($param){
		
	}
	
	public static function byLastNameFilter($param){
		
	}

}

?>