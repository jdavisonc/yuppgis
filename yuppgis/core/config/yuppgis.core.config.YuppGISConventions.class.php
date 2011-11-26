<?php

/**
 * Clase que define las convenciones de YuppGIS.
 * 
 * @package yuppgis.core.config
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class YuppGISConventions extends YuppConventions {

	public static function gisTableName($tableNameOwner, $attr) {
		return $tableNameOwner . '_' . $attr . '_geo';
	}
	
	public static function getReservedWords(){
		return array("observers", "app", "class", "deleted", "id");
	}
}

?>