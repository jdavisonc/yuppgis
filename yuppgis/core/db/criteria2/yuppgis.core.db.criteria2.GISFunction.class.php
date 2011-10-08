<?php

class GISFunction extends SelectItem {
	
	private $type;
	private $params;
   
	const GIS_FUNCTION_DISTANCE = "gisfunction.type.distance";
	
	public function __construct($type, $params, $selectItemAlias = null) {
		$this->setType($type);
		$this->params = $params;
		parent::__construct($selectItemAlias);
	}
      
	private static function getGISTypes() {
      return array(
                self::GIS_FUNCTION_DISTANCE
             );
	}
	
	private static function getGISTypesThatReturnGeometry() {
      return array(
                
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
		$params = array(new SelectAttribute($alias, $attr));
		if ($value == null) {
			$params[] = new SelectAttribute($alias2, $attr2);
		} else {
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

}

?>