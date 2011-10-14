<?php

YuppLoader::load('yuppgis.core.basic.ui', 'Icon');
YuppLoader::load('yuppgis.core.basic.ui', 'Background');
YuppLoader::load('yuppgis.core.basic.ui', 'Border');

class UIProperty {
	
	protected $alpha;
	protected $zIndex;
	
	public function __construct($alpha = 0, $zIndex = 0) {
		$this->alpha = $alpha;
		$this->zIndex = $zIndex;
	}
	
	public function getAlpha() {
		return $this->alpha;
	}
	
	public function setAlpha($alpha) {
		$this->alpha = $alpha;
	}
	
	public function getZIndex() {
		return $this->zIndex;
	}
	
	public function setZIndex($zIndex) {
		$this->zIndex = $zIndex;
	}
	
	public static function toJSON($uiProperty) {
		if ($uiProperty == null) {
			return null;
		}
		
		$json = new stdClass();
	    foreach ($uiProperty as $key => $value) 
	    { 
	        $json->$key = $value; 
	    } 
	    $json->class = get_class($uiProperty);
    	
	    return json_encode($json);
	} 
	
	public static function fromJSON($json_str) 
	{
    	if($json_str != null && $json_str != ""){
			$json = json_decode($json_str, 1);
	    	$obj = new $json['class'];
	    	unset($json['class']);
	    	foreach ($json as $key => $value) 
	    	{ 
	        	$obj->$key = $value; 
	    	}
	    	return $obj;
    	}else{    		
    		return null;
    	} 
	}

}

class Color {
	
	const WHITE = 0;
	const BLACK = 1;
	const RED = 2;
	const BLUE = 3;
	
	public static function getColorName($color) {		
		switch ($color) {
		    case Color::WHITE:
		        return "FFFFFFFF";
		    case Color::BLACK:
		        return "FF000000";
		    case Color::RED:
		        return "FF0000FF";
		    case Color::BLUE:
		        return "FFFF0000";
		        break;
		    default:
		    	return "";
		}		
	}
	
	public static function fromColorName($name) {
		switch (strtoupper($name)) {
		    case "FFFFFFFF":
		        return Color::WHITE;
		    case "FF000000":
		        return Color::BLACK;
		    case "FF0000FF":
		        return Color::RED;
		    case "FFFF0000";
		    	return Color::BLUE;
		    default:
		    	return "";
		}
	}
	
}

?>