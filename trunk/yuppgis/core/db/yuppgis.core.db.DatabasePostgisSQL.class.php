<?php

YuppLoader::load( 'core.db', 'DatabasePostgreSQL' );

class DatabasePostgisSQL extends DatabasePostgreSQL {
	
	public function evaluateAnyGISCondition( Condition $condition , $srid ) {
		$where = "";
		$refVal = $condition->getReferenceValue();
		$refAttr = $condition->getReferenceAttribute();
		$atr = $condition->getAttribute();
		
		switch ( $condition->getType() ) {
			case GISCondition::GISTYPE_ISCONTAINED:
				$where = $this->evaluateISCONTAINED( $atr, $refVal, $srid );
				break;
			case GISCondition::GISTYPE_CONTAINS:
				$where = $this->evaluateCONTAINS( $atr, $refVal, $srid );
				break;
			case GISCondition::GISTYPE_EQGEO:
				$where = $this->evaluateEQGEO( $atr, $refVal, $refAttr, $srid );
				break;
			case GISCondition::GISTYPE_INTERSECTS:
				$where = $this->evaluateINTERSECTS( $atr, $refVal, $srid );
				break;
			case GISCondition::GISTYPE_DWITHIN:
				$extra = $condition->getExtraValueReference();
				$where = $this->evaluateDWITHIN( $atr, $refVal, $extra, $srid);
				break;
			case Condition::TYPE_NOT:
				$where = $this->evaluateNOTGISCondition( $condition, $srid );
				break;
			case Condition::TYPE_AND:
				$where = $this->evaluateANDGISCondition( $condition, $srid );
				break;
			case Condition::TYPE_OR:
				$where = $this->evaluateORGISCondition( $condition, $srid );
				break;
			default:
				$where = parent::evaluateAnyCondition($condition);
		}
		return $where;
	}
	
	private function evaluateDWITHIN( $attribute, $refValue, $extra, $srid ) { 
		return "ST_DWithin(" . $attribute->alias . "." . $attribute->attr . ", ". $this->geomFromText($refValue, $srid) .", " . $extra .") "; 
	}
	
	private function evaluateINTERSECTS( $attribute, $refValue, $srid ) { 
		return "ST_Intersects(" . $attribute->alias . "." . $attribute->attr . ", ". $this->geomFromText($refValue, $srid) .") "; 
	}
	
	private function evaluateEQGEO( $attribute, $refValue, $refAttr, $srid ) {
		if ($refValue != null) {
			return "ST_Equals(" . $attribute->alias . "." . $attribute->attr . ", ". $this->geomFromText($refValue, $srid) .") ";
		} else {
			return "ST_Equals(" . $attribute->alias . "." . $attribute->attr . ", " . $refAttr->alias . "." . $refAttr->attr . ") ";
		}
	}
	
	private function evaluateCONTAINS( $attribute, $refValue, $srid ) { 
		return "ST_Contains(" . $attribute->alias . "." . $attribute->attr . ", ". $this->geomFromText($refValue, $srid) .") ";
	}
	
	private function evaluateISCONTAINED( $attribute, $refValue, $srid ) { 
		return "ST_Contains(" .  $this->geomFromText($refValue, $srid) . ", " . $attribute->alias . "." . $attribute->attr . ") ";
	}
	
	public function geomFromText($wkt, $srid) {
		return "GeomFromText( '" . $wkt ."' , " . $srid.")";
	}
	
	public function asText($columnName) {
		return "AsText(" . $columnName .")";
	}
	
	public function evaluateGISQuery( Query $query, $srid ) {
		$select = $this->evaluateGISSelect($query->getSelect(), $srid ) . " ";
		$from   = $this->evaluateFrom($query->getFrom()) . " ";
		$where  = $this->evaluateGISWhere($query->getWhere(), $srid) . " ";
		$order  = $this->evaluateOrder($query->getOrder()) . " ";
		
		return $select . $from . $where . $order;
	}
	
	public function evaluateGISWhere( $condition, $srid ) {
		$where = "";
		if ($condition !== NULL) {
			$where = "WHERE " . $this->evaluateAnyGISCondition( $condition, $srid );
		}
		return $where;
   }
	
