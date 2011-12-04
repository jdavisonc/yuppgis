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
	
	public static function getLabelFilterAttr($appName, $class, $attr){
		return $appName . '.' . $class . '.' . $attr;
	}
	
	public static function getLabelFilterConditionAND($appName, $view){
		if ($view) {
			return $appName . '.' . $view . '.filterAttr.AND';
		} else {
			return $appName . '.filterAttr.AND';
		}
	}
	
	public static function getLabelFilterConditionOR($appName, $view){
		if ($view) {
			return $appName . '.' . $view . '.filterAttr.OR';
		} else {
			return $appName . '.filterAttr.OR';
		}
	}
}

?>