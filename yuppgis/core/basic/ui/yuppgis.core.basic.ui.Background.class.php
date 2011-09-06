<?php

class Background extends UIProperty {
	
	function __construct($color = '') {
		
		$this->addAttribute('color', Datatypes::TEXT);
		
		parent :: __construct(array ('color' => $color));
	}
	
}

?>