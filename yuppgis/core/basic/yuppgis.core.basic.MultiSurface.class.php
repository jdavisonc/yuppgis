<?php

abstract class MultiCurve extends GeometryCollection {
	
	function __construct($collection = array()) {
		parent :: __construct($collection);
	}
	
	public function preValidate() {
		parent::preValidate();
	}
}

?>