<?php

class GISPMBasic extends GISPersistentManager {
	
	/**
	 * @see GISPersistentManager::init()
	 */
	protected function init( $appName ) {
		// TODO_GIS: Deberia de tener dos instancias de dal distintas? uno comun y otro wsdal? xq esta clase no es igual que premium, 
		//			 ya q uno es ws y otro db.
		$this->dal = new DAL($appName);
	}

	/**
	 * @see GISPersistentManager::get_gis_object()
	 */
	public function get_gis_object( $tableNameOwner, $attr, $persistentClass, $id ) {
		
	}

	/**
	 * @see GISPersistentManager::save_gis_object()
	 */
	protected function save_gis_object( $ownerTableName, $attrNameAssoc, PersistentObject $obj ) {
		
	}

	/**
	 * @see GISPersistentManager::delete_gis_object()
	 */
	protected function delete_gis_object($owner, $attrNameAssoc, $assocObj, $logical) {
		
	}

	/**
	 * @see GISPersistentManager::findByGISQuery()
	 */
	protected function findByGISQuery(GISQuery $query) {
		
	}

	/**
	 * @see GISPersistentManager::processGISCondition()
	 */
	protected function processGISCondition(PersistentObject $instance, GISCondition $condition, ArrayObject $params ) {
		
	}
	
}

?>