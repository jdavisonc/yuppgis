<?php

class Border extends UIProperty {
	
	function __construct($color = '', $width = 10) {
		
		$this->addAttribute('color', Datatypes::TEXT);
		$this->addAttribute('width', Datatypes::INT_NUMBER);
		
		parent :: __construct(array ('color' => $color, 'width' => $width));
	}
	
}

?>