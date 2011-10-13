<?php

YuppLoader::load('yuppgis.core.basic','Point');
YuppLoader::load('yuppgis.core.basic','MultiPolygon');
YuppLoader::load('yuppgis.core.basic.ui', 'UIProperty');


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

		$kml = '';
		if ($element->hasAttribute('ubicacion')){
			$kml .= '
		<Placemark>
			<name>'.$element->getId().'</name>
			<description>Capa: '.$layer->getName().', Id: '.$element->getId().'</description>
			<className>'.get_class($element).'</className>
			<layerId>'.$layer->getId().'</layerId>
			<elementId>'.$element->getId().'</elementId>
			<gisType>'.GISDatatypes::POINT.'</gisType>
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
		}
		
		if ($element->hasAttribute('linea')){

			$kml .= '
		<Placemark>
			<name>'.$element->getId().'</name>
			<description>Capa: '.$layer->getName().', Id: '.$element->getId().'</description>
			<className>'.get_class($element).'</className>
			<layerId>'.$layer->getId().'</layerId>
			<elementId>'.$element->getId().'</elementId>
			<gisType>'.GISDatatypes::LINESTRING.'</gisType>';
						
			if($element->getLinea() != null){
				
				if($element->getLinea()->hasAttribute('uiproperty')){
						$lineStringUip = $element->getLinea()->getUIProperty();
						if(get_class($lineStringUip) == "Border"){									
							$kml.= '<Style>
							    		<LineStyle>									    		 
							      		<color>'.$lineStringUip->getColorName().'</color>	
							      		<width>'.$lineStringUip->getWidth().'</width>								      
							    		</LineStyle>
						  			</Style>';
						}								
					}
				
			$kml.=	'<LineString>
				<coordinates>';			
				foreach ($element->getLinea()->getPoints() as $point){
					$kml.=	$point->getX().','.$point->getY().',0. ';
				}			
			$kml.=	'</coordinates>
			</LineString>';
			}
		$kml .= '</Placemark>';
		}
		
		if ($element->hasAttribute('zonas')){
				foreach ($element->getZonas()->getCollection() as $zona) {
					$kml .= '
						<Placemark>
							<name>'.$element->getId().'</name>
							<description>Capa: '.$layer->getName().', Id: '.$element->getId()	.'</description>
							<className>'.get_class($element).'</className>
							<layerId>'.$layer->getId().'</layerId>
							<elementId>'.$element->getId().'</elementId>
							<gisType>'.GISDatatypes::POLYGON.'</gisType>';
					
							if($element->getZonas()->hasAttribute('uiproperty')){
								$polygonUip = $element->getZonas()->getUIProperty();
								if(get_class($polygonUip) == "Background"){									
									$kml.= '<Style>
									    		<PolyStyle>									    		 
									      		<color>'.$polygonUip->getColorName().'</color>									      
									    		</PolyStyle>
								  			</Style>';
								}								
							}		
					$kml .= '<Polygon><outerBoundaryIs>
        						<LinearRing>
        						<coordinates>';			
									foreach ($zona->getExteriorBoundary()->getPoints() as $point){
										$kml.=	$point->getX().','.$point->getY().',0. ';
									}			
						$kml.=	'</coordinates>
								 </LinearRing>
							</outerBoundaryIs>';
         			
					if ($zona->getInteriorsBoundary() != null) {
						
						$kml .= '<innerBoundaryIs>';
						foreach ($zona->getInteriorsBoundary() as $line) {
							$kml.=	'<LineRing>
									<coordinates>';			
										foreach ($line->getPoints() as $point){
											$kml.=	$point->getX().','.$point->getY().',0. ';
										}			
							$kml.=	'</coordinates>
									</LineRing>
									</innerBoundaryIs>';
						}
						
					}
					$kml .= '</Polygon></Placemark>';
				}
		}

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