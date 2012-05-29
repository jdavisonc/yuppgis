<?php

/**
 * Interfaz que define las operaciones que debe tener un conector a un repositorio de datos geograficos
 * 
 * @package yuppgis.core.services
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
interface GISWSDAL {
	
	
	function get($ownerName, $attr, $persistentClass, $id);
	
	function save($ownerName, $attr, PersistentObject $obj);
	
	function delete($ownerName, $attr, $id, $logical);
	
}

?>