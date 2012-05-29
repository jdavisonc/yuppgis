<?php

/**
 * Clase que representa una linea con dos puntos.
 * 
 * @package yuppgis.core.basic
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class Line extends LineString {
	
	function __construct( $points ) {
		parent :: __construct( $points);
	}
	
	private function validatePoints( $points ) {
		if ( count($points) != 2 ) {
			throw new Exception("El tipo geografico Linea solo puede tener dos puntos");
		}
		return true;
	}
}

?>