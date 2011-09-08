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
	
	public function encodeJSON() { 
		$json = new stdClass();;
	    foreach ($this as $key => $value) 
	    { 
	        $json->$key = $value; 
	    } 
    	return json_encode($json); 
	} 
	
	public function decodeJSON($json_str) 
	{ 
    	$json = json_decode($json_str, 1); 
    	foreach ($json as $key => $value) 
    	{ 
        	$this->$key = $value; 
    	} 
	} 

}

class Color {
	
	const WHITE = 0;
	const BLACK = 1;
	const RED = 2;
	
}

?>