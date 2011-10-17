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
	public function get_gis_object( $tableNameOwner, $attr, $persistentClass, $id ) {
		Logger::getInstance()->pm_log("GISPM.get_gis_object " . $persistentClass . " " . $id);
		
		if ( $id === NULL ) {
			throw new Exception("id de objeto " . $persistentClass . " no puede ser null");
		}

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
	
	/**
	 * 
	 * Enter description here ...
	 * @param PersistentManager $owner
	 * @param unknown_type $attr
	 */
	public function generate_gisTables( PersistentObject $owner, $attr, $appName) {
	    
		Logger::getInstance()->pm_log("GISPMPreiumManager::generate");
	      
	    $ownertableName = YuppConventions::tableName( $owner );
	    $tableName = YuppGISConventions::gisTableName($ownertableName, $attr);
	    
	    $this->dal->createGISTable($tableName);
	    $srid = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_SRID);
	    
	    $this->dal->addGeometryColumn($tableName, $srid, GISDatatypes::getTypeByName($owner->getType($attr)));
   }
   
   public function generateAll( $appName ) {
		Logger::getInstance()->pm_log("GISPersistentManager::generateAll ======");
       	
		// Todas las clases del primer nivel del modelo.
		$persitent = ModelUtils::getSubclassesOf( 'PersistentObject', $appName ); // FIXME> no es recursiva!
		$gisPersistent = ModelUtils::getSubclassesOf( GISPersistentObject::getClassName(), $appName ); // FIXME> no es recursiva!
		
		$A = array_merge($persitent, $gisPersistent);
		
		$mode = YuppGISConfig::getInstance()->getGISPropertyValue( $appName, YuppGISConfig::PROP_YUPPGIS_MODE);
		$this->generateAllByClass($A, $mode, $appName);
		
		$class = array();
   		$class[] = 'Map';
   		$class[] = 'DataLayer';
   		$class[] = 'Tag';
   		
   		$class[] = 'GISPersistentObject';
   		$mode = YuppGISConfig::getInstance()->getGISPropertyValue( $appName, YuppGISConfig::PROP_YUPPGIS_MODE);
   		
   		$this->generateAllByClass($class, $mode, $appName);	
   }
   
   
	/**
	 * 
	 * @see PersistentManager::generateAll()
	 */	
   private function generateAllByClass(array $A, $mode = null, $appName = null) {
		// Se utiliza luego para generar FKs.
        $generatedPOs = array();
        $dalForApp = $this->dal;
        
        $isModePremium = $mode != null && $mode == YuppGISConfig::MODE_PREMIUM;
          
   	
   		foreach( $A as $clazz )
          {
             $struct = MultipleTableInheritanceSupport::getMultipleTableInheritanceStructureToGenerateModel( $clazz );
    
             // struct es un mapeo por clave las clases que generan una tabla y valor las clases que se mapean a esa tabla.
             foreach ($struct as $class => $subclassesOnSameTable)
             {
                // Instancia que genera tabla
                $c_ins = new $class(); // FIXME: supongo que ya tiene withTable, luego veo el caso que no se le ponga WT a la superclase...
                // FIXME: como tambien tiene los atributos de las superclases y como van en otra tabla, hay que sacarlos.
                
                // Para cara subclase que se mapea en la misma tabla
                foreach ( $subclassesOnSameTable as $subclass )
                {
                   $sc_ins = new $subclass(); // Para setear los atributos.
                   
                   $props = $sc_ins->getAttributeTypes();
                   $hone  = $sc_ins->getHasOne();
                   $hmany = $sc_ins->getHasMany();
                   
                   // FIXME: si el artibuto no es de una subclase parece que tambien pone nullable true...
                   
                   // Agrega constraint nullable true, para que los atributos de las subclases
                   // puedan ser nulos en la tabla, para que funcione bien el mapeo de herencia de una tabla.
                   //Logger::getInstance()->pm_log( "Para cada attr de: $subclass " . __FILE__ . " " . __LINE__);
                   foreach ($props as $attr => $type)
                   {
                      // FIXME: esta parte seria mas facil si simplemente cuando la clase tiene la constraint 
                      // y le seteo otra del mismo tipo para el mismo atributo, sobreescriba la anterior.
    
                      $constraint = $sc_ins->getConstraintOfClass( $attr, 'Nullable' );
                      if ($constraint !== NULL)
                      {
                         //Logger::getInstance()->log( "CONTRAINT NULLABLE EXISTE!");
                         // Si hay, setea en true
                         $constraint->setValue(true);
                      }
                      else
                      {
                         // Si no hay, agrega nueva
                         //Logger::getInstance()->log( "CONTRAINT NULLABLE NO EXISTE!, LA AGREGA");
                         $sc_ins->addConstraints($attr, array(Constraint::nullable(true)));
                      }
                   }
                   
                   //Logger::getInstance()->pm_log( "Termina con las constraints ======= " . __FILE__ . " " . __LINE__);
                   
                   // Se toma luego de modificar las restricciones
                   $constraints = $sc_ins->getConstraints();
                   
                   foreach( $props as $name => $type ) $c_ins->addAttribute($name, $type);
                   foreach( $hone  as $name => $type ) $c_ins->addHasOne($name, $type);
                   foreach( $hmany as $name => $type ) $c_ins->addHasMany($name, $type);
                   
                   // Agrego las constraints al final porque puedo referenciar atributos que todavia no fueron agregados.
                   foreach( $constraints as $attr => $constraintList ) $c_ins->addConstraints($attr, $constraintList);
                }
                
                //TODO GIS_ revisar si copiar y pasar al Premium
                $parent_class = get_parent_class($c_ins);
                if ( $parent_class !== 'PersistentObject' &&  $parent_class !== GISPersistentObject::getClassName() ) // Si la instancia no es de primer nivel
                {
                   // La superclase de c_ins se mapea en otra tabla, saco esos atributos...
                   $suc_ins = new $parent_class();
                   $c_ins = PersistentObject::less($c_ins, $suc_ins); // Saco los atributos de la superclase
                }
                
                $tableName = YuppConventions::tableName( $c_ins );

                // FIXME: esta operacion necesita instanciar una DAL por cada aplicacion.
                // La implementacion esta orientada a la clase, no a la aplicacion, hay que modificarla.
                
                // Si la tabla ya existe, no la crea.
                if ( !$dalForApp->tableExists( $tableName ) )
                {
                   // FIXME: c_ins no tiene las restricciones sobre los atributos inyectados.
                   $this->generate( $c_ins, $dalForApp );
                
                   // Para luego generar FKs.
                   $generatedPOs[] = $c_ins;
                }
             } // foreach ($struct as $class => $subclassesOnSameTable)
          } // foreach( $A as $clazz )
          
          
          // ======================================================================
          // Crear FKs en la base.
          
          //Logger::struct( $generatedPOs, "GENERATED OBJS" );
          
          foreach ($generatedPOs as $ins)
          {
             $tableName = YuppConventions::tableName( $ins );
             $fks = array();
             
             // FKs hasOne
             $ho_attrs = $ins->getHasOne();
             foreach ( $ho_attrs as $attr => $refClass )
             {
                // Problema: pasa lo mismo que pasaba en YuppConventions.relTableName, esta tratando
                // de inyectar la FK en la tabla incorrecta porque la instancia es de una superclase
                // de la clase donde se declara la relacion HasOne, entonces hay que verificar si una
                // subclase no tiene ya el atributo hasOne declarado, para asegurarse que es de la
                // instancia actual y no intentar generar la FK si no lo es.
                
                $instConElAtributoHasOne = NULL;
                $subclasses = ModelUtils::getAllAncestorsOf( $ins->getClass() );
                
                foreach ( $subclasses as $aclass )
                {
                   $ains = new $aclass();
                   if ( $ains->hasOneOfThis( $refClass ) )
                   {
                      //Logger::getInstance()->log( $ains->getClass() . " TIENE UNO DE: $refClass" );
                      $instConElAtributoHasOne = $ains; // EL ATRIBUTO ES DE OTRA INSTANCIA!
                      break;
                   }
                }
                
                // Si el atributo de FK hasOne es de la instancia actual, se genera:
                if ( $instConElAtributoHasOne === NULL )
                {
                   // Para ChasOne esta generando "chasOne", y el nombre de la tabla que aparece en la tabla es "chasone".
                   
					// TODO_GIS _ si tiene habilitado modo premium??
				   $isGeometry = is_subclass_of($refClass , Geometry::getClassName());
                   if ($isGeometry && $isModePremium) {

                   		$refTableName = YuppGISConventions::gisTableName($tableName, $attr, $appName);
                   		if ( !$dalForApp->tableExists( $refTableName ) )
		                {
		                  
		                   $this->generate_gisTables($ins, $attr, $appName);
		                }
		                                  		
                   } else {
                   		$refTableName = YuppConventions::tableName( $refClass );
                   		$fks[] = array(
                             'name'    => DatabaseNormalization::simpleAssoc($attr), // nom_id, $attr = nom
                             'table'   => $refTableName,
                             'refName' => 'id' // Se que esta referencia es al atributo "id".
                            );
					}
                }
             }
             
             // FKs tablas intermedias HasMany
             $hasMany = $ins->getHasMany();
             
             foreach ( $hasMany as $attr => $assocClassName )
             {
                //Logger::getInstance()->pm_log("AssocClassName: $assocClassName, attr: $attr");
                
                if ( $ins->isOwnerOf( $attr ) ) // VERIFY, FIXME, TODO: Toma la asuncion de que el belongsTo es por clase. Podria generar un problema si tengo dos atributos de la misma clase pero pertenezco a uno y no al otro porque el modelo es asi.
                {
                   $hm_fks = array();
                   $hasManyTableName = YuppConventions::relTableName( $ins, $attr, new $assocClassName() );
       
                   // "owner_id", "ref_id" son FKs.
       
                   // ===============================================================================
                   // El nombre de la tabla owner para la FK debe ser el de la clase 
                   // donde se declara el attr hasMany,
                   // no para el ultimo de la estructura de MTI (como pasaba antes).
                   $classes = ModelUtils::getAllAncestorsOf( $ins->getClass() );
          
                   //Logger::struct( $classes, "Superclases de " . $ins1->getClass() );
                   
                   $instConElAtributoHasMany = $ins; // En ppio pienso que la instancia es la que tiene el atributo masMany.
                   foreach ( $classes as $aclass )
                   {
                      $_ins = new $aclass();
                      if ( $_ins->hasManyOfThis( $assocClassName ) )
                      {
                         //Logger::getInstance()->log("TIENE MANY DE " . $ins2->getClass());
                         $instConElAtributoHasMany = $_ins;
                         break;
                      }
                      
                      //Logger::struct( $ins, "Instancia de $aclass" );
                   }
                   // ===============================================================================
                   //TODO_GIS ERROR: insert or update on table "data_layer_elements_gis_persistent_object" violates foreign key constraint 
                   //"fk_gis_persistent_object_ref_id_id" DETAIL: Key (ref_id)=(1) is not present in table "gis_persistent_object".
                   if ($ins->getClass() != 'DataLayer' && $attr != 'elements') {
                   	
	                   $hm_fks[] = array(
	                             'name'    => "owner_id",
	                             'table'   => YuppConventions::tableName( $instConElAtributoHasMany->getClass() ), // FIXME: Genera link a gs (tabla de G1) aunque el atributo sea declarado en cs (tabla de C1). Esto puede generar problemas al cargar (NO PASA NADA AL CARGAR, ANDA FENOMENO!), aunque la instancia es la misma, deberia hacer la referencia a la tabla correspondiente a la instancia que declara el atributo, solo por consistencia y correctitud.
	                             'refName' => 'id' // Se que esta referencia es al atributo "id".
	                            );
	       
	                   $hm_fks[] = array(
	                             'name'    => "ref_id",
	                             'table'   => YuppConventions::tableName( $assocClassName ),
	                             'refName' => 'id' // Se que esta referencia es al atributo "id".
	                            );
                   }
                            
                   // Genera FKs
                   $dalForApp->addForeignKeys($hasManyTableName, $hm_fks);
                }
             } // foreach hasMany
             
             // Genera FKs
             $dalForApp->addForeignKeys($tableName, $fks);
             
          } // foreach PO
	}
   


}

?>