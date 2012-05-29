<?php

/**
 * Clase que representa una condicion geografica para consultas geograficas ({@link GISQuery}).
 * 
 * @package yuppgis.core.db.criteria2
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class GISCondition extends Condition {
	
	private $extraValueReference;
	
	/**
	 * Condicion geografica de pertenecia, A contiene a B?
	 */
	const GISTYPE_CONTAINS		= "giscondition.type.contains";
	
	/**
	 * Condicion geografica de pertenencia, A esta contenido en B?
	 */
	const GISTYPE_ISCONTAINED	= "giscondition.type.iscontained";
	
	/**
	 * Condicion geografica de igualdad, A es igual a B?
	 */
	const GISTYPE_EQGEO			= "giscondition.type.equals";
	
	/**
	 * Condicion geografica de interseccion, A intersecta a B?
	 */
	const GISTYPE_INTERSECTS	= "giscondition.type.intersects";
	
	/**
	 * Condicion geografica de distancia, A esta a una distancia X de B?
	 */
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
	
	public function setExtraValueReference($extraValueReference) {
		$this->extraValueReference = $extraValueReference;
	}
	
	public function getExtraValueReference() {
		return $this->extraValueReference;
	}
	
	private static function createGISConditionValueWithExtra( $type, $alias, $attr, $refValue, $extraValue) {
		$c = new GISCondition();
		$c->setType( $type );
		$c->setAttribute( $alias, $attr );
		$c->setReferenceValue( $refValue );
		$c->setExtraValueReference($extraValue);
		return $c;
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

	/**
	 * Crea una condicion de pertencia.
	 * 
	 * @param string $alias alias de la tabla.
	 * @param string $attr nombre del atributo a aplicar la condicion.
	 * @param Geometry $refValue valor con el cual va a comparar la condicion.
	 */
	public static function CONTAINS( $alias, $attr, $refValue ) {
		return self::createGISConditionValue(self::GISTYPE_CONTAINS, $alias, $attr, $refValue );
	}
	
	/**
	 * Crea una condicion de pertencia.
	 * 
	 * @param string $alias alias de la tabla.
	 * @param string $attr nombre del atributo a aplicar la condicion.
	 * @param Geometry $refValue valor con el cual va a comparar la condicion.
	 */
	public static function ISCONTAINED( $alias, $attr, $refValue ) {
		return self::createGISConditionValue(self::GISTYPE_ISCONTAINED, $alias, $attr, $refValue );
	}
	
	/**
	 * Crea una condicion de igualdad.
	 * 
	 * @param string $alias alias de la tabla.
	 * @param string $attr nombre del atributo a aplicar la condicion.
	 * @param Geometry $refValue valor con el cual va a comparar la condicion.
	 */
	public static function EQGEO( $alias, $attr, $refValue ) {
		return self::createGISConditionValue(self::GISTYPE_EQGEO, $alias, $attr, $refValue );
	}
	
	/**
	 * Crea una condicion de igualdad.
	 * 
	 * @param string $alias alias de la tabla.
	 * @param string $attr nombre del atributo a aplicar la condicion.
	 * @param string $refAlias alias de la segunda tabla.
	 * @param string $refAttr nombre del atributo dos a aplicar la condicion.
	 */
	public static function EQGEOA( $alias, $attr, $refAlias, $refAttr ) {
       return self::createGISConditionAttribute(self::GISTYPE_EQGEO, $alias, $attr, $refAlias, $refAttr );
    }

    /**
	 * Crea una condicion de interseccion.
	 * 
	 * @param string $alias alias de la tabla.
	 * @param string $attr nombre del atributo a aplicar la condicion.
	 * @param Geometry $refValue valor con el cual va a comparar la condicion.
	 */
	public static function INTERSECTS( $alias, $attr, $refValue ) {
		return self::createGISConditionValue(self::GISTYPE_INTERSECTS, $alias, $attr, $refValue );
	}
	
    /**
	 * Crea una condicion de distancia.
	 * 
	 * @param string $alias alias de la tabla.
	 * @param string $attr nombre del atributo a aplicar la condicion.
	 * @param Geometry $refValue valor con el cual va a comparar la condicion.
	 * @param int $distance valor con el cual va a comparar la condicion.
	 */
	public static function DWITHIN( $alias, $attr, $refValue, $distance) {
		return self::createGISConditionValueWithExtra(self::GISTYPE_DWITHIN, $alias, $attr, $refValue, $distance);
	}
	
}

?>