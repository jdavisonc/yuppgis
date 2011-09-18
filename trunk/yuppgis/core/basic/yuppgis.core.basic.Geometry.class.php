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
		if ($this->uiPropertyObject == null) {
			$this->uiPropertyObject = UIProperty::fromJSON($this->aGet('uiproperty'));
		}
		return $this->uiPropertyObject;
	}
	
	public function setUIProperty($uiProperty) {
		$this->uiPropertyObject = $uiProperty;
	}
	
	/**
	 * Se serializa el atributo uiproperty a JSON
	 * @see PersistentObject::preValidate()
	 */
	public function preValidate(){
		$this->aSet('uiproperty', UIProperty::toJSON($this->uiPropertyObject));
	}

}

?>