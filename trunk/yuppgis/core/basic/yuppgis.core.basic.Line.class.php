<?php

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