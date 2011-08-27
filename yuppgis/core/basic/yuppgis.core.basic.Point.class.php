<?php

class Point extends Geometry {
	
	function __construct($x = 0, $y = 0, $isSimpleInstance = false) {
		
		$this->addAttribute("x", Datatypes :: LONG_NUMBER);
		$this->addAttribute("y", Datatypes :: LONG_NUMBER);
		
		parent :: __construct(array ('x' => $x, 'y' => $y), $isSimpleInstance);
	}
	
}

?>