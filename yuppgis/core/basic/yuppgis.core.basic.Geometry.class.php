<?php

YuppLoader::load('yuppgis.core.basic.ui', 'UIProperty');

class Geometry extends GISPersistentObject {
	
	private $uiPropertyObject = null;
	private $callbackUpdate = null;
	
	public function __construct($args = array (), $isSimpleInstance = false) {
		$this->addAttribute('uiproperty', Datatypes::TEXT);

		$this->callbackUpdate = new Callback();
		$this->callbackUpdate->set( $this, 'serializeUIProperty', array() );
		
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
		$this->registerToUpdateUIProperty();
		if ($this->uiPropertyObject == null) {
			$this->uiPropertyObject = UIProperty::fromJSON($this->aGet('uiproperty'));
		}
		return $this->uiPropertyObject;
	}
	
	public function setUIProperty($uiProperty) {
		$this->registerToUpdateUIProperty();
		$this->uiPropertyObject = $uiProperty;
	}
	
	public function serializeUIProperty() {
		$this->aSet('uiproperty', UIProperty::toJSON($this->uiPropertyObject));
	}
	
	private function registerToUpdateUIProperty() {
		if ($this->isDirty()) {
			$this->registerBeforeSaveCallback($this->callbackUpdate);
		}
	}

}

?>