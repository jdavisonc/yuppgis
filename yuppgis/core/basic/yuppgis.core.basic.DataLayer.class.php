<?php

YuppLoader::load('yuppgis.core.basic', 'Tag');
YuppLoader::load('yuppgis.core.basic', 'Observable');
YuppLoader::load('yuppgis.core.basic.ui', 'UIProperty');

/**
 * Capa de datos que contendra elementos para luego ser mostrados en un mapa.
 * 
 * @author Jorge Davison
 * @author German Schnyder 
 * @author Emilia Rosa
 * @author Martin Taruselli
 */
class DataLayer extends Observable {

	private $geoAttributes = null;
	private $uiPropertyObject = null;

	function __construct($args = array( 'visible' => true ), $isSimpleInstance = false) {

		$this->setWithTable("data_layer");

		$this->addAttribute("name", Datatypes::TEXT);
		$this->addAttribute("classType", Datatypes::TEXT);
		$this->addAttribute("attributes", Datatypes::TEXT);
		$this->addAttribute("defaultUIProperty", Datatypes::TEXT);
		$this->addAttribute("visible", Datatypes::BOOLEAN);
		$this->addHasMany("elements", "GISPersistentObject");
		$this->addHasMany("tags", "Tag");
		
		if ($args && !is_string($args) && array_key_exists('attributes', $args)) {
			$this->geoAttributes = $args['attributes'];
			unset($args['attributes']);
		}

		parent :: __construct($args, $isSimpleInstance);
	}
	
	public function getDefaultUIProperty() {
		if ($this->uiPropertyObject == null && $this->aGet('defaultUIProperty')) {
			$this->uiPropertyObject = UIProperty::fromJSON($this->aGet('defaultUIProperty'));
		}
		return $this->uiPropertyObject;
	}
	
	public function setDefaultUIProperty($uiProperty) {
		$this->uiPropertyObject = $uiProperty;
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
		if (!$this->uiPropertyObject && !$this->aGet('defaultUIProperty')) {
			$this->aSet('defaultUIProperty', UIProperty::toJSON(new Icon()));
		} else if ($this->uiPropertyObject) {
			$this->aSet('defaultUIProperty', UIProperty::toJSON($this->uiPropertyObject));
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