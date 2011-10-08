<?php

class GISFunction extends SelectItem {
	
	private $type;
	private $params;
   
	const GIS_FUNCTION_DISTANCE 	= "gisfunction.type.distance";
	const GIS_FUNCTION_AREA 		= "gisfunction.type.area";
	const GIS_FUNCTION_INTERSECTION	= "gisfunction.type.intersection";
	const GIS_FUNCTION_UNION		= "gisfunction.type.union";
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
	
	public static function DISTANCE( $alias, $attr, $alias2, $attr2, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_DISTANCE, $alias, $attr, $alias2, $attr2, $selectItemAlias );
	}
	
	public static function DISTANCE_TO( $alias, $attr, Geometry $value, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_DISTANCE, $alias, $attr, null, null, $value, $selectItemAlias);
	}
	
	public static function AREA($alias, $attr, $selectItemAlias = null){
		return self::createGISFunction(self::GIS_FUNCTION_AREA, $alias, $attr, null, null, null, $selectItemAlias);
	}
	
	public static function INTERSECTION( $alias, $attr, $alias2, $attr2, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_INTERSECTION, $alias, $attr, $alias2, $attr2, $selectItemAlias );
	}
	
	public static function INTERSECTION_TO( $alias, $attr, Geometry $value, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_INTERSECTION, $alias, $attr, null, null, $value, $selectItemAlias);
	}
	
	public static function UNION( $alias, $attr, $alias2, $attr2, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_UNION, $alias, $attr, $alias2, $attr2, $selectItemAlias );
	}
	
	public static function UNION_TO( $alias, $attr, Geometry $value, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_UNION, $alias, $attr, null, null, $value, $selectItemAlias);
	}

	public static function DIFFERENCE( $alias, $attr, $alias2, $attr2, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_DIFFERENCE, $alias, $attr, $alias2, $attr2, $selectItemAlias );
	}
	
	public static function DIFFERENCE_TO( $alias, $attr, Geometry $value, $selectItemAlias = null ) {
		return self::createGISFunction(self::GIS_FUNCTION_DIFFERENCE, $alias, $attr, null, null, $value, $selectItemAlias);
	}
}

?>