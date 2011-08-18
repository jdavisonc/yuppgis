<?php

class GISPersistentManager extends PersistentManager {

	// Trae un objeto simple sin asociaciones hasMany y solo los ids de hasOne.
	public static function get_gis_object( $owner, $attr, $persistentClass, $id ) {
		
		Logger::getInstance()->pm_log("PM.get_object " . $persistentClass . " " . $id);

		//$tableName = YuppConventions::tableName( $obj );
		
		// TODO_GIS: Crear yuppgis conventions para hacer este mapeo, falta nombre de la aplicacion
		$tableName = 'prototipo_'.$owner . '_' . $attr . '_geo'; 

		$attrValues = PersistentManager::getInstance()->getGeometry( $persistentClass, $tableName, $id );
		return PersistentManager::getInstance()->get_mti_object_byData( $persistentClass, $attrValues );
		
	}
	 
}

?>