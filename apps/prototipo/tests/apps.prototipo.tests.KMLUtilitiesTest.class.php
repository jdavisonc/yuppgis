<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('prototipo.model', 'Paciente');
YuppLoader::load('yuppgis.core.gis', 'KMLUtilities');

class KMLUtilitiesTest extends YuppGISTestCase {

	private function getKml($id, $name, $X, $Y, $elementId){		
		$kml = '<?xml version="1.0" encoding="UTF-8"?>
					<kml xmlns="http://earth.google.com/kml/2.0">
						<Document>
						<name>KML Samples</name>
						<open>1</open>
						<description>Unleash your creativity with the help of these examples!</description>
						<Folder id="'.$id.'">
						<name>'.$name.'</name>
						<visibility>0</visibility>
						<description>Examples of paths. Note that the tessellate tag is by default set to 0. If you want to create tessellated lines, they must be authored (or edited) directly in KML.</description>
						<Placemark>
						<name></name>
						<description>Capa: layerName, Id: </description>
						<className>Paciente</className>
						<layerId>'.$id.'</layerId>
						<elementId>'.$elementId.'</elementId>
						<Style>
					        <IconStyle>
					          <scale>0.8</scale>
					          <Icon>
					            <href>/yuppgis/yuppgis/js/gis/img/marker-gold.png</href>
					          </Icon>
					        </IconStyle>
					    </Style>						
						<Point>
						<coordinates>'.$X.','.$Y.'</coordinates>
						</Point>
						</Placemark>
						</Folder>
						</Document>
					</kml>';
		return $kml;		
	}
		
	public function testLayerToKml(){
								
		$layer =  new DataLayer('layerName', 'nombre');
		
		$paciente = new Paciente();
		$paciente->setNombre('Roberto');
		$paciente->setUbicacion(new Point(10, 10));
				
		$layer->addElement($paciente);
		//$layer->save();
		
		$kml = $this->getKml($layer->getId(), $layer->getName(), $paciente->getUbicacion()->getX(), $paciente->getUbicacion()->getY(), $paciente->getId());
		
		$result = KMLUtilities::LayerToKml($layer);
				
		$this->assertXMLEquals($kml, $result, "Test layer to kml");
		
	}	
	
}

?>