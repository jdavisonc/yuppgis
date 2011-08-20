<?php

class Point extends Geometry {
	
	function __construct($args = array (), $isSimpleInstance = false) {
		
		$this->addAttribute("x", Datatypes :: LONG_NUMBER);
		$this->addAttribute("y", Datatypes :: LONG_NUMBER);
		
		parent :: __construct($args, $isSimpleInstance);
	}
	
}

?>