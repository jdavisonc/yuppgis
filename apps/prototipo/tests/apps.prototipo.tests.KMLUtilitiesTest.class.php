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
						<name>YuppGIS KML</name>
						<open>1</open>
						<description>Description Here!</description>
						<Folder ID="'.$id.'">
						<name>'.$name.'</name>
						<visibility>1</visibility>
						<description>Description Here!</description>
						<Placemark ID="'.$elementId.'">
						<name></name>
						<description>Capa: layerName, Id: </description>
						<className>Paciente</className>
						<layerId>'.$id.'</layerId>
						<elementId>'.$elementId.'</elementId>
						<gisType>yuppgis_type_point</gisType>
						<Style>
					        <IconStyle>
					          <scale>0.8</scale>
					          <Icon>
					            <href>/yuppgis/yuppgis/js/gis/img/marker-gold.png</href>
					          </Icon>
					        </IconStyle>
					    </Style>						
						<Point>
						<coordinates>'.$X.','.$Y.',0.</coordinates>
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
		
		$kml = $this->getKml($layer->getId(), $layer->getName(), $paciente->getUbicacion()->getX(), $paciente->getUbicacion()->getY(), $paciente->getId());
		
		$result = KMLUtilities::layerToKml($layer);
		$this->assertXMLEquals($kml, $result, "Test layer a kml");
	}	
	
	public function testKmlToLayer() {
		$layer =  new DataLayer('layerName', 'nombre');
		$paciente = new Paciente();
		$paciente->setNombre('Roberto');
		$paciente->setUbicacion(new Point(10, 10));
		$layer->addElement($paciente);
		
		$kml = $this->getKml($layer->getId(), $layer->getName(), $paciente->getUbicacion()->getX(), $paciente->getUbicacion()->getY(), $paciente->getId());
		
		$result = KMLUtilities::KMLToLayer($kml);
		$this->assert($result != null && count($result->getElements()) == 1, "Test kml a layer");
	}
	
}

?>