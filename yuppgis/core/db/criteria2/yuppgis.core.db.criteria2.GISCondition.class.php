<?php

class GISCondition extends Condition {
	
	// Tipos de condiciones
	const GISTYPE_CONTAINS		= "giscondition.type.contains";
	const GISTYPE_ISCONTAINED	= "giscondition.type.iscontained";
	
	public static function getClassName() {
        return get_called_class();
    }
	
	private static function getGISTypes() {
      return array(
                self::GISTYPE_CONTAINS,
                self::GISTYPE_ISCONTAINED
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
	
	private static function createGISConditionAttribute( $type, $alias, $attr, $refValue ) {
		$c = new GISCondition();
		$c->setType( $type );
		$c->setAttribute( $alias, $attr );
		$c->setReferenceValue( $refValue );
		return $c;
	}

	public static function CONTAINS( $alias, $attr, $refValue ) {
		return self::createGISConditionAttribute(self::GISTYPE_CONTAINS, $alias, $attr, $refValue );
	}
	
	public static function ISCONTAINED( $alias, $attr, $refValue ) {
		return self::createGISConditionAttribute(self::GISTYPE_ISCONTAINED, $alias, $attr, $refValue );
	}
	
}

?>