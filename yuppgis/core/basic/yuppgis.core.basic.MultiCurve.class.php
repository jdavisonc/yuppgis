<?php

abstract class MultiSurface extends GeometryCollection {
	
	function __construct($collection = array()) {
		parent :: __construct($collection);
	}
	
	public function preValidate() {
		parent::preValidate();
	}
}

?>