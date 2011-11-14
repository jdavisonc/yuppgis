<?php

/**
 * Clase que representa una coleccion de figuras geometricas {@link Geometry}.
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class GeometryCollection extends Geometry {

	function __construct($collection = array()) {
 		$this->addHasMany('collection', Geometry::getClassName());
		parent :: __construct(array ('collection' => $collection));
	}
	
	/**
	 * TODO_GIS: Debe el tipo (puntos), dimension, greado de superposicion.
	 * @see Geometry::preValidate()
	 */
	public function preValidate() {
		parent::preValidate();
	}
	
	
}

?>