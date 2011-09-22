<?php

class GISFunction extends SelectItem {
	
	private $functionName;
	private $params;
   
	const GIS_FUNCTION_DISTANCE = "gisfunction.type.distance";
      
	private static function getGISTypes() {
      return array(
                self::GIS_FUNCTION_DISTANCE
             );
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
	
	private static function createGISFunction( $type, $alias, $attr, $alias2, $attr2 ) {
		$f = new GISFunction();
		$f->setType( $type );
		$f->setParams( array( new SelectAttribute($alias, $attr), new SelectAttribute($alias2, $attr2) ) );
		return $f;
	}
	
	public static function DISTANCE( $alias, $attr, $alias2, $attr2  ) {
		return self::createGISFunction(self::GIS_FUNCTION_DISTANCE, $alias, $attr, $alias2, $attr2 );
	}

}

?>