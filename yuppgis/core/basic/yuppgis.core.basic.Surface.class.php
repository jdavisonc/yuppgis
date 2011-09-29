<?php
 abstract class Surface extends Geometry {
 	
 	 	
 	function __construct(Curve $exteriorBoundary, array $interiorsBoundary = array()) {
		$this->addHasOne('exteriorBoundary', Curve::getClassName());
 		$this->addHasMany('interiorsBoundary', Curve::getClassName());
		parent :: __construct(array ('exteriorBoundary' => $exteriorBoundary, 'interiorsBoundary' => $interiorsBoundary));
	}
 	
	
 }