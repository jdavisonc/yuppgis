<?php

YuppLoader :: load('core.db.criteria2', 'Query');

// Se importa DAL geografico
YuppLoader :: load('yuppgis.core.db', 'GISDAL');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISQuery');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISCondition');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISFunction');
YuppLoader :: load('yuppgis.core.db.criteria2', 'SelectValue');
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
   		$geo = $this->createGISObjectFromData( $attrValues );
   		
   		//Valido que la clase creada sea una instancia valida
   		if (! $geo instanceof $persistentClass) {
   			throw new Exception('No coinciden los tipos de geometrias. Se esperaba ' . $persistentClass . ' y se obtuvo ' . $geo->getClass());
   		}
   		return $geo;
	}
	
	/**
	 * Crea un objeto geografico desde datos retornados por la base de datos.
	 * @param unknown_type $class
	 * @param unknown_type $data
	 */
	//TODO_GIS
	private function createGISObjectFromData( $data ) {
		$attrsValues = array( 'id' => $data['id'],  'uiproperty' => $data['uiproperty'] );
		$attrsValues = array_merge( $attrsValues , WKTGEO::fromText($data['geo']));
		
		return $this->createObjectFromData($attrsValues['class'], $attrsValues);
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
	
	/**
	 * Si es una GISQuery, la descomponemos y creamos una Query soportada por Yupp.
	 * @see PersistentManager::findByQuery()
	 */
	public function findByQuery(Query $q) {
		// TODO_GIS
		if ($q instanceof GISQuery) {
			
			$newQuery = new Query();
			$newQuery->setSelect($this->GISSelectToSelect($q->getFrom(), $q->getSelect()));
			$newQuery->setFrom($this->GISFromToFrom($q->getFrom()));
			$newQuery->setCondition($this->GISConditionToCondition($q->getFrom(), $q->getWhere()));
			
			return $this->dal->query($newQuery);
			
			
		} else {
			return parent::findByQuery($q);
		}
	}
	
	public function findBy( PersistentObject $instance, Condition $condition, ArrayObject $params ) {
		$newCondition = $this->findGISBy($instance, $condition, $params);
		return parent::findBy($instance, $newCondition, $params);
	}
	
	private static function getGeometryAttrs($instance_or_class) {
		$ins = $instance_or_class;
		if ( !is_object($ins) ) {
			$ins = new $instance_or_class(array(), true);
		}
		return $ins->hasGeometryAttributes();
	}
	
	private function GISProjectionToProjection($geoAttrsOfFroms, $selectItem) {
		if ($selectItem instanceof SelectAttribute) {
			$alias = $selectItem->getAlias();
			
			if (in_array($selectItem->getAttrName(), $geoAttrsOfFroms[$alias])) {
				$geoAttrAssoc = DatabaseNormalization::simpleAssoc($selectItem->getAttrName());
				return new SelectAttribute($alias, $geoAttrAssoc, $selectItem->getSelectItemAlias());
			} else {
				return new SelectAttribute($alias, $selectItem->getAttrName(), $selectItem->getSelectItemAlias()); 
			}
		} else if ($selectItem instanceof SelectAggregation) {
			return new SelectAggregation($selectItem->getName(), 
				$this->GISProjectionToProjection($geoAttrsOfFroms, $selectItem->getParam()), 
				$selectItem->getSelectItemAlias());
		} else if ($selectItem instanceof SelectValue) {
			return new SelectValue($selectItem->getValue(), $selectItem->getSelectItemAlias());
		} else if ($selectItem instanceof GISFunction) {
			$params = array();
			foreach ($selectItem->getParams() as $param) {
				$params[] = $this->GISProjectionToProjection($geoAttrsOfFroms, $param);
			}
			return new GISFunction($selectItem->getType(), $params, $selectItem->getSelectItemAlias());
		} else {
			throw new Exception("No implementado");
		}
	}
	
	private function GISFromToFrom(array $gisFroms) {
		$from = array();
		foreach ($gisFroms as $gisFrom) {
			$f = new stdClass();
			$f->alias = $gisFrom->alias;
			$f->name = YuppConventions::tableName($gisFrom->instance_or_class);
			$from[] = $f;
		}
		return $from;
	}
	
	private function GISSelectToSelect(array $froms, $gisSelect) {
		
		$geoAttrsOfFroms = array();
		foreach ($froms as $from) {
			// TODO_GIS ver de mejorar el caso de que se realize From de las mismas tablas y no se realize
			// dos veces la busqueda
			$geoAttrsOfFroms[$from->alias] = self::getGeometryAttrs($from->instance_or_class);
		}
		
		$projections = array();
		foreach ($gisSelect->getAll() as $selectItem) {
			$projections[] = $this->GISProjectionToProjection($geoAttrsOfFroms, $selectItem);
		}
		return new Select($projections);
	}
	
	private function GISConditionToCondition(array $froms, Condition $condition) {
		
		if ( $condition instanceof GISCondition) {
			
			$attr = $condition->getAttribute();
			
			$fromSelect = self::getFrom($froms, $attr->alias);
			
			// Es gis condition, tener cuidado que puede tener subcondiciones comunes
			$tableName = YuppConventions::tableName($fromSelect->instance_or_class);
			$gisTableName = YuppGISConventions::gisTableName($tableName, $attr->attr);
			
			$gisCondition = new GISCondition();
			$gisCondition->setType($condition->getType());
			$gisCondition->setAttribute($fromSelect->alias, 'geom'); // Se establece el alias de la tabla (Ver query mas abajo) y nombre de la columna
			
			$query = new Query();
			$query->addFrom($gisTableName, $fromSelect->alias);
			$query->addProjection($fromSelect->alias, 'id', 'id');
			
			if ($condition->getReferenceAttribute() !== null) {
				$attr2 = $condition->getReferenceAttribute();
				$fromSelect2 = self::getFrom($froms, $attr2->alias);
				
				$tableName2 = YuppConventions::tableName($fromSelect2->instance_or_class);
				$gisTableName2 = YuppGISConventions::gisTableName($tableName2, $attr2->attr);
				$query->addFrom($gisTableName2, $fromSelect2->alias);
				$query->addProjection($fromSelect2->alias, 'id', 'id2');
				
				$gisCondition->setReferenceAttribute($fromSelect2->alias, 'geom');
				
			} else {
				$attrGeo = WKTGEO::toText( $condition->getReferenceValue() );
				$gisCondition->setReferenceValue($attrGeo);
			}
			$query->setCondition($gisCondition);
			
			$query_res = $this->dal->gis_query($query);
			
			$res = self::createValuesStringFromKeyOnQuery($query_res, 'id');
			$res2 = null;
			if ($condition->getReferenceAttribute() !== null) {
				$res2 = self::createValuesStringFromKeyOnQuery($query_res, 'id2');
			}
			
			if ($res2 == null) {
				$attr_id = DatabaseNormalization::simpleAssoc($attr->attr); // Se normaliza el nombre para obtener el nombre de la columna
				if ($res !== '') {
					return Condition::IN($attr->alias, $attr_id, $res);
				} else {
					return Condition::IN($attr->alias, $attr_id, null);
				}
			} else {
				$newCondition = new Condition();
				$newCondition->setType(Condition::TYPE_AND);
				
				$attr_id = DatabaseNormalization::simpleAssoc($attr->attr); // Se normaliza el nombre para obtener el nombre de la columna
				if ($res !== '') {
					$newCondition->add(Condition::IN($attr->alias, $attr_id, $res));
				} else {
					$newCondition->add(Condition::IN($attr->alias, $attr_id, null));
				}
				$attr_id2 = DatabaseNormalization::simpleAssoc($attr2->attr); // Se normaliza el nombre para obtener el nombre de la columna
				if ($res2 !== '') {
					$newCondition->add(Condition::IN($attr2->alias, $attr_id2, $res2));
				} else {
					$newCondition->add(Condition::IN($attr2->alias, $attr_id2, null));
				}
				return $newCondition;
			}
			
			
		} else {
			if ( $condition->getType() == Condition::TYPE_AND  || $condition->getType() == Condition::TYPE_OR || 
					$condition->getType() == Condition::TYPE_NOT ) {
				$newCondition = new Condition();
				$newCondition->setType($condition->getType());
				$subconditions = $condition->getSubconditions();
				for ($i = 0; $i < count($subconditions); $i++) {
					$newCondition->add($this->GISConditionToCondition( $froms, $subconditions[$i] ));
				}
				return $newCondition;
			} else {
				return $condition;
			}
		}
		
	}
	
	/**
	 * TODO_GIS
	 * Funcion que obtiene la $key del resultado de la DB y los retorna en una lista string (SIN REPETIDOS)
	 * @param $query_res
	 * @param $key
	 */
	private static function createValuesStringFromKeyOnQuery($query_res, $key) {
		$res = array ();
		foreach ($query_res as $value ) {
			$res[] = $value[$key];
		}
		return implode(',', array_unique($res, SORT_REGULAR));
	}
	
	/**
	 * Retorna el objeto From dado un alias en un array de From
	 * @param $from
	 * @param $alias
	 */
	private static function getFrom(array $from, $alias) {
		$i = 0;
		$finded = null;
		while ($i < count($from) && $finded == null) {
			if ($from[$i]->alias == $alias) {
				$finded = $from[$i];
			}
			$i++;
		}
		if ($finded == null) {
			throw new Exception("Alias en From no encontrado");
		}
		return $finded;
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