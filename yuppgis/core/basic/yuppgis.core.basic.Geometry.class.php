<?php

YuppLoader::load('yuppgis.core.basic.ui', 'UIProperty');

class Geometry extends GISPersistentObject {
	
	private $uiPropertyObject = null;
	
	public function __construct($args = array (), $isSimpleInstance = false) {
		$this->addAttribute('uiproperty', Datatypes::TEXT);		
		parent :: __construct($args, $isSimpleInstance);
	}

	/**
     * Toda geometria pertenece a cualquier elemento que la contenga.
	 * @see PersistentObject::belonsToClass()
	 */
	public function belonsToClass($className) {
		return true;		
	}
	
	public function getUIProperty() {
		//TODO_GIS: $this->registerBeforeSaveCallback($this->UIProp2JSON());
		if ($this->uiPropertyObject == null) {
			$this->JSON2UIProp();
		}
		return $this->uiPropertyObject;
	}
	
	public function setUIProperty($uiProperty) {
		//TODO_GIS: $this->registerBeforeSaveCallback();
		$this->uiPropertyObject = $uiProperty;
		$this->aSet('uiproperty', 'hola');
	}
	
	private function UIProp2JSON() {
		
	}
	
	private function JSON2UIProp() {
		
	}
	
}

?>