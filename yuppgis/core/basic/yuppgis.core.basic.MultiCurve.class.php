<?php

/**
 * Clase que representa un conjunto de curvas {@link Curve}.
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
abstract class MultiCurve extends GeometryCollection {
	
	function __construct($collection = array()) {
		parent :: __construct($collection);
	}
	
	public function preValidate() {
		parent::preValidate();
	}
	
}

?>