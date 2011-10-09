<?php

class MultiLineString extends MultiCurve {
	
	function __construct($collection = array()) {
		parent :: __construct($collection);
	}
	
	public function preValidate() {
		foreach ($this->getCollection() as $geom) {
			if (!$geom instanceof LineString) {
				return false;
			}
		}
		parent::preValidate();
	}
}

?>