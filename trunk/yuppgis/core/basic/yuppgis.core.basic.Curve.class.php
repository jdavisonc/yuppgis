<?php

abstract class Curve extends Geometry {
	
	function __construct( $points ) {
		
		$this->addHasMany('points', 'Point');
		
		parent :: __construct(array ('points' => $points));
	}
	
}
?>