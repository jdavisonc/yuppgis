<?php

/**
 * Clase que representa un conjunto de puntos.
 * 
 * @package yuppgis.core.basic
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class LineString extends Curve {

	function __construct( $points = array()) {
		parent :: __construct( $points );
	}
}
?>