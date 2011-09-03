<?php

YuppLoader::load( 'core.db', 'DatabasePostgreSQL' );

class DatabasePostgisSQL extends DatabasePostgreSQL {
	
	public function evaluateAnyGISCondition( Condition $condition , $srid) {
		$where = "";
		switch ( $condition->getType() ) {
			case GISCondition::GISTYPE_ISCONTAINED:
				$where = $this->evaluateISCONTAINED( $condition, $srid );
				break;
			case GISCondition::GISTYPE_CONTAINS:
				$where = $this->evaluateCONTAINS( $condition, $srid );
				break;
			default:
				$where = parent::evaluateAnyCondition($condition);
		}
		return $where;
	}
	
	
	private function evaluateCONTAINS( GISCondition $condition, $srid ) {
		$refVal = $condition->getReferenceValue();
		$atr    = $condition->getAttribute();
      
		return "ST_Contains(" . $atr->alias.".".$atr->attr . ", ". $this->geomFromText($refVal, $srid) .") "; // -> geom columna geom de paciente_ubicacion_geo
	}
	
	private function evaluateISCONTAINED( GISCondition $condition, $srid ) {
		$refVal = $condition->getReferenceValue();
		$atr    = $condition->getAttribute();
      
		return "ST_Contains(" .  $this->geomFromText($refVal, $srid) . ", " . $atr->alias.".".$atr->attr . ") "; // -> geom columna geom de paciente_ubicacion_geo
	}
	
	public function geomFromText($wkt, $srid) {
		return "GeomFromText( '" . $wkt ."' , " . $srid.")";
	}
	
	public function asText($columnName) {
		return "AsText( '" . $columnName .")";
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