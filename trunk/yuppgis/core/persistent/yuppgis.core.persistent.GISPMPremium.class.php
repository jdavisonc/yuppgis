<?php

// Se importa DAL geografico
YuppLoader :: load('yuppgis.core.db', 'GISDAL');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISQuery');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISCondition');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISFunction');
YuppLoader :: load('yuppgis.core.db.criteria2', 'SelectValue');
YuppLoader :: load('yuppgis.core.db.criteria2', 'SelectGISAttribute');

YuppLoader :: load('yuppgis.core.persistent', 'GISQueryProcessor');
YuppLoader :: load('yuppgis.core.persistent.serialize', 'WKTGEO');

class GISPMPremium extends GISPersistentManager {
	
	private $gisQueryProcessor = null;

	/**
	 * @see GISPersistentManager::init()
	 */
	protected function init( $appName ) {
		$this->dal = new GISDAL( $appName );
	}

	/**
	 * @see GISPersistentManager::get_gis_object()
	 */
	public function get_gis_object( $tableNameOwner, $attr, $persistentClass, $id ) {
		Logger::getInstance()->pm_log("GISPM.get_gis_object " . $persistentClass . " " . $id);
		
		if ( $id === NULL ) {
			throw new Exception("id de objeto " . $persistentClass . " no puede ser null");
		}

		$tableName = YuppGISConventions::gisTableName($tableNameOwner, $attr); 

		$query = new Query();
		$query->addFrom($tableName, 'geo');
		$query->getSelect()->add(new SelectAttribute('geo', 'id'));
		$query->getSelect()->add(new SelectAttribute('geo', 'uiproperty'));
		$query->getSelect()->add(new SelectGISAttribute('geo', 'geom'));
		$query->setCondition(Condition::EQ('geo', 'id', $id));
		
		$query_res = $this->dal->gis_query($query);
		if (count($query_res) == 0) {
			throw new Exception("No se encuentra el objeto " . $persistentClass . " con id " . $id);
		}
		
   		// Se crea el objeto directamente ya que no se va a contar con herencia en tablas distintas para
   		// elementos geograficos.
   		$geo = $this->createGISObjectFromData( $query_res[0] );
   		
   		//Valido que la clase creada sea una instancia valida
   		if (! ($geo instanceof $persistentClass)) {
   			throw new Exception('No coinciden los tipos de geometrias. Se esperaba ' . $persistentClass . ' y se obtuvo ' . $geo->getClass());
   		}
   		return $geo;
	}
	
	/**
	 * Crea un objeto geografico desde datos retornados por la base de datos.
	 * 
	 * @param unknown_type $class
	 * @param unknown_type $data
	 */
	private function createGISObjectFromData( $data ) {
		$geo = $data['geom'];
		$geo->setId($data['id']);
		$geo->setUiproperty($data['uiproperty']);
		return $geo;
	}
	
	/**
	 * @see GISPersistentManager::save_gis_object()
	 */
	protected function save_gis_object( $ownerTableName, $attrNameAssoc, PersistentObject $obj ) {
	   	Logger::getInstance()->pm_log("GISPM.save_object " . get_class($obj) );
	
	   	$tableName = YuppGISConventions::gisTableName($ownerTableName, $attrNameAssoc);
		$attrGeo = WKTGEO::toText( $obj );
	   	
		if ( !$obj->getId() ) {
	   		$attrs = array( 'geom' => $attrGeo, 'uiproperty' => $obj->aGet('uiproperty') );
	   		$id = $this->dal->insert_geometry($tableName, $attrs);
	   		$obj->setId($id);
	   	} else {
	   		$attrs = array( 'id' => $obj->getId(), 'geom' => $attrGeo, 'uiproperty' => $obj->aGet('uiproperty') );
	   		$this->dal->update_geometry($tableName, $attrs);
	   	}
	}
	
	/**
	 * @see GISPersistentManager::delete_gis_object()
	 */
	protected function delete_gis_object($owner, $attrNameAssoc, $assocObj, $logical) {
		if ( !$logical ) {
			$tableName = YuppGISConventions::gisTableName(YuppConventions::tableName( $owner ), $attrNameAssoc);
			return $this->dal->deleteFromTable($tableName,  $assocObj->getId(), false);
		}
	}
	
	/**
	 * Retorna instancia de procesador de consultas geograficas
	 * @return GISQueryProcessor
	 */
	private function getGISQueryProcessor() {
		if ($this->gisQueryProcessor == null) {
			$this->gisQueryProcessor = new GISQueryProcessor($this->dal);
		}
		return $this->gisQueryProcessor;
	}

	/**
	 * @see GISPersistentManager::findByGISQuery()
	 */
	protected function findByGISQuery(GISQuery $query) {
		return $this->getGISQueryProcessor()->process($query);
	}
	
	/**
	 * @see GISPersistentManager::processGISCondition()
	 */
	protected function processGISCondition(PersistentObject $instance, GISCondition $condition, ArrayObject $params ) {
		$attr = $condition->getAttribute();
		
		// Es gis condition, tener cuidado que puede tener subcondiciones comunes
		$tableName = YuppGISConventions::gisTableName(YuppConventions::tableName( $instance ), $attr->attr);
		
		$gisCondition = new GISCondition();
		$gisCondition->setType($condition->getType());
		$gisCondition->setExtraValueReference($condition->getExtraValueReference());
		$gisCondition->setAttribute('geo', 'geom'); // Se establece el alias de la tabla (Ver query mas abajo) y nombre de la columna
		
		if ($condition->getReferenceAttribute() !== null) {
			//TODO_GIS: Consulta que compara un valor con valor de otra tabla geografica -> g.geom == j.geom
			// Tiene sentido para cuando es una condicion sobre elementos? o seria solo para Query?
		} else {
			$attrGeo = WKTGEO::toText( $condition->getReferenceValue() );
			$gisCondition->setReferenceValue($attrGeo);
		}
		
		$query = new Query();
		$query->addFrom($tableName, 'geo');
		$query->addProjection('geo', 'id');
		$query->setCondition($gisCondition);
		
		return $this->dal->gis_query($query);
	}

}

?>