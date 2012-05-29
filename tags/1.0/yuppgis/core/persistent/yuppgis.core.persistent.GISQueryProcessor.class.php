<?php

/**
 * Clase encargada de procesar las query geograficas (@link GISQuery)
 * 
 * @package yuppgis.core.persistent
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class GISQueryProcessor {

	private $dal = null;

	public function __construct(GISDAL $dal) {
		$this->dal = $dal;
	}

	/**
	 * Procesa una GISQuery y retorna el resultado.
	 *
	 * @param GISQuery $query
	 */
	public function process(GISQuery $query) {
		// Optimizacion para cuando es la misma conexion de DB que GISDB
		if ($this->dal->isTheSameConnectorThatGISDB()) {
			return $this->processQueryWithSameConnectorToGISDB($query);
		} else {
			return $this->processQueryWithDifferentConnectorToGISDB($query);
		}
	}

	/**
	 * Procesa una GISQuery con conectores a DB y GISDB distintos.
	 *
	 * @param GISQuery $query
	 */
	private function processQueryWithDifferentConnectorToGISDB(GISQuery $query) {
		//0. Obtengo atributos geograficos posibles en la query
		$geoAttrsOfQuery = $this->getGeometryAttrs($query->getFrom());

		//1. Armamos las dos bolsas de Selects
		$processedSelects = $this->processSelects($query, $geoAttrsOfQuery);

		//2. Se ejecuta query sobre DB (no Geo)
		$mainResult = $this->executeSimpleQuery($query, $processedSelects->mainSelect);

		//3. Se arman las GISQuery con los res de 1 y 2
		$gisQueries = $this->createGISQuerys($query->getFrom(), $processedSelects, $mainResult);

		//4.
		$gisResults = $this->executeGISQuerys($gisQueries);

		//5. Se arma resultado final
		return $this->processResults($query->getSelect(), $mainResult, $processedSelects, $gisResults);
	}

	/**
	 * Procesa una GISQuery con el mismo conector de DB que GISDB.
	 * *CUIDADO*: Esta funcion puede retornar menos resultados, ya que Yupp no soporta JOINS explicitos!!!!
	 *
	 * @param GISQuery $query
	 */
	private function processQueryWithSameConnectorToGISDB(GISQuery $query) {
		$geoAttrsOfQuery = $this->getGeometryAttrs($query->getFrom());

		$select = $query->getSelect();
		$from = $query->getFrom();

		if ($select->isEmpty()) { // caso select *
			$newProyections = $this->createAllProjections($select, $from);
			$select = new Select($newProyections);
		}
		$conditions = Condition::_AND();
		$newSelect = new Select();
		$aliasAttrUsed = array();

		foreach ($select->getAll() as $selectItem) {
			$this->GISProjectionToProjectionWithSameConnector($geoAttrsOfQuery, $selectItem, $newSelect, $aliasAttrUsed, $conditions);
		}

		$newFrom = $this->GISFromToFromWithSameConnector($from, $aliasAttrUsed);
		$newCondition = $this->GISConditionToConditionWithSameConnector($newFrom, $query->getWhere());
		if ($newCondition != null) {
			$conditions->add($newCondition);
		}
		$query->setSelect($newSelect);
		$query->setFrom($newFrom);
		$query->setCondition($conditions);

		return $this->dal->gis_query($query);
	}

	private function GISProjectionToProjectionWithSameConnector($geoAttrsOfQuery, $selectItem, $newSelect, &$aliasAttrUsed, $conditions) {
		if ($selectItem instanceof SelectAttribute) {
			$alias = $selectItem->getAlias();
			$attrName = $selectItem->getAttrName();
			$attrAlias = $selectItem->getSelectItemAlias();
			if (in_array($attrName, $geoAttrsOfQuery[$alias])) {
				$geoAttrAssoc = DatabaseNormalization::simpleAssoc($attrName);
				$conditions->add(Condition::EQA($alias, $geoAttrAssoc, $alias . $attrName, 'id')); // p.ubicacion_id = pgeom.id
				$newSelect->add(new SelectGIS($alias . $attrName, $attrAlias));
				if (array_key_exists($alias, $aliasAttrUsed)) {
					$aliasAttrUsed[$alias][] = $attrName;
				}else {
					$aliasAttrUsed[$alias] = array($attrName);
				}
			} else {
				$newSelect->add($selectItem);
			}
		} else if ($selectItem instanceof SelectAggregation) {
				
			if ($selectItem->getParam() instanceof GISFunction) {
				$gisFunction = $selectItem->getParam();
				$newGisFunctionParams = array();
				foreach ($gisFunction->getParams() as $param) {
					if ($param instanceof SelectAttribute) {

						$alias = $param->getAlias();
						$attrName = $param->getAttrName();
						$geoAttrAssoc = DatabaseNormalization::simpleAssoc();
						$conditions->add(Condition::EQA($alias, $geoAttrAssoc, $alias . $attrName, 'id')); // p.ubicacion_id = pgeom.id
						$newGisFunctionParams[] = new SelectAttribute($alias . $attrName, 'geom');
						if (array_key_exists($alias, $aliasAttrUsed)) {
							$aliasAttrUsed[$alias][] = $attrName;
						}else {
							$aliasAttrUsed[$alias] = array($attrName);
						}
					} else { // SelectValue
						$newGisFunctionParams[] = $param;
					}
				}
				$newSelect->add(new SelectAggregation($selectItem->getName(),new GISFunction($gisFunction->getType(), $newGisFunctionParams)));
			} else {
				$newSelect->add($selectItem);
			}
		} else if ($selectItem instanceof SelectValue) {
			$newSelect->add($selectItem);
		} else if ($selectItem instanceof GISFunction) {
			$gisFunction = $selectItem;
			$gisFunctionAlias = $selectItem->getSelectItemAlias();
			$newGisFunctionParams = array();
				
			foreach ($gisFunction->getParams() as $param) {
				if ($param instanceof SelectAttribute) {
					$alias = $param->getAlias();
					$attrName = $param->getAttrName();
					$geoAttrAssoc = DatabaseNormalization::simpleAssoc($attrName);
					$conditions->add(Condition::EQA($alias, $geoAttrAssoc, $alias . $attrName, 'id')); // p.ubicacion_id = pgeom.id
					$newGisFunctionParams[] = new SelectAttribute($alias . $attrName, 'geom');
					if (array_key_exists($alias, $aliasAttrUsed)) {
						$aliasAttrUsed[$alias][] = $attrName;
					}else {
						$aliasAttrUsed[$alias] = array($attrName);
					}
				} else { // SelectValue
					$newGisFunctionParams[] = $param;
				}
			}
			$newSelect->add(new GISFunction($gisFunction->getType(), $newGisFunctionParams, $gisFunctionAlias));
		}
	}

	/**
	 * A partir de los Selects y Froms originales genera los Selects para ir contra la base no geografica
	 * y los selects para ir contra la base geo.
	 *
	 * @param Select	select de la consulta a procesar
	 * @param from		From de la query a procesar
	 * @param array		atributos geograficos posibles de la consulta
	 * @return ProcessedSelects		 objeto con select principal y geograficos
	 */
	private function processSelects($query, $geoAttrsOfQuery) {

		$processedSelects = new ProcessedSelects();

		$select = $query->getSelect();
		$from = $query->getFrom();

		if ($select->isEmpty()) {
			// caso select *
			$newProyections = $this->createAllProjections($select, $from);
			$select = new Select($newProyections);
			$query->setSelect($select);
		}
		foreach ($select->getAll() as $selectItem) {
			$this->GISProjectionToProjection($geoAttrsOfQuery, $selectItem, $processedSelects);
		}

		return $processedSelects;
	}

	private function GISProjectionToProjection($geoAttrsOfQuery, $selectItem, ProcessedSelects $processedSelects) {

		if ($selectItem instanceof SelectAttribute) {
				
			$alias = $selectItem->getAlias();
			$attrName = $selectItem->getAttrName();
			$attrAlias = $selectItem->getSelectItemAlias();
				
			if (in_array($attrName, $geoAttrsOfQuery[$alias])) {

				$geoAttrAssoc = DatabaseNormalization::simpleAssoc($attrName);
				$geoAttrAlias = $alias . $geoAttrAssoc;
				$attrMainProjection = new SelectAttribute($alias, $geoAttrAssoc, $geoAttrAlias); // Alias de atributo AttrName_id == geoAttrAssoc

				$processedSelects->mainSelect->add($attrMainProjection);

				$attrGisProjection = new SelectGIS($alias, $attrAlias);
				$attrIdProjection = new SelectAttribute($alias, 'id', 'id1');
				
				$processedSelects->gisSelect[$attrAlias] = new Select(array($attrGisProjection, $attrIdProjection));
				$processedSelects->tableAttrGeo[$attrAlias] = array( new Reference($geoAttrAssoc, $alias, $geoAttrAlias) );

			} else {
				$processedSelects->mainSelect->add($selectItem);
			}
				
		} else if ($selectItem instanceof SelectAggregation) {
				
			if ($selectItem->getParam() instanceof GISFunction) { // Mismo caso que cuando es GISFunction, se ejecuta todo en GISDB

				$aggregationName = $selectItem->getName();
				$gisFunction = $selectItem->getParam();
				$processedSelects->tableAttrGeo[$aggregationName] = array();

				$newGisFunctionParams = array();
				$select = new Select();
				$i = 1;

				foreach ($gisFunction->getParams() as $param) {
					if ($param instanceof SelectAttribute) {

						$alias = $param->getAlias();
						$attrName = $param->getAttrName();

						$geoAttrAssoc = DatabaseNormalization::simpleAssoc($attrName);
						$geoAttrAlias = $alias . $geoAttrAssoc;
						$attrMainProjection = new SelectAttribute($alias, $geoAttrAssoc, $geoAttrAlias);

						$processedSelects->mainSelect->add($attrMainProjection);
						$processedSelects->tableAttrGeo[$aggregationName][] = new Reference($geoAttrAssoc, $alias, $geoAttrAlias);
						$newGisFunctionParams[] = new SelectAttribute($alias, 'geom'); // No interesa que tenga alias de selectItem

						$select->add(new SelectAttribute($alias, 'id', 'id' . $i++));

					} else { // SelectValue
						$newGisFunctionParams[] = $param;
					}
				}

				$select->add(new SelectAggregation(
				$selectItem->getName(),
				new GISFunction($gisFunction->getType(), $newGisFunctionParams)));
				$processedSelects->gisSelect[$aggregationName] = $select;

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
			$select = new Select();
			$i = 1;
				
			foreach ($gisFunction->getParams() as $param) {
				if ($param instanceof SelectAttribute) {
						
					$alias = $param->getAlias();
					$attrName = $param->getAttrName();
						
					$geoAttrAssoc = DatabaseNormalization::simpleAssoc($attrName);
					$geoAttrAlias = $alias . $geoAttrAssoc;
					$attrMainProjection = new SelectAttribute($alias, $geoAttrAssoc, $geoAttrAlias);

					$processedSelects->mainSelect->add($attrMainProjection);
					$processedSelects->tableAttrGeo[$gisFunctionAlias][] = new Reference($geoAttrAssoc, $alias, $geoAttrAlias);
					$newGisFunctionParams[] = new SelectAttribute($alias, 'geom'); // No interesa que tenga alias de selectItem
					$select->add(new SelectAttribute($alias, 'id', 'id' . $i++));
						
				} else { // SelectValue
					$newGisFunctionParams[] = $param;
				}
			}

			$select->add(new GISFunction($gisFunction->getType(), $newGisFunctionParams, $gisFunctionAlias));
			$processedSelects->gisSelect[$gisFunctionAlias] = $select;
				
		} else {
			throw new Exception("No implementado");
		}
	}

	/**
	 * Se encarga de ejececutar la query sobre la base de datos no geografica
	 */
	private function executeSimpleQuery($mainQuery, $mainSelect) {

		$newFrom = $this->GISFromToFrom($mainQuery->getFrom());
		$newCondition = $this->GISConditionToCondition($mainQuery->getFrom(), $mainQuery->getWhere());

		$newQuery = new Query();
		$newQuery->setSelect($mainSelect);
		$newQuery->setFrom($newFrom);
		$newQuery->setCondition($newCondition);

		return $this->dal->query($newQuery);
	}


	/**
	 * Se encarga de ejececutar las querys sobre la base de datos geografica
	 */
	private function createGISQuerys($mainFrom, ProcessedSelects $pocessedSelects, $mainResult) {

		$gisSelects = $pocessedSelects->gisSelect;
		$tableAttrGeo = $pocessedSelects->tableAttrGeo;

		$gisQueries = array();
		foreach ($gisSelects as $aliasSelect => $gisSelect) {
			$newGisQuery = new Query();
			$alias = array();
				
			foreach ($gisSelect->getAll() as $gisProjection) {
				// se arma from para ir contra la base geo

				$isSelectAttribute = ($gisProjection instanceof SelectAttribute);

				if ($isSelectAttribute) {
					$resAdd = $this->addFromsInGisQueries($newGisQuery, $aliasSelect, $mainFrom, $gisProjection->getAlias(), $tableAttrGeo, $alias);
					$alias = $resAdd[0];
						
					if ($resAdd[1]) {
						// se agrego el from
						$newGisQuery->setSelect($gisSelect);
					}
						
				} else {
					//Caso de funciones
					$addGisSelect = false;
					foreach ($gisProjection->getPArams() as $param) {

						if (! ($param instanceof SelectValue)) {
							$resAdd = $this->addFromsInGisQueries($newGisQuery, $aliasSelect, $mainFrom, $param->getAlias(), $tableAttrGeo, $alias);
							$alias = $resAdd[0];
								
							if ($resAdd[1]) {
								$addGisSelect = true; // Si solo hace falta agregar un from, hay que agregar el select
							}
						}
					}

					if ($addGisSelect) {
						$newGisQuery->setSelect($gisSelect);
					}
				}
			}
				
			$orConditions = Condition::_OR();
			$gisOrderProjections = $this->orderProjections($gisSelect->getAll());
			foreach ($mainResult as $row) {
				$alias = array();
				foreach ($gisOrderProjections as $gisProjection) {
						
					if ($this->isFromProcessed($gisProjection, $mainFrom, $alias)) {
						continue;
					}
						
					if ($gisProjection instanceof  SelectAttribute) {
						$ref = $tableAttrGeo[$aliasSelect][0]; // stdClass
						$condition = Condition::EEQ($ref->alias, 'id', $row[$ref->attrAlias]);
						$orConditions->add($condition);
					} else {
						//GIS Function
						$andConditions = Condition::_AND();
						for ($i = 0; $i < count($tableAttrGeo[$aliasSelect]); $i++) {
							$ref = $tableAttrGeo[$aliasSelect][$i];
							$condition = Condition::EEQ($ref->alias, 'id', $row[$ref->attrAlias]);
							$andConditions->add($condition);
						}

						if (count($andConditions->getSubconditions()) == 1) {
							// si la funcion recibe un solo parametro, no tiene sentido el and
							$orConditions->add($condition);
						} else {
							$orConditions->add($andConditions);
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
				
			$gisQueries[$aliasSelect] = $newGisQuery;
		}

		return $gisQueries;
	}
	
	/**
	 * 
	 * Ordena las proyecciones colocando las funciones antes que los selectitems
	 * @param unknown_type $gisProjection
	 */
	private function orderProjections($gisProjection) {
		$gisFunctions = array();
		$othersProjections = array();
		foreach ($gisProjection as $proj) {
			if ($proj instanceof  SelectAttribute) {
				$othersProjections[] = $proj;		
			} else {
				$gisFunctions[] = $proj;
			}
		}
		
		return array_merge($gisFunctions, $othersProjections);
	}

	private function isFromProcessed($gisProjection, $mainFrom, &$usedAlias) {
		$isSelectAttribute = ($gisProjection instanceof  SelectAttribute);
		if ($isSelectAttribute) {
			$from = $this->getFrom($mainFrom, $gisProjection->getAlias());
			if (in_array($from->alias, $usedAlias)) {
				return true;
			}
			$usedAlias[] = $from->alias;
		} else {
			//Caso funciones
			foreach ($gisProjection->getParams() as $param) {
				if (! ($param instanceof SelectValue)) {
					$from = $this->getFrom($mainFrom, $param->getAlias());
					if (in_array($from->alias, $usedAlias)) {
						return true;
					}
					$usedAlias[] = $from->alias;
				}
			}
		}
		return false;
	}

	private function addFromsInGisQueries($gisQuery, $aliasSelect, $mainFrom, $alias, $tableAttrGeo, $usedAlias) {
		$from = $this->getFrom($mainFrom, $alias);
		if (!in_array($from->alias, $usedAlias)) {
				
			$usedAlias[] = $from->alias;
				
			$ref = $tableAttrGeo[$aliasSelect][0]; // stdClass
			$tableName = YuppConventions::tableName($from->instance_or_class);
			$attrName = DatabaseNormalization::getSimpleAssocName($ref->name);
			$gisTableName = YuppGISConventions::gisTableName($tableName, $attrName);
				
			$gisQuery->addFrom($gisTableName, $from->alias);
				
			return array($usedAlias, true);
		}
		//ya se agrego la tabla con ese alias
		return array($usedAlias, false);;
	}

	private function executeGISQuerys($gisQueries) {
		$gisResults = array();

		foreach ($gisQueries as $aliasSelect => $gisQuery) {
			$result = $this->dal->gis_query($gisQuery);
			$newGisResult = array();
				
			//se indexan los gis results por id para joinear con los main result
			foreach ($result as $row) {
				$key = $row['id1'];
				unset($row['id1']);

				if (array_key_exists('id2', $row)) {
					$key .= '$' . $row['id2'];
					unset($row['id2']);
				}

				$newGisResult[$key] = $row;
			}
				
			$gisResults[$aliasSelect] = $newGisResult;
		}

		return $gisResults;
	}
	/**
	 * Se arma el resultado, mergeando el resultado de la base no geo y la base geo
	 */
	private function processResults($mainSelect, $mainResult, ProcessedSelects $processedSelects, $gisResults) {

		$tableAttrGeo = $processedSelects->tableAttrGeo;
		$queryResult = array();

		foreach ($mainResult as $result) {
			$mergeRow = array();
			foreach ($mainSelect->getAll() as $mainProjection) {

				$alias = $mainProjection->getSelectItemAlias();
				if (array_key_exists($alias, $result)) {
					$mergeRow[$alias] = $result[$alias];
				} else {
						
					// Caso geografico
						
					//TODO_GIS, caso de $key null y que no exista en gisResults
					$key = $this->getKeyFromTableAttrGeo($tableAttrGeo, $alias, $result);
					//Se vuelve a pedir alias, el gis esta indexado por alias el cual contiene
					//segun una key un conjunto de atributos de los cuales nos interesa el atributo alias
					$mergeRow[$alias] = $gisResults[$alias][$key][$alias];
				}
			}
				
			$queryResult[] = $mergeRow;
		}

		return $queryResult;
	}


	//// Metodos auxiliares


	private function createAllProjections($select, $froms) {

		$usedAttrs = array();
		$newSelectItems = array();

		foreach ($froms as $from) {
			$ins = $from->instance_or_class;
			if ( !is_object($ins) ) {
				$ins = new $ins(array(), true);
			}
				
			foreach ($ins->getAttributeTypes() as $attrName => $attrType) {
				if (in_array($attrName, $usedAttrs)) {
					throw new Exception("Columna ". $attrName . "es ambigua");
				}
				$usedAttrs[] = $attrName;
				$newSelectItems[] = new SelectAttribute($from->alias, $attrName);
			}
				
			foreach ($ins->getHasOne() as $attrName => $attrType) {
				if (in_array($attrName, $usedAttrs)) {
					throw new Exception("Columna ". $attrName . "es ambigua");
				}
				$usedAttrs[] = $attrName;
				$newSelectItems[] = new SelectAttribute($from->alias, $attrName);
			}
				
		}

		return $newSelectItems;
	}

	/**
	 * Funcio que retorna la key para ir a buscar al gis result
	 * @param $tableAttrGeo
	 * @param $alias
	 * @param $result
	 */
	private function getKeyFromTableAttrGeo ($tableAttrGeo, $alias, $result) {
		$key = "";

		foreach ($tableAttrGeo[$alias] as $ref) {
			$key .=  $result[$ref->attrAlias] . "$";
		}

		//quitamos el ultimo $
		return substr($key, 0, -1);

	}
	/**
	 * Retorna un array con los atributos geograficos de tablas en un From
	 * @param array $from
	 */
	private function getGeometryAttrs(array $froms) {
		$geoAttrsOfFroms = array();
		foreach ($froms as $from) {
			// TODO_GIS ver de mejorar el caso de que se realize From de las mismas tablas y no se realize
			// dos veces la busqueda
			$ins = $from->instance_or_class;
			if ( !is_object($ins) ) {
				$ins = new $ins(array(), true);
			}
			$geoAttrsOfFroms[$from->alias] = $ins->hasGeometryAttributes();
		}
		return $geoAttrsOfFroms;
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

	private function GISFromToFromWithSameConnector(array $from, $aliasAttrUsed) {
		$newFrom = array();
		foreach ($from as $gisFrom) {
			$f = new stdClass();
			$f->alias = $gisFrom->alias;
			$f->name = YuppConventions::tableName($gisFrom->instance_or_class);
			$newFrom[] = $f;
				
			if (array_key_exists($f->alias, $aliasAttrUsed)) {
				foreach ($aliasAttrUsed[$f->alias] as $alias => $attrName) {
					$fgeom = new stdClass();
					$fgeom->name = YuppGISConventions::gisTableName($f->name, $attrName);
					$fgeom->alias = $gisFrom->alias . $attrName;
					$newFrom[] = $fgeom;
				}
			}
		}
		return $newFrom;
	}

	private function GISConditionToConditionWithSameConnector(array $froms, $condition) {
		if ($condition !== null) {
			if ($condition instanceof GISCondition) {
				$attr = $condition->getAttribute();
				$fromSelect = $this->getFrom($froms, $attr->alias);
				$gisCondition = new GISCondition();
				$gisCondition->setType($condition->getType());
				$gisCondition->setAttribute($fromSelect->alias . $attr->attr, 'geom');
				$gisCondition->setExtraValueReference($condition->getExtraValueReference());

				if ($condition->getReferenceAttribute() !== null) {
					$attr2 = $condition->getReferenceAttribute();
					$fromSelect2 = $this->getFrom($froms, $attr2->alias);
					$gisCondition->setReferenceAttribute($fromSelect2->alias . $attr2->attr, 'geom');
				} else {
					$attrGeo = WKTGEO::toText( $condition->getReferenceValue() );
					$gisCondition->setReferenceValue($attrGeo);
				}
				return $gisCondition;
			} else {
				if ( $condition->getType() == Condition::TYPE_AND  || $condition->getType() == Condition::TYPE_OR ||
				$condition->getType() == Condition::TYPE_NOT ) {
					$newCondition = new Condition();
					$newCondition->setType($condition->getType());
					$subconditions = $condition->getSubconditions();
					for ($i = 0; $i < count($subconditions); $i++) {
						$newCondition->add($this->GISConditionToConditionWithSameConnector( $froms, $subconditions[$i] ));
					}
					return $newCondition;
				} else {
					return $condition;
				}
			}
		}
	}

	private function GISConditionToCondition(array $froms, $condition) {
		if ($condition !== null) {
			if ($condition instanceof GISCondition) {

				$attr = $condition->getAttribute();

				$fromSelect = $this->getFrom($froms, $attr->alias);

				// Es gis condition, tener cuidado que puede tener subcondiciones comunes
				$tableName = YuppConventions::tableName($fromSelect->instance_or_class);
				$gisTableName = YuppGISConventions::gisTableName($tableName, $attr->attr);

				$gisCondition = new GISCondition();
				$gisCondition->setType($condition->getType());
				$gisCondition->setAttribute($fromSelect->alias, 'geom'); // Se establece el alias de la tabla (Ver query mas abajo) y nombre de la columna
				$gisCondition->setExtraValueReference($condition->getExtraValueReference());

				$query = new Query();
				$query->addFrom($gisTableName, $fromSelect->alias);
				$query->addProjection($fromSelect->alias, 'id', 'id1');

				if ($condition->getReferenceAttribute() !== null) {
					$attr2 = $condition->getReferenceAttribute();
					$fromSelect2 = $this->getFrom($froms, $attr2->alias);
						
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

				$res = $this->createValuesStringFromKeyOnQuery($query_res, 'id1');
				$res2 = null;
				if ($condition->getReferenceAttribute() !== null) {
					$res2 = $this->createValuesStringFromKeyOnQuery($query_res, 'id2');
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
	}

	/**
	 * Retorna el objeto From dado un alias en un array de From
	 * @param $from
	 * @param $alias
	 */
	private function getFrom(array $from, $alias) {
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
	private function createValuesStringFromKeyOnQuery($query_res, $key) {
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
	public $attrAlias; //Nombre de la columna

	public function __construct($name, $alias, $attrAlias) {
		$this->name = $name;
		$this->alias = $alias;
		$this->attrAlias = $attrAlias;
	}
}

?>