<?php

YuppLoader::load( "yuppgis.core.persistent", "GISPersistentObject" );

class Paciente extends GISPersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {

		$this->setWithTable("prototipo_paciente");

		$this->addAttribute("nombre", Datatypes :: TEXT);
		$this->addAttribute("ubicacion", GISDatatypes :: POINT);
		$this->addAttribute("linea", GISDatatypes ::LINESTRING);



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

	public static function nameFilter($param, $layerId=null){
		$result = array ();

		$cond = Condition::ILIKE(YuppGISConventions::tableName(Paciente::getClassName()), 'nombre', '%'.$param.'%');
		$pacientes = Paciente::findBy($cond, new ArrayObject());

		if($layerId != null){
			$elements = DataLayer::get($layerId)->getElements();
			foreach ($elements as $p){
				if(in_array($p, $pacientes)){
					$result[] = $p;
				}
			}
		}else{
			$result = $pacientes;
		}

		return $result;
	}

	public static function positionFilter($param){
		//TODO Mandar y usar región
		$cond = GISCondition::ISCONTAINED(YuppGISConventions::tableName(Paciente::getClassName()),'ubicacion', new Point(10, 10));

		$pacientes = Paciente::findBy($cond, new ArrayObject());

		return $pacientes;
	}

	public function setNombreWithNotify($value){
		parent::setNombre($value);
		$this->notifyObservers(array("method" => "setNombre"));
	}

}

?>