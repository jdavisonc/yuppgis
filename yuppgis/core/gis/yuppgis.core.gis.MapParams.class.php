<?php
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
			switch ($key){
				case MapParams::ID:
					return 'map_'.uniqid();
				case MapParams::OpenLayerJS_URL:
					return "http://maps.google.com/maps?file=api&v=2&key=your_key_here";
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