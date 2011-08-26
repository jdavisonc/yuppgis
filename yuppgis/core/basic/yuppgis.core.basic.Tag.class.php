<?php

class Tag extends GISPersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {
		
		$this->addAttribute("nombre", Datatypes :: TEXT);		
		$this->addAttribute("color", Datatypes :: TEXT);
		
		parent :: __construct($args, $isSimpleInstance);
						
	}	
}

?>