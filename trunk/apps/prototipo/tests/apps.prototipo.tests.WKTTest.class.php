<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');

/**
 * Test para probar la serializacion de WKT a Geometry
 * @author harley
 */
class WKTTest extends YuppGISTestCase {
	
	public function testSerializationOfPoint() {
		$text = 'POINT(23 32)';
		$point = WKTGEO::fromText(Point::getClassName(), $text);
		
		$this->assert($point == array('x' => 23, 'y' => 32), 'Test parseo punto');
	}
	
}