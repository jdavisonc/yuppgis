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
	
	public function evaluateGISQuery( Query $query, $srid )
	{
		$select = $this->evaluateSelect( $query->getSelect() ) . " ";
		$from   = $this->evaluateFrom( $query->getFrom() )   . " ";
		$where  = $this->evaluateGISWhere( $query->getWhere(), $srid )  . " ";
		$order  = $this->evaluateOrder( $query->getOrder() )  . " ";

		return $select . $from . $where . $order;
   }
   
	public function evaluateGISWhere( Condition $condition, $srid )
	{
		$where = "";
		if ($where !== NULL) {
			$where = "WHERE " . $this->evaluateAnyGISCondition( $condition, $srid );
		}
		return $where;
   }
	
}

?>