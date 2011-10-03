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
		$gisQueries = self::createGISQuerys($query->getFrom(), $processedSelects->gisSelect, $mainResult);
		
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
			$attrAlias = $selectItem->getSelectItemAlias();
			$attrName = $selectItem->getAttrName();
			
			if (in_array($attrName, $geoAttrsOfQuery[$alias])) {

				$geoAttrAssoc = DatabaseNormalization::simpleAssoc($attrName);
				$attrMainProjection = new SelectAttribute($alias, $geoAttrAssoc, $attrAlias);

				$processedSelects->mainSelect->add($attrMainProjection);
				
				$attrGeo = new stdClass();
				$attrGeo->alias = ($attrAlias == null)? $attrName : $attrRes->getSelectItemAlias();
				$attrGeo->attrGeo = $geoAttrAssoc;
				$attrGeo->object = $alias;
				$attrGeo->attr = $attrName;
				
				$processedSelects->tableAttrGeo[$attrGeo->alias] = $attrGeo;
				
				$attrGisProjectionId = new SelectAttribute($alias, 'id');
				$attrGisProjectionGeom = new SelectAttribute($alias, 'geom', $attrGeo->alias);
				$processedSelects->gisSelect[] = new Select(array($attrGisProjectionId, $attrGisProjectionGeom));
				
			} else {
				return new SelectAttribute($alias, $selectItem->getAttrName(), $selectItem->getSelectItemAlias()); 
			}
			
		} else if ($selectItem instanceof SelectAggregation) {
			
			return new SelectAggregation($selectItem->getName(), 
				$this->GISProjectionToProjection($geoAttrsOfFroms, $selectItem->getParam(), $tableAttrGeo), 
				$selectItem->getSelectItemAlias());
				
		} else if ($selectItem instanceof SelectValue) {
			return new SelectValue($selectItem->getValue(), $selectItem->getSelectItemAlias());
			
		} else if ($selectItem instanceof GISFunction) {
			//TODO_GIS, esto generaría cosas del estilo Area(ubicacio_id), estas funciones debe de ir contra la otra base
			$params = array();
			foreach ($selectItem->getParams() as $param) {
				$params[] = $this->GISProjectionToProjection($geoAttrsOfFroms, $param, $tableAttrGeo);
			}
			return new GISFunction($selectItem->getType(), $params, $selectItem->getSelectItemAlias());
			
		} else {
			throw new Exception("No implementado");
		}
	}
	
	/**
	 * Se encarga de ejececutar la query sobre la base de datos no geografica
	 */
	private static function executeSimpleQuery() {
		//TODO_GIS
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
	private static function createGISQuerys() {
		//TODO_GIS
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
	
}

//// Clases auxiliares

class ProcessedSelects {
	public $mainSelect;
	public $gisSelect;
	public $tableAttrGeo;
	
	public function __construct() {
		$this->mainSelect = new Select();
		$this->gisSelect = array();
		$this->tableAttrGeo = array();
	}
}


?>