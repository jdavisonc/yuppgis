<?php
class Polygon extends Surface {
	
	function __construct($exteriorBoundary = array(), array $interiorsBoundary = array()) {
		//validatePolygon($exteriorBoundary, $interiorsBoundary);
		parent :: __construct($exteriorBoundary, $interiorsBoundary);
	}
 	
	public function preValidate() {
		//TODO_GIS validaciones de las propiedades
		parent::preValidate();
	}
}
?>