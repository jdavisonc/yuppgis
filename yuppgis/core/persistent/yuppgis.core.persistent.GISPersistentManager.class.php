<?php

// Se importa DAL geografico
YuppLoader :: load('yuppgis.core.db', 'GISDAL');

class GISPersistentManager extends PersistentManager {

	public function init_dal( $appName ) {
		$this->dal = new GISDAL( $appName );
	}

	/**
	 * 
	 * Obtiene un objeto geografico desde la base de datos.
	 * @param unknown_type $tableNameOwner
	 * @param unknown_type $attr
	 * @param unknown_type $persistentClass
	 * @param unknown_type $id
	 */
	public function get_gis_object( $tableNameOwner, $attr, $persistentClass, $id ) {
		
		Logger::getInstance()->pm_log("GISPM.get_gis_object " . $persistentClass . " " . $id);

		$tableName = YuppGISConventions::gisTableName($tableNameOwner, $attr); 

		$attrValues = $this->dal->get_geometry( $tableName, $id );
		
   		// Se crea el objeto directamente ya que no se va a contar con herencia en tablas distintas para
   		// elementos geograficos.
   		return $this->createGISObjectFromData( $persistentClass, $attrValues );
	}
	
	/**
	 * 
	 * Crea un objeto geografico desde datos retornados por la base de datos.
	 * @param unknown_type $class
	 * @param unknown_type $data
	 */
	private function createGISObjectFromData( $class, $data ) {
		$attrsValues = array( 'id' => $data['id'], 'class' => $class );
		$attrsValues = array_merge( $attrsValues , TextGEO::fromText($class, $data['text']));
		
		return $this->createObjectFromData($class, $attrsValues);
	}
	
	/**
	 * 
	 * Se salva en cascada con el dueño y su nombre de atributo.
	 * @see PersistentManager::save_cascade_owner()
	 */
   public function save_cascade_owner( PersistentObject $owner, $attrNameAssoc, PersistentObject $obj, $sessId ) {
   		if (is_subclass_of($obj, Geometry :: getClassName())) {
   			$ownerTableName = YuppConventions::tableName( $owner );
   			return $this->save_gis_object($ownerTableName, $attrNameAssoc, $obj, $sessId);
   		} else {
   			return parent::save_cascade_owner( $owner, $attrNameAssoc, $obj, $sessId ) ;
   		}
   }
   
   /**
    * 
    * Se salva el objeto geografico en la base de datos.
    * @param unknown_type $ownerTableName
    * @param unknown_type $attrNameAssoc
    * @param PersistentObject $obj
    * @param unknown_type $sessId
    */
   private function save_gis_object( $ownerTableName, $attrNameAssoc, PersistentObject $obj, $sessId ) {
	   	Logger::getInstance()->pm_log("GISPM.save_object " . get_class($obj) );
	
	   	$tableName = YuppGISConventions::gisTableName($ownerTableName, $attrNameAssoc);
	
	   	if ( !$obj->getId() ) {
	   		// TODO_GIS: INSERT
	   		
	   		$attrGeo = TextGEO::toText( $obj );
	   		$id = $this->dal->generateNewId($tableName);
	   		
	   		//TODO_GIS, vamos a dejar el atributo class en esta tabla??
	   		$attrs = array( 'id' => $id, 'geom' => $attrGeo, 'class' => get_class($obj));
	   		$this->dal->insert_geometry($tableName, $attrs);
	   		$obj->setId($id);
	   	} else {
			// TODO_GIS: UPDATE
	   		throw new Exception("No soportado");
	   	}
   }
   
   
}

?>