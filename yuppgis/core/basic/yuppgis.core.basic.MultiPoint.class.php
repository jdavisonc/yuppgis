<?php

class MultiPoint extends GeometryCollection {
	
	function __construct($collection = array()) {
		parent :: __construct($collection);
	}
	
	public function preValidate() {
		foreach ($this->getCollection() as $geom) {
			if (!$geom instanceof Point) {
				return false;
			}
		}
		parent::preValidate();
	}
}

?>