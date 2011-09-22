<?php

YuppLoader :: load('core.db.criteria2', 'Query');

// Se importa DAL geografico
YuppLoader :: load('yuppgis.core.db', 'GISDAL');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISQuery');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISCondition');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISFunction');
YuppLoader :: load('yuppgis.core.persistent.serialize', 'WKTGEO');

class GISPMPremium  extends PersistentManager implements GISPersistentManager {

	// TODO_GIS: Ver de tema de configuracion cuando es un Basico, no crear un GISDAL o bien crear dos
	// 			 dos persistentmanager, GISPersistentManagerSimple y GISPersistentManagerPremium, uno para
	//			 DBGIS y otro para GISWS
	public function init_dal( $appName ) {
		$this->dal = new GISDAL( $appName );
	}

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
		$attrsValues = array( 'id' => $data['id'], 'class' => $class, 'uiproperty' => $data['uiproperty'] );
		$attrsValues = array_merge( $attrsValues , WKTGEO::fromText($class, $data['geo']));
		
		return $this->createObjectFromData($class, $attrsValues);
	}
	
	public function save_cascade_owner( PersistentObject $owner, $attrNameAssoc, PersistentObject $obj, $sessId ) {
   		if (is_subclass_of($obj, Geometry :: getClassName())) {
   			
   			$obj->executeBeforeSave();
   			
   			$ownerTableName = YuppConventions::tableName( $owner );
   			$this->save_gis_object($ownerTableName, $attrNameAssoc, $obj);
   			
   			$obj->executeAfterSave();
   		} else {
   			parent::save_cascade_owner( $owner, $attrNameAssoc, $obj, $sessId ) ;
   		}
   }
   
   /**
    * Se salva el objeto geografico en la base de datos.
    * @param unknown_type $ownerTableName
    * @param unknown_type $attrNameAssoc
    * @param PersistentObject $obj
    * @param unknown_type $sessId
    */
   private function save_gis_object( $ownerTableName, $attrNameAssoc, PersistentObject $obj ) {
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
   
   public function delete( $persistentInstance, $id, $logical ) {
	   	Logger::add( Logger::LEVEL_PM, "GISPM::delete ". __FILE__."@". __LINE__ );
	   	 
	   	$sassoc = $persistentInstance->getSimpleAssocValues();
	   	foreach ( $sassoc as $attrName => $assocObj )
	   	{
	   		// ojo el objeto debe estar cargado (se verifica eso)
	   		if ( $assocObj !== PersistentObject::NOT_LOADED_ASSOC &&  $persistentInstance->isOwnerOf( $attrName )) {
	   			//TODO_GIS: control de circularidad
	   			Logger::getInstance()->pm_log("GISPM::delete_cascade de ". $assocObj->getClass() .__LINE__);
	   			$this->delete_cascade( $persistentInstance, $attrName, $assocObj, $logical );
	   		}
	   	}
	   	
	   	$this->dal->delete( $persistentInstance->getClass(), $id, $logical );
	   	
	   	// Soporte MTI
	   	if (MultipleTableInheritanceSupport::isMTISubclassInstance( $persistentInstance )) {
	   		// Ahora tengo que pedir las superclases y para cada una, borrar la instancia parcial
	   		$superclasses = ModelUtils::getAllAncestorsOf($persistentInstance->getClass());
	   		foreach ($superclasses as $mtiClass) {
	   			$this->dal->delete( $mtiClass, $id, $logical );
	   		}
	   	}
   	}

   	private function delete_cascade( $owner, $attrNameAssoc, $assocObj, $logical ) {
   		if ( is_subclass_of($assocObj, Geometry::getClassName()) ) {
   			if ( !$logical ) {
    			$tableName = YuppGISConventions::gisTableName(YuppConventions::tableName( $owner ), $attrNameAssoc);
    			return $this->dal->deleteFromTable($tableName,  $assocObj->getId(), false);
    		}
    	} else {
    		return $this->delete($assocObj, $assocObj->getId(), $logical) ;
    	}
	}
	
	public function findByQuery(Query $q) {
		// TODO_GIS
	}
	
	public function findBy( PersistentObject $instance, Condition $condition, ArrayObject $params ) {
		$newCondition = $this->findGISBy($instance, $condition, $params);
		return parent::findBy($instance, $newCondition, $params);
	}
	
	private function findGISBy( PersistentObject $instance, Condition $condition, ArrayObject $params ) {
		if ( $condition instanceof GISCondition) {
			
			$attr = $condition->getAttribute();
			
			// Es gis condition, tener cuidado que puede tener subcondiciones comunes
			$tableName = YuppGISConventions::gisTableName(YuppConventions::tableName( $instance ), $attr->attr);
			
			$gisCondition = new GISCondition();
			$gisCondition->setType($condition->getType());
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
			
			$query_res = $this->dal->gis_query($query);
			
			$res = '';
			$first = true;
			foreach ($query_res as $value ) {
				if ($first) {
					$first = false;	
				} else {
					$res =  $res . ',';
				}
				$res = $res . $value['id'];
			}
			
			$attr_id = DatabaseNormalization::simpleAssoc($attr->attr); // Se normaliza el nombre para obtener el nombre de la columna
			if ($res !== '') {
				return Condition::IN($attr->alias, $attr_id, $res);
			} else {
				return Condition::IN($attr->alias, $attr_id, null);
			}
		} else {
			if ( $condition->getType() == Condition::TYPE_AND  || $condition->getType() == Condition::TYPE_OR || 
					$condition->getType() == Condition::TYPE_NOT ) {
				$newCondition = new Condition();
				$newCondition->setType($condition->getType());
				$subconditions = $condition->getSubconditions();
				for ($i = 0; $i < count($subconditions); $i++) {
					$newCondition->add($this->findGISBy( $instance, $subconditions[$i], $params ));
				}
				return $newCondition;
			} else {
				return $condition;
			}
		}
		
	}
	

}

?>