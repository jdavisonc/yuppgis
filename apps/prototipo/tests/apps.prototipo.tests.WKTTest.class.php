<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');

/**
 * Test para probar la serializacion de WKT a Geometry
 * @author harley
 */
class WKTTest extends YuppGISTestCase {
	
	public function testSerializationOfPoint() {
		$text = 'Point(23 32)';
		$point = WKTGEO::fromText($text);
		
		$this->assert($point == array('class' => 'Point', 'x' => 23, 'y' => 32), 'Test parseo punto');
	}
	
	public function testSerializationOfLineString() {
		$text = 'LineString(23 32, 32 56)';
		$linea = WKTGEO::fromText($text);
		
		$arr = array ('class' => 'LineString', 'points' => array (array('x' => 23, 'y' => 32), array('x' => 32, 'y' => 56)));
		$this->assert($linea == $arr, 'Test parseo camino');
	}
	
	public function testSerializationOfLine() {
		
		$text = 'LineString(23 32, 32 56)';
		$linea = WKTGEO::fromText($text);
		
		$arr = array ('class' => 'LineString', 'points' => array (array('x' => 23, 'y' => 32), array('x' => 32, 'y' => 56)));
		$this->assert($linea == $arr, 'Test parseo linea');
	}
	
	public function testSerializationOfLine2() {
				
		$puntos = array (new Point(23, 32), new Point(32, 56));
		$linea = new LineString($puntos);
		
		$puntos2 = $linea->getPuntos();
		
		$text = 'LineString(23 32, 32 56)';
		$puntos2 = WKTGEO::fromText($text);
		$linea2 = new LineString($puntos);
		
		$this->assert($linea == $linea2, 'Test parseo linea2');
	}
	
	public function testArray() {
		$points = array (array ('x' => 2, 'y' => 3), array ('x' => 2, 'y' => 3));
		$points2 = array (array ('x' => 2, 'y' => 3), array ('x' => 2, 'y' => 6));
		
		$count1 = count(array_unique($points, SORT_REGULAR));
		$count2 = count(array_unique($points2, SORT_REGULAR));
		
		$this->assert(true, 'Test ' . $count1 . ' ' . $count2);
	}
}