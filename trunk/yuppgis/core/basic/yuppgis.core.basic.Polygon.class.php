<?php

/**
 * Clase que representa un poligono.
 * 
 * @package yuppgis.core.basic
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class Polygon extends Surface {
	
	function __construct($exteriorBoundary = array(), array $interiorsBoundary = array()) {
		parent :: __construct($exteriorBoundary, $interiorsBoundary);
	}
 	
	public function preValidate() {
		parent::preValidate();
	}
	
}

?>