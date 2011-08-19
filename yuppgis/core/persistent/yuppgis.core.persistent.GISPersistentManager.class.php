<?php

// Se importa DAL geografico
YuppLoader :: load('yuppgis.core.db', 'GISDAL');

class GISPersistentManager extends PersistentManager {

	public function init_dal( $appName ) {
		$this->dal = new GISDAL( $appName );
	}
	
	// Trae un objeto simple sin asociaciones hasMany y solo los ids de hasOne.
	public static function get_gis_object( $tableNameOwner, $attr, $persistentClass, $id ) {
		
		Logger::getInstance()->pm_log("GISPM.get_gis_object " . $persistentClass . " " . $id);

		$tableName = YuppGISConventions::gisTableName($tableNameOwner, $attr); 

		$attrValues = $this->dal->get_geometry( $tableName, $id );
   		$attrValues["class"] = $persistentClass;
		
   		// Se crea el objeto directamente ya que no se va a contar con herencia en tablas distintas para
   		// elementos geograficos.
   		return $this->createGISObjectFromData( $realClass, $attrValues );
	}
	
	// TODO_GIS: Parsear GML que viene en $data
	private function createGISObjectFromData( $class, $data ) {
		return new Point(23, 32);
	}
	 
}

?>