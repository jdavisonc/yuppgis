<?php

/**
 * Clase que representa una curva.
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
abstract class Curve extends Geometry {
	
	function __construct( $points ) {
		$this->addHasMany('points', 'Point');
		parent :: __construct(array ('points' => $points));
	}
	
}
?>