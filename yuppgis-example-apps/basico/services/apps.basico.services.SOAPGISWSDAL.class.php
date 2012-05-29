<?php

/**
 * Clase de ejemplo de como seria la implementacion de la interfaz GISWSDAL para un cliente SOAP.
 *
 * @author harley
 */
class SOAPGISWSDAL implements GISWSDAL {

	function __construct() {

	}

	public function get($ownerName, $attr, $persistentClass, $id) {
		// TODO_GIS: Implementar
		throw new Exception("SOAPGISWSDAL->get() no implementado.");
	}

	public function save($ownerName, $attr, PersistentObject $obj) {
		// TODO_GIS: Implementar
		throw new Exception("SOAPGISWSDAL->save() no implementado.");
	}

	public function delete($ownerName, $attr, $id, $logical) {
		// TODO_GIS: Implementar
		throw new Exception("SOAPGISWSDAL->delete() no implementado.");
	}

}

?>