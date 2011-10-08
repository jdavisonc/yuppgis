<?php

YuppLoader::load( 'core.db', 'DatabasePostgreSQL' );

class DatabasePostgisSQL extends DatabasePostgreSQL {
	
	public function evaluateAnyGISCondition( Condition $condition , $srid) {
		$where = "";
		$refVal = $condition->getReferenceValue();
		$refAttr = $condition->getReferenceAttribute();
		$atr    = $condition->getAttribute();
		
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
			default:
				$where = parent::evaluateAnyCondition($condition);
		}
		return $where;
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
			return "SELECT *";
		} else {
			$res = "SELECT ";
			foreach ($projections as $proj) {
				if ($proj instanceof SelectGISAttribute) {
					$res .= $this->asText($proj->getAlias() . "." . $proj->getAttrName());
				} else if ($proj instanceof GISFunction) {
          			$res .= $this->evaluateGISFunction($proj, $srid );
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
	
	public function evaluateGISFunction( $projection, $srid ) {
		$params = $projection->getParams();
		
		switch ($projection->getType()) {
			case GISFunction::GIS_FUNCTION_DISTANCE:
				 return $this->evaluateDISTANCEFunction($params, $srid);
			default:
				throw new Exception("Function " . $projection->getYpe() . "not supported yet");
		}
	}
	
	private function evaluateDISTANCEFunction(array $params, $srid) {
		$function = "ST_Distance(";
		foreach ($params as $selectItem) {
			if ($selectItem instanceof SelectAttribute) {
				$function .= $selectItem->getAlias() . "." . $selectItem->getAttrName() . ",";
			} else if ($selectItem instanceof SelectValue) {
				$function .= $this->geomFromText($selectItem->getValue(), $srid) . ",";
			} else {
				throw new Exception("Type not supported for Function DISTANCE");
			}
		}
		
		return substr($function, 0, -1) . ")"; // Saca ultimo "; " y agrega el ultimo parentesis
	}

	
}

?>