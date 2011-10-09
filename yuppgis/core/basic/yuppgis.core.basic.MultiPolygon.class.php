<?php

class MultiPolygon extends MultiSurface {
	
	function __construct($collection = array()) {
		parent :: __construct($collection);
	}
	
	public function preValidate() {
		foreach ($this->getCollection() as $geom) {
			if (!$geom instanceof Polygon) {
				return false;
			}
		}
		parent::preValidate();
	}
}

?>