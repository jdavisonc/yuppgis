<?php

class GeometryCollection extends Geometry {
	
	//protected $collection; // array de coleciones geograficas

	function __construct($collection = array()) {
 		$this->addHasMany('collection', Geometry::getClassName());
		parent :: __construct(array ('collection' => $collection));
	}
	
	public function preValidate() {
		//TODO_GIS validaciones de las propiedades
		//Cada sub clase debe validar el tipo (puntos), dimension, greado de superposicion
		parent::preValidate();
	}
	
	
}

?>