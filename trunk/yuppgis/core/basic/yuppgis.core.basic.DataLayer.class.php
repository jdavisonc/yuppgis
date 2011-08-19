<?php

class DataLayer extends GISPersistentObject {

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $id
	 * @param unknown_type $name
	 * @param unknown_type $indexAttribute
	 */
	function __construct($id,$name, $indexAttribute='id'){		
		
		$this->setWithTable("data_layer");
		
		$this->addAttribute("name", Datatypes::TEXT);		
		$this->addAttribute("indexAttribute", Datatypes::TEXT);
		
		$this->addHasMany("elements",  "GISPersistentObject");		
		$this->addHasMany("tags", Datatypes::TEXT);
		
		$this->setElements(array());
	}	

	function addElement($element){		
		$this->addToElements($element);
	}
	
	function removeElement($id){
		$this->removeFromElements(array($id));
	}
}

?>