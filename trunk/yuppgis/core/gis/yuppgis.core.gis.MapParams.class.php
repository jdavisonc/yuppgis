<?php

YuppLoader :: load('yuppgis.core.config', 'YuppGISConfig');

class MapParams{
	
	const ID = "Id";
	const OpenLayerJS_URL = "OLUrl";
	const WIDTH = "Width";
	const HEIGHT = "height";
	const BORDER = "border";
	const KML_URL = "kmlurl";
	
	public static function getValueOrDefault($array, $key)
	{
		if($array != null && array_key_exists ( $key , $array ))			
			return $array[$key];
		else{
			$appName = YuppContext::getInstance()->getApp();
			$gmaps_key = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_GOOGLE_MAPS_KEY);
			
			switch ($key){
				case MapParams::ID:
					return -1;
				case MapParams::OpenLayerJS_URL:
					return "http://maps.google.com/maps?file=api&v=2&key=". $gmaps_key;
				case MapParams::WIDTH:
					return "500px";
				case MapParams::HEIGHT:
					return "250px";
				case MapParams::BORDER:
					return "1px solid black";
				case MapParams::KML_URL:
					return "/Home/KML";  
			}
			
		}			
	}	
}
?>