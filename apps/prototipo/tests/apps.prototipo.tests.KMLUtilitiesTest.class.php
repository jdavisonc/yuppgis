<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('prototipo.model', 'Paciente');
YuppLoader::load('yuppgis.core.gis', 'KMLUtilities');

class KMLUtilitiesTest extends YuppGISTestCase {

	private $kml = "";
	private $layerId;
	private $layerName = "";
	private $pointX;
	private $pointY;
	
	public function __construct($suite) {

		$this->layerId = uniqid();
		$this->layerName = "Test";
		$this->pointX = 10;
		$this->pointY = 10;
				
		$this->kml = '<?xml version="1.0" encoding="UTF-8"?>
					<kml xmlns="http://earth.google.com/kml/2.0">
						<Document>
						<name>KML Samples</name>
						<open>1</open>
						<description>Unleash your creativity with the help of these examples!</description>
						<Folder id="'.$this->layerId.'">
						<name>'.$this->layerName.'</name>
						<visibility>0</visibility>
						<description>Examples of paths. Note that the tessellate tag is by default set to 0. If you want to create tessellated lines, they must be authored (or edited) directly in KML.</description>
						<Placemark>
						<name>Blue Icon</name>
						<description>Just another blue icon.</description>
						<Point>
						<coordinates>'.$this->pointX.','.$this->pointY.'</coordinates>
						</Point>
						</Placemark>
						</Folder>
						</Document>
					</kml>';
		
		parent::__construct($suite);
	}	
		
	public function testLayerToKml(){
								
		$layer =  new DataLayer($this->layerId, $this->layerName, 'nombre');
		
		$paciente = new Paciente();
		$paciente->setUbicacion(new Point(array('x' => $this->pointX, 'y' => $this->pointY)));
				
		$layer->addElement($paciente);
		
		$result = KMLUtilities::LayerToKml($layer);		
				
		$this->assertXMLEquals($this->kml, $result, "Test layer to kml");		
		
	}	
	
}

?>