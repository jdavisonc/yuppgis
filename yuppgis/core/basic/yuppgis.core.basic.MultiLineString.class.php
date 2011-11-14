<?php

/**
 * Clase que representa un conjunto de lineas {@link LineString}.
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class MultiLineString extends MultiCurve {
	
	function __construct($collection = array()) {
		parent :: __construct($collection);
	}
	
	public function preValidate() {
		foreach ($this->getCollection() as $geom) {
			if (!$geom instanceof LineString) {
				return false;
			}
		}
		parent::preValidate();
	}
	
}

?>