	public function evaluateGISSelect( $select, $srid  ) {
		$projections = $select->getAll();
		if (count($projections) == 0) {
			return "SELECT * ";
		} else {
			$res = "SELECT ";
			$i = 1;
			foreach ($projections as $proj) {
				if ($proj instanceof SelectGIS) {
					$res .= $this->selectGisToSelect($proj->getAlias(), $i);
					$i++;
				} else if ($proj instanceof GISFunction) {
          			$res .= $this->evaluateGISFunction($proj, $srid );
          			if ($proj->returnGeometry()) {
          				$i++; //se debe incrementar la key
          			}
				} else if ($proj instanceof SelectAttribute) {
					$res .= $proj->getAlias() . "." . $proj->getAttrName();
				} else if ($proj instanceof SelectAggregation) {
					$res .= $proj->getName() . "(". $proj->getParam()->getAlias() . "." . $proj->getParam()->getAttrName() .")";
				}
				if ($proj->getSelectItemAlias() != null) {
					$res .= " as " . $proj->getSelectItemAlias();
				}
				$res .= ", ";
			}
			return substr($res, 0, -2); // Saca ultimo "; "
		}
	}
	
	private function selectGisToSelect($alias, $i) {
		$res = '';
		$res .= $alias . "." . "id as gisid" . $i ." , ";
		$res .= $alias . "." . "uiproperty as gisuiproperty" . $i ." , ";;
		$res .= $this->asText($alias . "." . "geom");
		return $res;
	}
	
	
	
	public function evaluateGISFunction( $projection, $srid ) {
		$params = $projection->getParams();
		$returnGeometry = $projection->returnGeometry();
		
		switch ($projection->getType()) {
			case GISFunction::GIS_FUNCTION_DISTANCE:
				 return $this->evaluateGISFunctionName('ST_Distance', $returnGeometry,$params, $srid);
			case GISFunction::GIS_FUNCTION_AREA:
				 return $this->evaluateGISFunctionName('ST_Area', $returnGeometry, $params, $srid);
			case GISFunction::GIS_FUNCTION_INTERSECTION:
				 return $this->evaluateGISFunctionName('ST_Intersection', $returnGeometry, $params, $srid);
			case GISFunction::GIS_FUNCTION_UNION:
				 return $this->evaluateGISFunctionName('ST_Union', $returnGeometry, $params, $srid);
			case GISFunction::GIS_FUNCTION_DIFFERENCE:
				 return $this->evaluateGISFunctionName('ST_Difference', $returnGeometry, $params, $srid);
			default:
				throw new Exception("Function " . $projection->getYpe() . "not supported yet");
		}
	}
	
	private function evaluateGISFunctionName($name, $returnGeometry, array $params, $srid) {
		$function = $name . '(';
		foreach ($params as $selectItem) {
			if ($selectItem instanceof SelectAttribute) {
				$function .= $selectItem->getAlias() . "." . $selectItem->getAttrName() . ",";
			} else if ($selectItem instanceof SelectValue) {
				$function .= $this->geomFromText($selectItem->getValue(), $srid) . ",";
			} else {
				throw new Exception('Type not supported for Function ' . $name);
			}
		}
		
		$function = substr($function, 0, -1) . ")"; // Saca ultimo "; " y agrega el ultimo parentesis
		if ($returnGeometry) {
			return $this->asText($function);
		} else {
			return $function;
		}
	}

	public function evaluateNOTGISCondition( Condition $condition, $srid ) {
		$conds = $condition->getSubconditions();
		if ( count($conds) !== 1 ) {
			throw new Exception("Not debe tener exactamente una condicion para evaluarse. ".__FILE__." ".__LINE__);
		}
		
		return "NOT (" . $this->evaluateAnyGISCondition( $conds[0], $srid ) . ") ";
	}
   
	public function evaluateANDGISCondition( Condition $condition, $srid ) {
		$conds = $condition->getSubconditions();
		$res = "(";
		$i = 0;
		$condCount = count( $conds );
		
		foreach ( $conds as $cond ) {
			$res .= $this->evaluateAnyGISCondition( $cond, $srid );
			if ($i+1 < $condCount) {
				$res .= " AND ";
			}
			$i++;
		}
		return $res . ")";
	}
	
	public function evaluateORGISCondition( Condition $condition, $srid ) {
		$conds = $condition->getSubconditions();
		$res = "(";
		$i = 0;
		$condCount = count( $conds );
		
		foreach ( $conds as $cond ) {
			$res .= $this->evaluateAnyGISCondition( $cond, $srid );
			if ($i+1 < $condCount) {
				$res .= " OR ";
			}
			$i++;
		}
		return $res . ")";
	}
	
	public function getDBGisType( $type ) {
		switch ($type) {
			case GISDatatypes::POINT:
				return 'POINT';
			case GISDatatypes::LINESTRING:
			case GISDatatypes::LINERING:
			case GISDatatypes::LINE:
				return 'LINESTRING';
			case GISDatatypes::MULTISURFACE:
			case GISDatatypes::MULTIPOLYGON:
				return 'MULTIPOLYGON';
			case GISDatatypes::POLYGON:
				return 'POLYGON';
			default:
				throw new Exception("DatabasePosgisSQL.getDBGISType: el tipo ($type) no esta definido.");
   		}
   }
	
}

?>