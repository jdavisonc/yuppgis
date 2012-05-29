<?php

/**
 * Clase que representa una etiqueta.
 * 
 * @package yuppgis.core.basic
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class Tag extends PersistentObject {

	function __construct($args = array (), $isSimpleInstance = false) {
		$this->setWithTable("tag");
		$this->addAttribute("name", Datatypes :: TEXT);		
		$this->addAttribute("color", Datatypes :: TEXT);
		
		parent :: __construct($args, $isSimpleInstance);
						
	}	
	
	public static function listAll(ArrayObject $params) {
		self :: $thisClass = __CLASS__;
		return PersistentObject::listAll($params);
	}
	
}

?>