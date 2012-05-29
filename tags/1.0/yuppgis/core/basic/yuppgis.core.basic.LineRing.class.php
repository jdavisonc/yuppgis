<?php

/**
 * Clase que representa un anillo.
 * 
 * @package yuppgis.core.basic
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class LineRing extends LineString {
	
	function __construct( $points = array()) {
		parent :: __construct( $points );
	}
	
}

?>