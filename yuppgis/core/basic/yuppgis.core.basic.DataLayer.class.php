<?php
YuppLoader::load('yuppgis.core.basic', 'Tag');

class DataLayer extends PersistentObject {

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $id
	 * @param unknown_type $name
	 * @param unknown_type $indexAttribute
	 */
	
	function __construct($name = '', $indexAttribute='id', $iconurl='/yuppgis/yuppgis/js/gis/img/marker-gold.png', $visible=true){		
		
		$this->setWithTable("data_layer");
		
		$this->addAttribute("name", Datatypes::TEXT);		
		$this->addAttribute("indexAttribute", Datatypes::TEXT);
		
		$this->addHasMany("elements", "GISPersistentObject");		
		$this->addHasMany("tags", "Tag");
		$this->addAttribute("iconurl", Datatypes::TEXT);
		$this->addAttribute("visible", Datatypes::BOOLEAN);
		
		$args = array('name' =>$name, 'indexAttribute' => $indexAttribute, 'iconurl' => $iconurl, 'visible' => $visible );
		parent :: __construct($args, false);
	}	

	function addElement($element){		
		$this->addToElements($element);
	}
	
	function removeElement($element){
		$this->removeFromElements($element);
	}
	
	public static function listAll(ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return PersistentObject::listAll($params);
	}
	
	public static function findBy(Condition $condition, ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return PersistentObject::findBy($condition, $params);
	}
	
	public static function get($id) {
		self :: $thisClass = __CLASS__;
		return PersistentObject :: get($id);
	}
}

?>