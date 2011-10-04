<?php

class GISQueryProcessor {
	
	public function process(Query $query) {
		
		//0. Obtengo atributos geograficos posibles en la query
		$geoAttrsOfQuery = self::getGeometryAttrs($query->getFrom());
		
		//1. Armamos las dos bolsas de Selects
		$processedSelects = self::processSelects($query->getSelect(), $geoAttrsOfQuery);
		
		//2. Se ejecuta query sobre DB (no Geo)
		$mainResult = self::executeSimpleQuery($query, $processedSelects->mainSelect);
		
		//3. Se arman las GISQuery con los res de 1 y 2 
		$gisQueries = self::createGISQuerys($query->getFrom(), $processedSelects->gisSelect, $mainResult, $processedSelects->tableAttrGeo);
		
		//4. 
		$gisResults = self::executeGISQuerys($gisQueries);
		
		//5. Se arma resultado final
		$finalResult = self::processResults($mainResult, $gisResults);
		
		return $finalResult;
	}
	
	/**
	 * A partir de los Selects y Froms originales genera los Selects para ir contra la base no geografica
	 * y los selects para ir contra la base geo.
	 * 
	 * @param Select	select de la consulta a procesar
	 * @param array		atributos geograficos posibles de la consulta
	 * @return ProcessedSelects		 objeto con select principal y geograficos
	 */
	private static function processSelects(Select $select, $geoAttrsOfQuery) {
		$processedSelects = new ProcessedSelects();

		foreach ($select->getAll() as $selectItem) {
			self::GISProjectionToProjection($geoAttrsOfQuery, $selectItem, $processedSelects);
		}
		
		return $processedSelects;
	}
	
	private static function GISProjectionToProjection($geoAttrsOfQuery, $selectItem, ProcessedSelects $processedSelects) {
		if ($selectItem instanceof SelectAttribute) {
			$alias = $selectItem->getAlias();
			$attrAlias = ($selectItem->getSelectItemAlias() == null)? $alias : $selectItem->getSelectItemAlias();
			$attrName = $selectItem->getAttrName();
			
			if (in_array($attrName, $geoAttrsOfQuery[$alias])) {

				$geoAttrAssoc = DatabaseNormalization::simpleAssoc($attrName);
				$attrMainProjection = new SelectAttribute($alias, $geoAttrAssoc); // Alias de atributo AttrName_id == geoAttrAssoc

				$processedSelects->mainSelect->add($attrMainProjection);
				
				$attrGisProjectionId = new SelectAttribute($alias, 'id');
				$attrGisProjectionGeom = new SelectAttribute($alias, 'geom', $attrGeo->alias);
				$processedSelects->gisSelect[$attrAlias] = new Select(array($attrGisProjectionId, $attrGisProjectionGeom));
				$processedSelects->tableAttrGeo[$attrAlias] = array( new Reference($geoAttrAssoc, $alias) );
				
			} else {
				$processedSelects->mainSelect->add($selectItem); 
			}
			
		} else if ($selectItem instanceof SelectAggregation) {
			
			if ($selectItem->getParam() instanceof GISFunction) { // Mismo caso que cuando es GISFunction, se ejecuta todo en GISDB
				
				$aggregationName = $selectItem->getName();
				$gisFunction = $selectItem->getParam();
				$processedSelects->tableAttrGeo[$aggregationName] = array();
				
				$newGisFunctionParams = array();
				
				foreach ($gisFunction->getParams() as $param) {
					if ($param instanceof SelectAttribute) {
						
						$alias = $param->getAlias();
						$attrName = $param->getAttrName();
						
						$geoAttrAssoc = DatabaseNormalization::simpleAssoc($attrName);
						$attrMainProjection = new SelectAttribute($alias, $geoAttrAssoc);
		
						$processedSelects->mainSelect->add($attrMainProjection);
						$processedSelects->tableAttrGeo[$aggregationName][] = new Reference($geoAttrAssoc, $alias);
						$newGisFunctionParams[] = new SelectAttribute($alias, 'geom'); // No interesa que tenga alias de selectItem
						
					} else { // SelectValue
						$newGisFunctionParams[] = $param;
					}
				}
				
				$newGisFunction = new GISFunction($gisFunction->getType(), $newGisFunctionParams);
				$processedSelects->gisSelect[$aggregationName] = new SelectAggregation($selectItem->getName(), $newGisFunction);
				
			} else {
				$processedSelects->mainSelect->add($selectItem); 
			}
				
		} else if ($selectItem instanceof SelectValue) {
			$processedSelects->mainSelect->add($selectItem); 
			
		} else if ($selectItem instanceof GISFunction) {
			
			// TODO_GIS: Refactor de codigo de cuando es Aggregation (linea 72)
			$gisFunction = $selectItem;
			$gisFunctionAlias = $selectItem->getSelectItemAlias();
			if ($gisFunctionAlias == null) {
				$gisFunctionAlias = 'function' . $processedSelects->functionCount++;
			}
			$processedSelects->tableAttrGeo[$gisFunctionAlias] = array();
			
			$newGisFunctionParams = array();
			
			foreach ($gisFunction->getParams() as $param) {
				if ($param instanceof SelectAttribute) {
					
					$alias = $param->getAlias();
					$attrName = $param->getAttrName();
					
					$geoAttrAssoc = DatabaseNormalization::simpleAssoc($attrName);
					$attrMainProjection = new SelectAttribute($alias, $geoAttrAssoc);
	
					$processedSelects->mainSelect->add($attrMainProjection);
					$processedSelects->tableAttrGeo[$gisFunctionAlias][] = new Reference($geoAttrAssoc, $alias);
					$newGisFunctionParams[] = new SelectAttribute($alias, 'geom'); // No interesa que tenga alias de selectItem
					
				} else { // SelectValue
					$newGisFunctionParams[] = $param;
				}
			}
			
			$processedSelects->gisSelect[$gisFunctionAlias] = new GISFunction($gisFunction->getType(), $newGisFunctionParams, $gisFunctionAlias);
			
		} else {
			throw new Exception("No implementado");
		}
	}
	
