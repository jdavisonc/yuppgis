<?php
YuppLoader::load('yuppgis.core.basic', 'Tag');

class DataLayer extends GISPersistentObject {

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $id
	 * @param unknown_type $name
	 * @param unknown_type $indexAttribute
	 */
	
	function __construct($name = '', $indexAttribute='id'){		
		
		$this->setWithTable("data_layer");
		
		$this->addAttribute("name", Datatypes::TEXT);		
		$this->addAttribute("indexAttribute", Datatypes::TEXT);
		
		$this->addHasMany("elements", "GISPersistentObject");		
		$this->addHasMany("tags", "Tag");
		
		$args = array('name' =>$name, 'indexAttribute' => $indexAttribute);
		parent :: __construct($args, false);
	}	

	function addElement($element){		
		$this->addToElements($element);
	}
	
	function removeElement($id){
		$this->removeFromElements(array($id));
	}
}

?>