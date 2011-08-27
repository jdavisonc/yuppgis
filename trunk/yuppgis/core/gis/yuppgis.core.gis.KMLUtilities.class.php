<?php
YuppLoader::load('yuppgis.core.basic','Point');

class KMLUtilities{
	
	public static function LayerToKml(DataLayer $layer){
		$kml = '<?xml version="1.0" encoding="UTF-8"?>
			<kml xmlns="http://earth.google.com/kml/2.0">
  			<Document>
    			<name>KML Samples</name>
    			<open>1</open>
    			<description>Unleash your creativity with the help of these examples!</description>
    			<Folder id="'.$layer->getId().'">
			      <name>'.$layer->getName().'</name>
			      <visibility>0</visibility>
			      <description>Examples of paths. Note that the tessellate tag is by default set to 0. If you want to create tessellated lines, they must be authored (or edited) directly in KML.</description>';		

		$elements = $layer->getElements();
		
		foreach ($elements as $element){
			$kml .= KMLUtilities::ElementToKml($element);
		}
				
		$kml .= '</Folder>
			</Document>
		</kml>';
		
		return $kml;
	}


	private static function ElementToKml($element){		
		$kml = "		
		<Placemark>
			<name>Blue Icon</name>
			<description>Just another blue icon.</description>
			<Point>
				<coordinates>".$element->getUbicacion()->getX().",".$element->getUbicacion()->getY()."</coordinates>
			</Point>
		</Placemark>";
		
		return $kml;
	}	
}


?>