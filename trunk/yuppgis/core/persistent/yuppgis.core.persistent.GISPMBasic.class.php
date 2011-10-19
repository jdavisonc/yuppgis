<?php

YuppLoader::load('yuppgis.core.services', 'GISWSDAL');
YuppLoader::load('yuppgis.core.services', 'RestWSGISDAL');

class GISPMBasic extends GISPersistentManager {
	
	/**
	 * @see GISPersistentManager::init()
	 */
	protected function init( $appName ) {
		// TODO_GIS: Deberia de tener dos instancias de dal distintas? uno comun y otro wsdal? xq esta clase no es igual que premium, 
		//			 ya q uno es ws y otro db.
		$this->dal = new DAL($appName);
		// TODO_GIS: Configuracion dinamica del basico
		$this->giswsdal = new RestWSGISDAL($appName); 
	}

	/**
	 * @see GISPersistentManager::get_gis_object()
	 */
	public function get_gis_object( $ownerName, $attr, $persistentClass, $id ) {
		$geom = $this->giswsdal->get($ownerName, $attr, $persistentClass, $id);
		$geom->setId($id);
		return $geom;
	}

	/**
	 * @see GISPersistentManager::save_gis_object()
	 */
	protected function save_gis_object( $ownerName, $attrNameAssoc, PersistentObject $obj ) {
		$id = $this->giswsdal->save($ownerName, $attrNameAssoc, $obj);
		$geom->setId($id);
	}

	/**
	 * @see GISPersistentManager::delete_gis_object()
	 */
	protected function delete_gis_object($ownerName, $attrNameAssoc, $assocObj, $logical) {
		$this->giswsdal->delete($ownerName, $attrNameAssoc, $assocObj, $logical);
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
	
	/**
	 * @see GISPersistentManager::generate_gisTables()
	 */
	protected function generate_gisTables( PersistentObject $owner, $attr, $appName) {
		;
	}
	
}

?>