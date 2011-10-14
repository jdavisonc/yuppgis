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
	
	function testPointFromKML() {
		$result = KMLGEO::fromKML(self::KML_POINT);
		
		$this->assertNotNull($result != null && $result instanceof Point && $result->getUIProperty() != null, "");
	}
	
	function testLineStringFromKML() {
		$result = KMLGEO::fromKML(self::KML_LINE);
		
		$this->assertNotNull($result != null && $result instanceof LineString && $result->getUIProperty() != null, "");
	}
	
	function testPolygonFromKML() {
		$result = KMLGEO::fromKML(self::KML_POLYGON);
		
		$this->assertNotNull($result != null && $result instanceof Polygon && $result->getUIProperty() != null, "");
	}
	
	
}

?>