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
			<gisType>'.GISDatatypes::POINT.'</gisType>';

			if($element->getUbicacion()->hasAttribute('uiproperty')){
				$kml.= KMLUtilities::UIPropertyToKml(GISDatatypes::POINT, $element->getUbicacion()->getUIProperty(), $layer);
					

			}
				

			$kml .=	'<Point>
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
					$kml.= KMLUtilities::UIPropertyToKml(GISDatatypes::LINESTRING, $element->getLinea()->getUIProperty(), $layer);
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
					$kml.= KMLUtilities::UIPropertyToKml(GISDatatypes::POLYGON, $element->getZonas()->getUIProperty(), $layer);
				}
				$kml .= '<Polygon>
								<outerBoundaryIs>
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

	private static function DefaultStyleForLayer($gisDatatypes, $layer)
	{
		switch ($gisDatatypes){
			case GISDatatypes::POINT:
				return '<Style>
					        <IconStyle>
					          <scale>0.8</scale>
					          <Icon>
					             <href>'.$layer->getIconurl().'</href>
					          </Icon>
					        </IconStyle>
					    </Style>';		       			
				break;
			//TODO Preguntar por los valores por defecto de la capa
			case GISDatatypes::LINERING:
			case GISDatatypes::LINESTRING:				
			case GISDatatypes::POLYGON:
			default:
				return '<Style />';
				break;
		}

	}

	private static function UIPropertyToKml($gisDatatypes, $uip, $layer)
	{
		switch (get_class($uip)) {
			case "Icon":{
				switch ($gisDatatypes){
					case GISDatatypes::POINT:
						return '<Style>
							        <IconStyle>
							          <scale>0.8</scale>
							          <Icon>
							            <href>'.$uip->getIcon().'</href>
							          </Icon>
							        </IconStyle>
							    </Style>';		       			
						break;
					case GISDatatypes::LINERING:
					case GISDatatypes::LINESTRING:
					case GISDatatypes::POLYGON:
					default:
						return '<Style />';
						break;
				}
				break;
			}
			case "Background":{
				switch ($gisDatatypes){
					case GISDatatypes::POLYGON:
						return '<Style>
						    		<PolyStyle>									    		 
						      			<color>'.$uip->getColor().'</color>									      
						    		</PolyStyle>
				  				</Style>';		       			
						break;
					case GISDatatypes::LINERING:
					case GISDatatypes::LINESTRING:
					case GISDatatypes::POINT:
					default:
						return '<Style />';
						break;
				}
				break;
			}
			case "Border":{
				switch ($gisDatatypes){
					case GISDatatypes::POLYGON:
					case GISDatatypes::LINERING:
					case GISDatatypes::LINESTRING:
						return '<Style>
						    		<LineStyle>									    		 
							      		<color>'.$uip->getColor().'</color>							      		
							      		<width>'.$uip->getWidth().'</width>								      
						    		</LineStyle>
					  			</Style>';		       			
						break;							
					case GISDatatypes::POINT:
					default:
						return '<Style />';
						break;
				}
				break;
			}
			default:
				return KMLUtilities::DefaultStyleForLayer($gisDatatypes, $layer);
				break;
		}
	}
}


?>