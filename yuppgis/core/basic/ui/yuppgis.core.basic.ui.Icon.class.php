<?php

class Icon extends UIProperty {
	
	function __construct($url = '', $height = 10, $width = 10) {
		
		$this->addAttribute('url', Datatypes::TEXT);
		$this->addAttribute('height', Datatypes::INT_NUMBER);
		$this->addAttribute('width', Datatypes::INT_NUMBER);
		
		parent :: __construct(array ('url' => $urk, 'height' => $height, 'width' => $width));
	}
	
}

?>