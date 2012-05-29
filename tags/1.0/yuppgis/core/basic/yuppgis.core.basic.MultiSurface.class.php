<?php

/**
 * Clase que representa un conjunto de superficies {@link Surface}.
 * 
 * @package yuppgis.core.basic
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
abstract class MultiSurface extends GeometryCollection {
	
	function __construct($collection = array()) {
		parent :: __construct($collection);
	}
	
	public function preValidate() {
		parent::preValidate();
	}
	
}

?>