<?php

YuppLoader::load('yuppgis.core.basic.ui', 'UIProperty');

/**
 * Clase que representa una figura Geometrica.
 *
 * @package yuppgis.core.basic
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class Geometry extends PersistentObject {
	
	// propiedades de las geometrias, dimension
	protected $dimension;
	
	protected $uiPropertyObject = null;
	
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
	 * Serializa el atributo uiproperty a JSON.
	 * 
	 * @see PersistentObject::preValidate()
	 */
	public function preValidate(){
		$this->aSet('uiproperty', UIProperty::toJSON($this->uiPropertyObject));
	}
	
	/**
	 * Retorna nombre de clase. Nota: Solo soportado por PHP > 5.3
	 * @return nombre de la clase
	 */
	public static function getClassName() {
        return get_called_class();
    }
    
}

?>