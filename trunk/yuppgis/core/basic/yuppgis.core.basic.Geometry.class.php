<?php

abstract class Geometry extends GISPersistentObject {
	
	/**
	 * Retorna nombre de clase
	 * Solo soportado por PHP > 5.3
	 * @return nombre de la clase
	 */
	public static function getClassName() {
        return get_called_class();
    }
	
}

?>