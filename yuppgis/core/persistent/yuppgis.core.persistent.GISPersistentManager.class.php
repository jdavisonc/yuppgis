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
	 * @param String $tableNameOwner Nombre de la tabla del dueño
	 * @param String $attr Nombre del atributo
	 * @param class $persistentClass Clase o instancia a obtener
	 * @param int $id Identificador de la clase
	 */
	abstract public function get_gis_object( $tableNameOwner, $attr, $persistentClass, $id );

	/**
	 * Se salva en cascada con el dueño y su nombre de atributo.
	 * 
	 * @see PersistentManager::save_cascade_owner()
	 */
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
     * Salva el objeto geografico en la base de datos.
     * 
     * @param String $ownerTableName Tabla del propietario
     * @param String $attrNameAssoc Nombre del atributo de asociacion
     * @param PersistentObject $obj Objeto geografico a persistir
     */
	abstract protected function save_gis_object( $ownerTableName, $attrNameAssoc, PersistentObject $obj ); 

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
   			$this->delete_gis_object($owner, $attrNameAssoc, $assocObj, $logical);
    	} else {
    		return $this->delete($assocObj, $assocObj->getId(), $logical) ;
    	}
	}
	
	/**
	 * Elimina un elemento geografico.
	 * 
	 * @param String $owner Propietario
	 * @param String $attrNameAssoc Nombre del atributo de asociacion 
	 * @param Object $assocObj Objeto geografico asociado
	 * @param Boolean $logical Verdadero si es una eliminacion logica
	 */
	abstract protected function delete_gis_object($owner, $attrNameAssoc, $assocObj, $logical);
	
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
   	
}
?>