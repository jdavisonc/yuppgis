<?php

class GISCondition extends Condition {
	
	// Tipos de condiciones
	const GISTYPE_CONTAINS		= "giscondition.type.contains";
	const GISTYPE_ISCONTAINED	= "giscondition.type.iscontained";
	const GISTYPE_EQGEO			= "giscondition.type.equals";
	const GISTYPE_INTERSECTS	= "giscondition.type.intersects";
	const GISTYPE_DWITHIN		= "giscondition.type.dwithin";
	
	public static function getClassName() {
        return get_called_class();
    }
	
	private static function getGISTypes() {
      return array(
                self::GISTYPE_CONTAINS,
                self::GISTYPE_ISCONTAINED,
                self::GISTYPE_EQGEO,
                self::GISTYPE_INTERSECTS,
                self::GISTYPE_DWITHIN
             );
	} 

	public function setType( $type ) {
		if ( !in_array( $type, self::getGISTypes() ) )
			return parent::setType($type);
		$this->type = $type;
	}
	
	public function add( Condition $cond ) {
		throw new Exception("No corresponde");
	}
   
	public function getSubconditions() {
		throw new Exception("No corresponde");
	}
	
	private static function createGISConditionValue( $type, $alias, $attr, $refValue ) {
		$c = new GISCondition();
		$c->setType( $type );
		$c->setAttribute( $alias, $attr );
		$c->setReferenceValue( $refValue );
		return $c;
	}
	
	private static function createGISConditionAttribute( $type, $alias, $attr, $refAlias, $refAttr ) {
		$c = new GISCondition();
		$c->setType( $type );
		$c->setAttribute( $alias, $attr );
		$c->setReferenceAttribute( $refAlias, $refAttr );
		return $c;
	}

	public static function CONTAINS( $alias, $attr, $refValue ) {
		return self::createGISConditionValue(self::GISTYPE_CONTAINS, $alias, $attr, $refValue );
	}
	
	public static function ISCONTAINED( $alias, $attr, $refValue ) {
		return self::createGISConditionValue(self::GISTYPE_ISCONTAINED, $alias, $attr, $refValue );
	}
	
	public static function EQGEO( $alias, $attr, $refValue ) {
		return self::createGISConditionValue(self::GISTYPE_EQGEO, $alias, $attr, $refValue );
	}
	
	public static function EQGEOA( $alias, $attr, $refAlias, $refAttr ) {
       return self::createGISConditionAttribute(self::GISTYPE_EQGEO, $alias, $attr, $refAlias, $refAttr );
    }
	
	public static function INTERSECTS( $alias, $attr, $refValue ) {
		return self::createGISConditionValue(self::GISTYPE_INTERSECTS, $alias, $attr, $refValue );
	}
	
	public static function DWITHIN( $alias, $attr, $refValue, $distance) {
		// TODO_GIS: Terminar esta funcion, ver de crear un nuevo constructor de GISCondition
		//return self::createGISConditionAttribute(self::GISTYPE_DWITHIN, $alias, $attr, $refValue );
	}
	
}

?>