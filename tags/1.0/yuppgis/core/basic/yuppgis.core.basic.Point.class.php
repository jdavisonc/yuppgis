<?php

/**
 * Clase que representa un punto.
 * 
 * @package yuppgis.core.basic
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class Point extends Geometry {
	
	function __construct($x = 0, $y = 0) {
		$this->addAttribute('x', Datatypes :: LONG_NUMBER);
		$this->addAttribute('y', Datatypes :: LONG_NUMBER);
		
		parent :: __construct(array ('x' => $x, 'y' => $y));
	}
	
}

?>