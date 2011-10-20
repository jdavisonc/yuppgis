<?php

// Se importa DAL geografico
YuppLoader :: load('yuppgis.core.db', 'GISDAL');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISQuery');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISCondition');
YuppLoader :: load('yuppgis.core.db.criteria2', 'GISFunction');
YuppLoader :: load('yuppgis.core.db.criteria2', 'SelectValue');
YuppLoader :: load('yuppgis.core.db.criteria2', 'SelectGISAttribute');
YuppLoader :: load('yuppgis.core.db.criteria2', 'SelectGIS');

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
	public function get_gis_object( $ownerName, $attr, $persistentClass, $id ) {
		Logger::getInstance()->pm_log("GISPM.get_gis_object " . $persistentClass . " " . $id);
		
		if ( $id === NULL ) {
			throw new Exception("id de objeto " . $persistentClass . " no puede ser null");
		}

		$tableNameOwner = YuppConventions::tableName($ownerName);
		$tableName = YuppGISConventions::gisTableName($tableNameOwner, $attr); 

		$query = new Query();
		$query->addFrom($tableName, 'geo');
		$query->getSelect()->add(new SelectGIS('geo', 'geom'));
		$query->setCondition(Condition::EQ('geo', 'id', $id));
		
		$query_res = $this->dal->gis_query($query);
		if (count($query_res) == 0) {
			throw new Exception("No se encuentra el objeto " . $persistentClass . " con id " . $id);
		}
		
   		// Se crea el objeto directamente ya que no se va a contar con herencia en tablas distintas para
   		// elementos geograficos.
   		$geo =  $query_res[0]['geom'];
   		
   		//Valido que la clase creada sea una instancia valida
   		if (! ($geo instanceof $persistentClass)) {
   			throw new Exception('No coinciden los tipos de geometrias. Se esperaba ' . $persistentClass . ' y se obtuvo ' . $geo->getClass());
   		}
   		return $geo;
	}
	
	
	
	/**
	 * @see GISPersistentManager::save_gis_object()
	 */
	protected function save_gis_object( $ownerName, $attrNameAssoc, PersistentObject $obj ) {
	   	Logger::getInstance()->pm_log("GISPM.save_object " . get_class($obj) );
	
	   	$ownerTableName = YuppConventions::tableName($ownerName);
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
	protected function delete_gis_object($ownerName, $attrNameAssoc, $assocObj, $logical) {
		if ( !$logical ) {
			$tableName = YuppGISConventions::gisTableName(YuppConventions::tableName($ownerName), $attrNameAssoc);
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
	
	/**
	 * TODO_GIS
	 * @param PersistentManager $owner
	 * @param unknown_type $attr
	 */
	public function generate_gisTables( PersistentObject $owner, $attr, $appName) {
		Logger::getInstance()->pm_log("GISPMPreiumManager::generate");

		$ownertableName = YuppConventions::tableName( $owner );
		$tableName = YuppGISConventions::gisTableName($ownertableName, $attr, $appName);
		
		if ( !$this->dal->tableExists( $tableName ) ) {
	      
		    if (!$this->dal->tableGISExists($tableName)) {
			    $this->dal->createGISTable($tableName);
			    $srid = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_SRID);
			    
			    $this->dal->addGeometryColumn($tableName, $srid, GISDatatypes::getTypeByName($owner->getType($attr)));
		    }
		}
   }
         
	public function tableExists($className) {
   		
		//$isModePremium = YuppGISConfig::getInstance()->getGISPropertyValue( $appName, YuppGISConfig::PROP_YUPPGIS_MODE) == YuppGISConfig::MODE_PREMIUM;
		
		$ins = new $className();
		$res = array();
		
		if (is_subclass_of($ins, GISPersistentObject::getClassName())) {

			$tableName = YuppConventions::tableName( $className );
	   		if ($this->dal->tableExists( $tableName )) {
	   			$res[$className] = array('tableName'=>$tableName, 'created'=>"CREADA");
	   		} else {
	   			$res[$className] = array('tableName'=>$tableName, 'created'=>"NO CREADA");
	   		}

	   		// FKs hasOne
            $ho_attrs = $ins->getHasOne();
            foreach ( $ho_attrs as $attr => $refClass ) {
				$isGeometry = is_subclass_of($refClass , Geometry::getClassName());

				if ($isGeometry) {
					$GIStableName = YuppGISConventions::gisTableName($tableName, $attr);
	    			
					if ($this->dal->tableGISExists($GIStableName)) {
	    				$res[$className . '_' . $attr] = array('tableName'=>$GIStableName, 'created'=>"CREADA");
	    			} else {
	    				$res[$className . '_' . $attr] = array('tableName'=>$GIStableName, 'created'=>"NO CREADA");
	    			}
				}	
            }
            
            return $res;
		   		
		} else {
			return parent::tableExists($className);
		}	
   }

}

?>