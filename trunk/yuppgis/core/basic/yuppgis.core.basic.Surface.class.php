<?php

/**
 * Clase que representa una superficie.
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
abstract class Surface extends Geometry {

	function __construct($exteriorBoundary = array(), array $interiorsBoundary = array()) {
		$this->addHasOne('exteriorBoundary', Curve::getClassName());
		$this->addHasMany('interiorsBoundary', Curve::getClassName());
		parent :: __construct(array ('exteriorBoundary' => $exteriorBoundary, 'interiorsBoundary' => $interiorsBoundary));
	}

	public function preValidate() {
		parent::preValidate();
	}

}

?>