<?php

/**
 * 
 * Clase que representa una funcion geografica de una consulta geografica ({@link GISQuery}).
 * 
 * @package yuppgis.core.db.criteria2
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class GISFunction extends SelectItem {
	
	private $type;
	private $params;
   
	/**
	 * Funcion geografica de distancia entre dos geomentrias
	 * Ver: http://postgis.refractions.net/documentation/manual-1.3/ch06.html
	 */
	const GIS_FUNCTION_DISTANCE 	= "gisfunction.type.distance";
	
	/**
	 * Funcion geografica que calcula el Area de una geometria ({@link Polygon} o {@link MultiPolygon})
	 * Ver: http://postgis.refractions.net/documentation/manual-1.3/ch06.html
	 */
	const GIS_FUNCTION_AREA 		= "gisfunction.type.area";
	
	/**
	 * Funcion geografica que retorna la gemotria resultante de intersectar dos geometrias
	 * Ver: http://postgis.refractions.net/documentation/manual-1.3/ch06.html
	 */
	const GIS_FUNCTION_INTERSECTION	= "gisfunction.type.intersection";
	
	/**
	 * Funcion geografica que retorna la gemotria resultante de unir dos geometrias
	 * Ver: http://postgis.refractions.net/documentation/manual-1.3/ch06.html
	 */
	const GIS_FUNCTION_UNION		= "gisfunction.type.union";
	
	/**
	 * Funcion geografica que retorna la diferencia entre dos geometrias.
	 * Ver: http://postgis.refractions.net/documentation/manual-1.3/ch06.html
	 */
	const GIS_FUNCTION_DIFFERENCE	= "gisfunction.type.difference";
	
	public function __construct($type, $params, $selectItemAlias = null) {
		$this->setType($type);
		$this->params = $params;
		parent::__construct($selectItemAlias);
	}
      
	private static function getGISTypes() {
      return array(
                self::GIS_FUNCTION_DISTANCE,
                self::GIS_FUNCTION_AREA,
                self::GIS_FUNCTION_INTERSECTION,
                self::GIS_FUNCTION_UNION,
                self::GIS_FUNCTION_DIFFERENCE
             );
	}
	
	private static function getGISTypesThatReturnGeometry() {
      return array(
                self::GIS_FUNCTION_INTERSECTION,
                self::GIS_FUNCTION_UNION,
                self::GIS_FUNCTION_DIFFERENCE
             );
	}
	
	/**
	 * Devuelve true si la @link GISFunction retorna una nueva geometria (@link Geometry)
	 */
	public function returnGeometry() {
		return in_array($this->type, self::getGISTypesThatReturnGeometry());
	}
	
	public function setParams( array $params ) {
		$this->params = $params;
	}

	public function setType( $type ) {
		if ( !in_array( $type, self::getGISTypes() ) )
			return parent::setType($type);
		$this->type = $type;
	}
	
	public function getType(){
		return $this->type;
	}
	
	public function getParams(){
		return $this->params;
	}
	
	private static function createGISFunction( $type, $alias, $attr, $alias2, $attr2, $value = null, $selectItemAlias ) {
		$params = array();
		if ($alias != null) {
			$params[] = new SelectAttribute($alias, $attr);
		}
		if ($alias2 != null) {
			$params[] = new SelectAttribute($alias2, $attr2);
		} else if ($value != null) {
			$params[] = new SelectValue($value);
		}
		return new GISFunction($type, $params, $selectItemAlias);
	}
	
	/**
	 * Crea una funcion de distancia entre dos atributos (A y B)
	 * 
	 * @param string $alias alias de la tabla (tabla que contiene a A)
	 * @param string $attr nombre del atributo geografico para usar en la funcion (A)
	 * @param string $alias2 alias de la segunda tabla para aplicar la funcion (tabla que contiene a B)
	 * @param string $attr2 nombre del segundo atributo geografico para usar en la funcion (B)
	 * @param string $selectItemAlias alias para identificar el resultado de la funcion
	 */
	public static function DISTANCE( $alias, $attr, $alias2, $attr2, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_DISTANCE, $alias, $attr, $alias2, $attr2, null, $selectItemAlias );
	}
	
	/**
	 * Crea una funcion de distancia entre un atributo y un valor geografico (A y B)
	 *  
	 * @param string $alias alias de la tabla (tabla que contiene a A)
	 * @param string $attr nombre del atributo geografico para usar en la funcion (A)
	 * @param Geometry $value valor geografico para usar en la funcion (B)
	 * @param string $selectItemAlias alias para identificar el resultado de la funcion
	 */
	public static function DISTANCE_TO( $alias, $attr, Geometry $value, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_DISTANCE, $alias, $attr, null, null, $value, $selectItemAlias);
	}
	
	/**
	 * Crea una funcion de Area de un atributo
	 * 
	 * @param string $alias alias de la tabla
	 * @param string $attr nombre del atributo geografico para usar en la funcion
	 * @param string $selectItemAlias alias para identificar el resultado de la funcion
	 */
	public static function AREA($alias, $attr, $selectItemAlias = null){
		return self::createGISFunction(self::GIS_FUNCTION_AREA, $alias, $attr, null, null, null, $selectItemAlias);
	}
	
	/**
	 * Crea una funcion de interseccion entre dos atributos (A y B)
	 * 
	 * @param string $alias alias de la tabla (tabla que contiene a A)
	 * @param string $attr nombre del atributo geografico para usar en la funcion (A)
	 * @param string $alias2 alias de la segunda tabla para aplicar la funcion (tabla que contiene a B)
	 * @param string $attr2 nombre del segundo atributo geografico para usar en la funcion (B)
	 * @param string $selectItemAlias alias para identificar el resultado de la funcion
	 */
	public static function INTERSECTION( $alias, $attr, $alias2, $attr2, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_INTERSECTION, $alias, $attr, $alias2, $attr2, $selectItemAlias );
	}
	
	/**
	 * Crea una funcion de interseccion entre un atributo y un valor geografico (A y B)
	 * 
	 * @param string $alias alias de la tabla (tabla que contiene a A)
	 * @param string $attr nombre del atributo geografico para usar en la funcion (A)
	 * @param Geometry $value valor geografico para usar en la funcion (B)
	 * @param string $selectItemAlias alias para identificar el resultado de la funcion
	 */
	public static function INTERSECTION_TO( $alias, $attr, Geometry $value, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_INTERSECTION, $alias, $attr, null, null, $value, $selectItemAlias);
	}
	
	/**
	 * Crea una funcion de union entre dos atributos (A y B)
	 * 
	 * @param string $alias alias de la tabla (tabla que contiene a A)
	 * @param string $attr nombre del atributo geografico para usar en la funcion (A)
	 * @param string $alias2 alias de la segunda tabla para aplicar la funcion (tabla que contiene a B)
	 * @param string $attr2 nombre del segundo atributo geografico para usar en la funcion (B)
	 * @param string $selectItemAlias alias para identificar el resultado de la funcion
	 */
	public static function UNION( $alias, $attr, $alias2, $attr2, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_UNION, $alias, $attr, $alias2, $attr2, $selectItemAlias );
	}
	
	/**
	 * Crea una funcion de union entre un atributo y un valor geografico (A y B)
	 * 
	 * @param string $alias alias de la tabla (tabla que contiene a A)
	 * @param string $attr nombre del atributo geografico para usar en la funcion (A)
	 * @param Geometry $value valor geografico para usar en la funcion (B)
	 * @param string $selectItemAlias alias para identificar el resultado de la funcion
	 */
	public static function UNION_TO( $alias, $attr, Geometry $value, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_UNION, $alias, $attr, null, null, $value, $selectItemAlias);
	}

	/**
	 * Crea una funcion de diferencia entre dos atributos (A y B)
	 * 
	 * @param string $alias alias de la tabla (tabla que contiene a A)
	 * @param string $attr nombre del atributo geografico para usar en la funcion (A)
	 * @param string $alias2 alias de la segunda tabla para aplicar la funcion (tabla que contiene a B)
	 * @param string $attr2 nombre del segundo atributo geografico para usar en la funcion (B)
	 * @param string $selectItemAlias alias para identificar el resultado de la funcion
	 */
	public static function DIFFERENCE( $alias, $attr, $alias2, $attr2, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_DIFFERENCE, $alias, $attr, $alias2, $attr2, $selectItemAlias );
	}
	
	/**
	 * Crea una funcion de diferencia entre un atributo y un valor geografico (A y B)
	 * 
	 * @param string $alias alias de la tabla (tabla que contiene a A)
	 * @param string $attr nombre del atributo geografico para usar en la funcion (A)
	 * @param Geometry $value valor geografico para usar en la funcion (B)
	 * @param string $selectItemAlias alias para identificar el resultado de la funcion
	 */
	public static function DIFFERENCE_TO( $alias, $attr, Geometry $value, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_DIFFERENCE, $alias, $attr, null, null, $value, $selectItemAlias);
	}
}

?>