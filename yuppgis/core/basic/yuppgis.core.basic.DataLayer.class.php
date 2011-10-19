<?php
YuppLoader::load('yuppgis.core.basic', 'Tag');
YuppLoader::load('yuppgis.core.basic', 'Observable');

class DataLayer extends Observable {

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
		
		$this->notifyObservers(array("method" => "addElement", "object" => $element, "observable" => $this));
	}

	function removeElement($element){
		$this->removeFromElements($element);
		
		$this->notifyObservers(array("method" => "removeElement", "object" => $element, "observable" => $this));
	}

	function addTag($tag){
		$this->addToTags($tag);
		
		$this->notifyObservers(array("method" => "addTag", "object" => $tag, "observable" => $this));
	}

	function removeTag($tag){
		$this->removeFromTags($tag);
		
		$this->notifyObservers(array("method" => "removeTag", "object" => $tag, "observable" => $this));
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

	public function notifyObservers($params){		
		$string = $this->getObservers();		
		$observers = explode(";", $string);
		array_shift($observers);		
		
		foreach($observers as $observer) {
			$arr = explode("_", $observer);	
			$classname = $arr[0];
			$id = $arr[1];
			
			$obj = $classname::get($id);
			$obj->notify($this, $params);
		}

	}
}

?>