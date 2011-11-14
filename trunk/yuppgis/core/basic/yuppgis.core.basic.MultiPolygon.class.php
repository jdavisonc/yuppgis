<?php

/**
 * Clase que representa un conjunto de poligonos {@link Polygon}.
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class MultiPolygon extends MultiSurface {
	
	function __construct($collection = array()) {
		parent :: __construct($collection);
	}
	
	public function preValidate() {
		foreach ($this->getCollection() as $geom) {
			if (!$geom instanceof Polygon) {
				return false;
			}
		}
		parent::preValidate();
	}
	
}

?>