	/**
	 * Se encarga de ejececutar la query sobre la base de datos no geografica
	 */
	private static function executeSimpleQuery($mainQuery, $mainSelect) {

		$newFrom = self::GISFromToFrom($q->getFrom());
		$newCondition = self::GISConditionToCondition($q->getFrom(), $q->getWhere());
		
		$newQuery = new Query();
		$newQuery->setSelect($mainSelect);
		$newQuery->setFrom($newFrom);
		$newQuery->setCondition($newCondition);
		
		return $this->dal->query($newQuery);
	}
	
	/**
	 * Se encarga de armar las gis query
	 */
	private static function processGISQuerys() {
		//TODO_GIS
	}
	
	/**
	 * Se encarga de ejececutar las querys sobre la base de datos geografica
	 */
	private static function createGISQuerys($mainFrom, $gisSelects, $mainResult, $tableAttrGeo) {
		
		foreach ($gisSelects as $aliasSelect => $gisSelect) {
			$newGisQuery = new Query();
			$alias = array();
			foreach ($gisSelect->getAll() as $gisProjection) {
				// se arma from para ir contra la base geo
				$from = self::getFrom($mainFrom, $gisProjection->getAlias());
				if (!array_key_exists($from->alias, $newGisQuerygetFrom())) {
					$newGisQuery->addFrom($from->name, $from->alias);
				}
			}
			
			$orConditions = Condition::_OR(); 
			foreach ($mainResult as $row) {
				foreach ($gisSelect->getAll() as $gisProjection) {
					if ($gisProjection instanceof  SelectAttribute) {
						$ref = $tableAttrGeo[$aliasSelect][0]; // stdClass
						$condition = Condition::EEQ($ref->alias, 'id', $row[$ref->name]);
						$orConditions->add($condition);
					} else {
						//GIS Function
						$andConditions = Condition::_AND();
						for ($i = 0; $i < count($tableAttrGeo[$aliasSelect]); $i++) {
							$ref = $tableAttrGeo[$aliasSelect][$i];
							$condition = Condition::EEQ($ref->alias, 'id', $row[$ref->name]);
							$andConditions->add($condition);
						}
						
						if (count($andConditions->getSubconditions()) == 1) {
							// si la funcion recibe un solo parametro, no tiene sentido el and
							$orConditions->add($condition);
						} else {
							$orConditions->add($andCondition);
						}
					}
				}
			}
			
			if (count($orConditions->getSubconditions()) == 1) {
				// si el resultado era uno, no es un OR
				$condition = $orConditions->getSubconditions();
				$newGisQuery->setCondition($condition[0]);
			} else {
				$newGisQuery->setCondition($orConditions);
			}
			
			$gisQuerys[] = $newGisQuery;
		}
		
		return $gisQuerys; 
	}
	
	/**
	 * Se arma el resultado
	 */
	private static function processResults() {
		//TODO_GIS
	}
	
	
	//// Metodos auxiliares
	
	/**
	 * Retorna un array con los atributos geograficos de tablas en un From
	 * @param array $from
	 */
	private static function getGeometryAttrs(array $from) {
		$geoAttrsOfFroms = array();
		foreach ($froms as $from) {
			// TODO_GIS ver de mejorar el caso de que se realize From de las mismas tablas y no se realize
			// dos veces la busqueda
			$ins = $from->instance_or_class;
			if ( !is_object($ins) ) {
				$ins = new $instance_or_class(array(), true);
			}
			$geoAttrsOfFroms[$from->alias] = $ins->hasGeometryAttributes();
		}
		return $geoAttrsOfFroms;
	}
	
	private static function GISFromToFrom(array $gisFroms) {
		$from = array();
		foreach ($gisFroms as $gisFrom) {
			$f = new stdClass();
			$f->alias = $gisFrom->alias;
			$f->name = YuppConventions::tableName($gisFrom->instance_or_class);
			$from[] = $f;
		}
	return $from;
	}
	
	
	private static function GISConditionToCondition(array $froms, Condition $condition) {
		
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
	
}

//// Clases auxiliares

class ProcessedSelects {
	public $mainSelect;
	public $gisSelect;
	public $tableAttrGeo; // Por cada main select, guardo un array con todos los attrName_id que se usa xa hacer
	public $functionCount = 0;  
	
	public function __construct() {
		$this->mainSelect = new Select();
		$this->gisSelect = array();
		$this->tableAttrGeo = array();
	}
}

class Reference {
	public $name; // Nombre del atributo referenciado. Ejemplo: ubicacion_id
	public $alias; // Nombre de la tabla/objeto al que hace referencia
	
	public function __construct($name, $alias) {
		$this->name = $name;
		$this->alias = $alias;
	}
}

?>