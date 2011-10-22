<?php
YuppLoader::load('yuppgis.core.basic', 'Tag');
YuppLoader::load('yuppgis.core.basic', 'Observable');

class DataLayer extends Observable {

	private $geoAttributes = null;
	
	/**
	 * TODO_GIS
	 * @param unknown_type $name
	 * @param unknown_type $type
	 * @param unknown_type $attributes
	 * @param unknown_type $iconurl
	 * @param unknown_type $visible
	 */
	function __construct($args = array( 'iconUrl' => '/yuppgis/yuppgis/js/gis/img/marker-gold.png', 
										'visible' => true ), 
						 $isSimpleInstance = false) {

		$this->setWithTable("data_layer");

		$this->addAttribute("name", Datatypes::TEXT);
		$this->addAttribute("classType", Datatypes::TEXT);
		$this->addAttribute("attributes", Datatypes::TEXT);
		$this->addHasMany("elements", "GISPersistentObject");
		$this->addHasMany("tags", "Tag");
		$this->addAttribute("iconUrl", Datatypes::TEXT);
		$this->addAttribute("visible", Datatypes::BOOLEAN);
		
		if ($args && array_key_exists('attributes', $args)) {
			$this->geoAttributes = $args['attributes'];
			unset($args['attributes']);
		}

		parent :: __construct($args, $isSimpleInstance);
	}
	
	public function preValidate() {
		if (!$this->geoAttributes) {
			if ($this->getClassType()) {
				$classType = $this->getClassType();
				$instance = new $classType();
				$this->geoAttributes = $instance->hasGeometryAttributes();
				$this->aSet('attributes', implode(',', $this->geoAttributes));
			}
		} else {
			$this->aSet('attributes', implode(',', $this->geoAttributes));
		}
	}
	
	function getAttributes() {
		if (!$this->geoAttributes) {
			$this->geoAttributes = explode(',', $this->aGet('attributes'));
		}
		return $this->geoAttributes;
	}
	
	function setAttributes(array $attributes) {
		$this->geoAttributes = $attributes;
	}

	function addElement($element){
		if (get_class($element) == $this->getClassType() || is_subclass_of($element, $this->getClassType())) {
			$this->addToElements($element);
			$this->notifyObservers(array("method" => "addElement", "object" => $element, "observable" => $this));
		} else {
			throw new Exception("El tipo de elemento no es correcto.");
		}
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
		
			$obj = call_user_func_array($classname.'::get',array($id));
			$obj->notify($this, $params);
		}

	}
}

?>