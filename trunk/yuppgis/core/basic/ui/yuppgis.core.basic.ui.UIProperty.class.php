<?php

class UIProperty {
	
	function __construct($alpha = 0, $zIndex = 0) {
		
		$this->addAttribute('alpha', Datatypes::INT_NUMBER);
		$this->addAttribute('zIndex', Datatypes::LONG_NUMBER);
		
		parent :: __construct(array ('alpha' => $alpha, 'zIndex' => $zIndex));
	}
	
}

?>