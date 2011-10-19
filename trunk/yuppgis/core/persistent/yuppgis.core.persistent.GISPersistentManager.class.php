<?php

YuppLoader :: load('core.db.criteria2', 'Query');

abstract class GISPersistentManager extends PersistentManager {
	
	public function init_dal( $appName ) {
		$this->init($appName);
	}
	
	/**
	 * Funcion que es llamada para inicializar el Persistent Manager.
	 * 
	 * @param String $appName Nombre de la aplicacion en ejecucion
	 */
	abstract protected function init( $appName );
	
	/**
	 * Obtiene un objeto geografico desde la base de datos.
	 * 
	 * @param String $ownerName Nombre del objeto dueno
	 * @param String $attr Nombre del atributo
	 * @param class $persistentClass Clase o instancia a obtener
	 * @param int $id Identificador de la clase
	 */
	abstract public function get_gis_object( $ownerName, $attr, $persistentClass, $id );

	/**
	 * Se salva en cascada con el dueño y su nombre de atributo.
	 * 
	 * @see PersistentManager::save_cascade_owner()
	 */
	public function save_cascade_owner( PersistentObject $owner, $attrNameAssoc, PersistentObject $obj, $sessId ) {
	   	if (is_subclass_of($obj, Geometry :: getClassName())) {
   			$obj->executeBeforeSave();
   			$this->save_gis_object(get_class($owner), $attrNameAssoc, $obj);
   			$obj->executeAfterSave();
   		} else {
   			parent::save_cascade_owner( $owner, $attrNameAssoc, $obj, $sessId ) ;
   		}
	}
	
	/**
     * Salva el objeto geografico en la base de datos.
     * 
     * @param String $ownerName Nombre del objeto dueno
     * @param String $attrNameAssoc Nombre del atributo de asociacion
     * @param PersistentObject $obj Objeto geografico a persistir
     */
	abstract protected function save_gis_object( $ownerName, $attrNameAssoc, PersistentObject $obj ); 

	/**
	 * Borra elementos en cascada.
	 *
	 * *Precaucion*: Solo se implemento el borrado en cascada para asociasiones hasOne
	 *
	 * @see PersistentManager::delete()
	 */
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
   			$this->delete_gis_object(get_class($owner), $attrNameAssoc, $assocObj, $logical);
    	} else {
    		return $this->delete($assocObj, $assocObj->getId(), $logical) ;
    	}
	}
	
	/**
	 * Elimina un elemento geografico.
	 * 
	 * @param String $ownerName Nombre del objeto dueno
	 * @param String $attrNameAssoc Nombre del atributo de asociacion 
	 * @param Object $assocObj Objeto geografico asociado
	 * @param Boolean $logical Verdadero si es una eliminacion logica
	 */
	abstract protected function delete_gis_object($ownerName, $attrNameAssoc, $assocObj, $logical);
	
	/**
	 * Ejecuta una consulta, si es una consulta geografica se procesa con un GISQueryProcessor.
	 * 
	 * @see PersistentManager::findByQuery()
	 */
	public function findByQuery(Query $q) {
		if ($q instanceof GISQuery) {
			return $this->findByGISQuery($q);
		} else {
			return parent::findByQuery($q);
		}
	}
	
	/**
	 * Ejecuta una consulta geografica (GISQuery) y retorna su resultado.
	 * 
	 * @param GISQuery $query
	 */
	abstract protected function findByGISQuery(GISQuery $query);
	
	
	/**
	 * Busca elementos $instance segun una condicion
	 * 
	 * *TODO_GIS*: Ver de suplantar por una GISQuery y asi borrar la logica de la funcion processCondition
	 * 
	 * @see PersistentManager::findBy()
	 */
	public function findBy( PersistentObject $instance, Condition $condition, ArrayObject $params ) {
		$newCondition = $this->processCondition($instance, $condition, $params);
		return parent::findBy($instance, $newCondition, $params);
	}

	/**
	 * Procesa una condicion pasada en la funcion findBy(), generando una nueva condicion a partir de la evaluacion
	 * de las condiciones geograficas.
	 * 
	 * @param PersistentObject $instance
	 * @param Condition $condition
	 * @param ArrayObject $params
	 */
	private function processCondition( PersistentObject $instance, Condition $condition, ArrayObject $params ) {
		if ( $condition instanceof GISCondition) {
			
			$query_res = $this->processGISCondition($instance, $condition, $params);
			
			// Construyo resultados para crear condicion IN
			$res = '';
			foreach ($query_res as $value ) {
				$res = $res . $value['id'] . ',';
			}
			
			$attr = $condition->getAttribute();
			$attr_id = DatabaseNormalization::simpleAssoc($attr->attr); // Se normaliza el nombre para obtener el nombre de la columna
			if ($res !== '') {
				return Condition::IN($attr->alias, $attr_id, substr($res, 0, -1));
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
					$newCondition->add($this->processCondition( $instance, $subconditions[$i], $params ));
				}
				return $newCondition;
			} else {
				return $condition;
			}
		}
	}
	
	/**
	 * Funcion que debe evaluar una GISCondition y retornar los objetos que cumplan con esta condicion.
	 * 
	 * @return Debe retornar un array, cuyas entradas deben estar numeradas, y cada una de ellas debe .
	 * 		   ser un array el cual contenga una key 'id' indicando el identificador del elemento que 
	 * 		   cumpla con la condicion.
	 */
	abstract protected function processGISCondition(PersistentObject $instance, GISCondition $condition, ArrayObject $params );
	
	public function generateAll( $appName ) {
		Logger::getInstance()->pm_log("GISPersistentManager::generateAll ======");

		// Todas las clases del primer nivel del modelo.
		$persitent = ModelUtils::getSubclassesOf( 'PersistentObject', $appName ); // FIXME> no es recursiva!
		$gisPersistent = ModelUtils::getSubclassesOf( GISPersistentObject::getClassName(), $appName ); // FIXME> no es recursiva!

		$A = array_merge($persitent, $gisPersistent);

		$this->generateAllByClass($A, $appName);

		$class = array();
		$class[] = 'Map';
		$class[] = 'DataLayer';
		$class[] = 'Tag';
		$class[] = 'GISPersistentObject';
		 
		$this->generateAllByClass($class, $appName);
	}
	
	/**
	 *
	 * @see PersistentManager::generateAll()
	 */
	private function generateAllByClass(array $A, $appName = null) {
		// Se utiliza luego para generar FKs.
		$generatedPOs = array();
		$dalForApp = $this->dal;

		foreach( $A as $clazz )
		{
			//TODO_GIS getMultipleTableInheritanceStructureToGenerateModel no es por app
			if ($clazz != GISPersistentObject::getClassName()) {
				$struct = MultipleTableInheritanceSupport::getMultipleTableInheritanceStructureToGenerateModel( $clazz );
			} else {
				$struct = array( GISPersistentObject::getClassName() => array());
			}

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
					if ($isGeometry) {
						$this->generate_gisTables($ins, $attr, $appName);

					} else if (!$isGeometry) {
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
	
	/**
	 * TODO_GIS
	 * @param unknown_type $owner
	 * @param unknown_type $attr
	 * @param unknown_type $appName
	 */
	protected abstract function generate_gisTables( PersistentObject $owner, $attr, $appName);

	 	
}
?>