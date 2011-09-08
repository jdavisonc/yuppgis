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
			$kml .= KMLUtilities::ElementToKml($element, $layer);
		}
				
		$kml .= '</Folder>
			</Document>
		</kml>';
		
		return $kml;
	}


	private static function ElementToKml($element, $layer){
		/*TODO: el elemento puede tener mas de un componente geografico, ver como resolver getUbicacion()*/		
		$kml = '		
		<Placemark>
			<name>'.$element->getName().'</name>
			<description>Just another blue icon.</description>
			<Style>
		        <IconStyle>
		          <scale>0.8</scale>
		          <Icon>
		            <href>'.$layer->getIconurl().'</href>
		          </Icon>
		        </IconStyle>
		    </Style>		    
			<Point>
				<coordinates>'.$element->getUbicacion()->getX().','.$element->getUbicacion()->getY().'</coordinates>
			</Point>
		</Placemark>';
		
		return $kml;
	}	
	
	
	/* kml.events.register(\'mousedown\', kml, function(evt) {
							  
							  var popup = new OpenLayers.Popup.FramedCloud(null,new OpenLayers.LonLat(-56.181944, -34.883611),null,
							              "<div style=\'background-color:red; width:150;height:100\'>hi</div>",
							              null,true,null);
							    map_'.$id.'.addPopup(popup);
							    OpenLayers.Event.stop(evt);
				             
			                });*/
}


?>