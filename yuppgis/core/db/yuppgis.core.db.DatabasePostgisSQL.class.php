<?php

YuppLoader::load( 'core.db', 'DatabasePostgreSQL' );

class DatabasePostgisSQL extends DatabasePostgreSQL {
	
	public function evaluateAnyCondition( Condition $condition ) {
		$where = "";
		switch ( $condition->getType() ) {
			case GISCondition::GISTYPE_ISCONTAINED:
				$where = $this->evaluateISCONTAINED( $condition );
				break;
			default:
				$where = parent::evaluateAnyCondition($condition);
		}
		
		return $where;
	}
	
	private function evaluateISCONTAINED( GISCondition $condition ) {
		$refVal = $condition->getReferenceValue();
		$refAtr = $condition->getReferenceAttribute();
		$atr    = $condition->getAttribute();
      
		// TODO_GIS: Ejemplo 
		// 	return "	ST_Contains(p.geom, AsGeom('POINT(10 10)'))	" -> geom columna geom de paciente_ubicacion_geo
		// Tambien tener cuidado que se puede hacer algo asi:
		// ST_CONTAINS (paciente.geom, medicos.geom) -> los pacientes en el area de medicos
		/*if ( $refAtr !== NULL ) {
			return $atr->alias.".".$atr->attr ."=". $refAtr->alias.".".$refAtr->attr; // a.b = c.d
		} else {
            return $atr->alias.".".$atr->attr ."=". $this->evaluateReferenceValue( $refVal ); // a.b = 666
		}*/
		return '';
		
		throw new Exception("Uno de valor o atributo de referencia debe estar presente. " . __FILE__ . " " . __LINE__);
	}
	
	
}

?>