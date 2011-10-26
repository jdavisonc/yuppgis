<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');
YuppLoader::load('yuppgis.core.persistent.serialize', 'KMLGEO');

class KMLGEOTest extends YuppGISTestCase {
	
	const KML_POINT = '<Placemark ID="2">
					<Style>
				        <IconStyle>
				          <scale>0.8</scale>
				          <Icon>
				            <href>/yuppgis/yuppgis/js/gis/img/marker-gold.png</href>
				          </Icon>
				        </IconStyle>
				    </Style>						
					<Point>
						<coordinates>-56.181448,-34.883641</coordinates>
					</Point>
				</Placemark>';

	const KML_LINE = '<Placemark ID="2">
					<Style>
						<LineStyle>
							<color>50FFFFFF</color>
							<width>15</width>
						</LineStyle>
				    </Style>						
					<LineString>
						<coordinates>-56.181448,-34.883641,0 -56.181448,-34.883641,0</coordinates>
					</LineString>
				</Placemark>';

	const KML_POLYGON = '<Placemark ID="2">
					<Style>
						<LineStyle>
							<color>50FFFFFF</color>
							<width>15</width>
						</LineStyle>
				    </Style>						
					<Polygon>
						<outerBoundaryIs>
							<LineRing>
								<coordinates>-56.181448,-34.883641,0 -56.181448,-34.883641,0</coordinates>
							</LineRing>
							<LineRing>
								<coordinates>-56.181448,-34.883641,0 -56.181448,-34.883641,0</coordinates>
							</LineRing>
						</outerBoundaryIs>
						<innerBoundaryIs>
							<LineRing>
								<coordinates>-56.181448,-34.883641,0 -56.181448,-34.883641,0</coordinates>
							</LineRing>
							<LineRing>
								<coordinates>-56.181448,-34.883641,0 -56.181448,-34.883641,0</coordinates>
							</LineRing>
						</innerBoundaryIs>
					</Polygon>
				</Placemark>'; 
	
	public function testPointFromKML() {
		$result = KMLGEO::fromKML(new SimpleXMLElement(self::KML_POINT));
		
		$this->assertNotNull($result != null && $result instanceof Point && $result->getUIProperty() != null, 'De kml a punto');
	}
	
	public function testLineStringFromKML() {
		$result = KMLGEO::fromKML(new SimpleXMLElement(self::KML_LINE));
		
		$this->assertNotNull($result != null && $result instanceof LineString && $result->getUIProperty() != null, 'De kml a lineString');
	}
	
	public function testPolygonFromKML() {
		$result = KMLGEO::fromKML(new SimpleXMLElement(self::KML_POLYGON));
		
		$this->assertNotNull($result != null && $result instanceof Polygon && $result->getUIProperty() != null, 'De kml a poligono');
	}
	
	public function testPointToKML() {
		$kml = new SimpleXMLElement('<Folder/>');
		$point = new Point(-56.0000, -34.1234234);
		$point->setUIProperty(new Icon(0,0,'ffff',0,0));
		KMLGEO::toKML(1, $point, 'Description', 'PPaciente', 200, null, $kml);
		
		$this->assertNotNull($kml->asXML() != null, 'De punto a kml');
	}
	
	public function testPointDefaultStyleToKML() {
		$kml = new SimpleXMLElement('<Folder/>');
		$point = new Point(-56.0000, -34.1234234);
		$point->setId(200);
		KMLGEO::toKML(1, $point, 'Description', 'PPaciente', 200, new Icon(0,0,'gg'), $kml);
		
		$this->assertNotNull($kml->asXML() != null, 'De punto a kml');
	}
	
	public function testLineStringToKML() {
		$kml = new SimpleXMLElement('<Folder/>');
		$line = new LineString(array(new Point(-56.02, -34.1234234), new Point(-56.02, -34.1234234)));
		$line->setId(200);
		KMLGEO::toKML(1, $line, 'Description', 'PPaciente', 200, null, $kml);
		
		$this->assertNotNull($kml->asXML() != null, 'De lineString a kml');
	}
	
	public function testPolygonToKML() {
		$kml = new SimpleXMLElement('<Folder/>');
		$points = array ( new Point(-56.17438, -34.88619), new Point(-56.181548, -34.882521), 
				new Point(-56.181948, -34.880621), new Point(-56.181948, -34.883821), new Point(-56.17438, -34.88619));
		$line = new LineRing($points);
		$polygon = new Polygon($line, array($line, $line));
		$polygon->setId(200);
		KMLGEO::toKML(1, $polygon, 'Description', 'PPaciente', 200, null, $kml);
		
		$this->assertNotNull($kml->asXML() != null, 'De lineString a kml');
	}
	
}

?>