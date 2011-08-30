<?php

abstract class Geometry extends GISPersistentObject {

	/**
     * Toda geometria pertenece a cualquier elemento que la contenga.
	 * @see PersistentObject::belonsToClass()
	 */
	public function belonsToClass($className) {
		return true;		
	}
	
}

?>