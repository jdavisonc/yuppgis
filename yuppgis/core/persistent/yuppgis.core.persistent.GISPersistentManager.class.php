<?php

// Se importa DAL geografico
YuppLoader :: load('yuppgis.core.db', 'GISDAL');

class GISPersistentManager extends PersistentManager {

	public function init_dal( $appName ) {
		$this->dal = new GISDAL( $appName );
	}

	/**
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
	 * Crea un objeto geografico desde datos retornados por la base de datos.
	 * @param unknown_type $class
	 * @param unknown_type $data
	 */
	private function createGISObjectFromData( $class, $data ) {
		$attrsValues = array( 'id' => $data['id'], 'class' => $class );
		$attrsValues = array_merge( $attrsValues , WKTGEO::fromText($class, $data['text']));
		
		return $this->createObjectFromData($class, $attrsValues);
	}
	
	/**
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
    * Se salva el objeto geografico en la base de datos.
    * @param unknown_type $ownerTableName
    * @param unknown_type $attrNameAssoc
    * @param PersistentObject $obj
    * @param unknown_type $sessId
    */
   private function save_gis_object( $ownerTableName, $attrNameAssoc, PersistentObject $obj, $sessId ) {
	   	Logger::getInstance()->pm_log("GISPM.save_object " . get_class($obj) );
	
	   	$tableName = YuppGISConventions::gisTableName($ownerTableName, $attrNameAssoc);
		$attrGeo = WKTGEO::toText( $obj );
	   	
		if ( !$obj->getId() ) {
	   		$attrs = array( 'geom' => $attrGeo );
	   		$id = $this->dal->insert_geometry($tableName, $attrs);
	   		$obj->setId($id);
	   	} else {
	   		$attrs = array( 'id' => $obj->getId(), 'geom' => $attrGeo );
	   		$this->dal->update_geometry($tableName, $attrs);
	   	}
   }
   
	// TODO_GIS: No esta implemntado la elimincion en cascada, que se hace? Implementamos solo para nuestras clases o para todo?
	public function delete( $persistentInstance, $id, $logical ) {
		parent::delete($persistentInstance, $id, $logical);
	}
	
}

?>