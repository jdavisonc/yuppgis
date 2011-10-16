<?php

YuppLoader::load('yuppgis.core.services', 'GISWSDAL');
YuppLoader::load('yuppgis.core.services', 'RestGISWSDAL');

class GISPMBasic extends GISPersistentManager {
	
	/**
	 * @see GISPersistentManager::init()
	 */
	protected function init( $appName ) {
		// TODO_GIS: Deberia de tener dos instancias de dal distintas? uno comun y otro wsdal? xq esta clase no es igual que premium, 
		//			 ya q uno es ws y otro db.
		$this->dal = new DAL($appName);
		// TODO_GIS: Configuracion dinamica del basico
		$this->giswsdal = new RestGISWSDAL($appName); 
	}

	/**
	 * @see GISPersistentManager::get_gis_object()
	 */
	public function get_gis_object( $tableNameOwner, $attr, $persistentClass, $id ) {
		$geom = $this->giswsdal->get($tableNameOwner, $attr, $persistentClass, $id);
		$geom->setId($id);
		return $geom;
	}

	/**
	 * @see GISPersistentManager::save_gis_object()
	 */
	protected function save_gis_object( $ownerTableName, $attrNameAssoc, PersistentObject $obj ) {
		$id = $this->giswsdal->save();
		$geom->setId($id);
	}

	/**
	 * @see GISPersistentManager::delete_gis_object()
	 */
	protected function delete_gis_object($owner, $attrNameAssoc, $assocObj, $logical) {
		$this->giswsdal->delete();
	}

	/**
	 * @see GISPersistentManager::findByGISQuery()
	 */
	protected function findByGISQuery(GISQuery $query) {
		throw new Exception('Busquedas GISQuery no soportadas en YuppGIS Basico');
	}

	/**
	 * @see GISPersistentManager::processGISCondition()
	 */
	protected function processGISCondition(PersistentObject $instance, GISCondition $condition, ArrayObject $params ) {
		throw new Exception('Busqueda por atributo geografico no soportado en YuppGIS Basico');
	}
	
}

?>