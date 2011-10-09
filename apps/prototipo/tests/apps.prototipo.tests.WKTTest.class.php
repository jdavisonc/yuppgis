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
		
		//Debe devolver clase linea por estar formado exactamente por dos lineas
		$this->assert($point->getX() == 23 && $point->getY() == 32 , 'Test parseo punto');
	}
	
	public function testSerializationOfPolygon() {
		//ejemplo pagina de postgres 
		$text = 'POLYGON((0 0,4 0,4 4,0 4,0 0),(1 1, 2 1, 2 2, 1 2,1 1))';
		$geom = WKTGEO::fromText($text);
		
		//$this->assert($point == array('class' => 'Point', 'x' => 23, 'y' => 32), 'Test parseo punto');
	}
	
	public function testSerializationOfLineString() {
		$text = 'LineString(23 32, 32 56, 32 59)';
		$linea = WKTGEO::fromText($text);
		
		$points = $linea->getPoints();
		$this->assert($points[0]->getX() == 23 && $points[0]->getY() == 32 && $points[2]->getX() == 32 && $points[2]->getY() == 59, 'Test parseo camino');
	}
	
	public function testSerializationOfLine() {
		
		$text = 'LineString(23 32, 32 56)';
		$linea = WKTGEO::fromText($text);
		
		//Debe devolver clase linea por estar formado exactamente por dos lineas
		$points = $linea->getPoints();
		$this->assert($points[0]->getX() == 23 && $points[0]->getY() == 32 && $points[1]->getX() == 32 && $points[1]->getY() == 56, 'Test parseo linea');
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
	
	public function testExpLines() {
		$exp = '/\([^\)]*\)/';
		$line = '(0 0,1 1,1 2),(2 3,3 2,5 4)';
		preg_match_all( $exp, $line, $pointsMatch, PREG_SET_ORDER );
		
	}
	
	
	public function testSerializationOfMultiPoint() {
		//ejemplo pagina de postgres 
		$text = 'MULTIPOINT(0 0,1 2)';
		$geom = WKTGEO::fromText($text);
		
		$col = $geom->getCollection();
		$p1 = $col[0];
		
		//Debe devolver clase linea por estar formado exactamente por dos lineas
		$this->assert($p1->getX() == 0 && $p1->getY() == 0 , 'Test parseo multi punto');
	}
	
	
	
	public function testSerializationOfMultiLineString() {
		//ejemplo pagina de postgres 
		$text = 'MULTILINESTRING((0 0,1 1,1 2),(2 3,3 2,5 4))';
		$geom = WKTGEO::fromText($text);
		
		$col = $geom->getCollection();
		$p1 = $col[0];
		
		$puntos = $p1->getPoints();
		
		$pto0 = $puntos[0];
		$pto2 = $puntos[2];
		
		$this->assert($pto0->getX() == 0 && $pto0->getY() == 0 && 
						$pto2->getX() == 1 && $pto2->getY() == 2 , 'Test parseo multi punto');
	}
	
	
	public function testSerializationOfMultiPolygon() {
		//ejemplo pagina de postgres 
		$text = 'MULTIPOLYGON(((0 0,4 0,4 4,0 4,0 0),(1 1,2 1,2 2,1 2,1 1)), ((-1 -1,-1 -2,-2 -2,-2 -1,-1 -1)))';
		$geom = WKTGEO::fromText($text);
		
		$col = $geom->getCollection();
		$p1 = $col[0];
		
		$pol = $p1->getExteriorBoundary();
		$puntos = $pol->getPoints();
		
		$pto0 = $puntos[0];
		$pto3 = $puntos[3];
		
		$this->assert($pto0->getX() == 0 && $pto0->getY() == 0 && 
						$pto3->getX() == 0 && $pto3->getY() == 4 , 'Test de multipolygonos');
	}
	
	public function testSerializationGeometryCollectionEmpty() {
		$text = 'GEOMETRYCOLLECTION EMPTY';
		$geom = WKTGEO::fromText($text);
		
		$col = $geom->getCollection();
		$this->assert(count($col) === 0 , 'Geometry Collections Vacias');
	}
	
	public function testSerializationOfGeometryCollection() {
		//ejemplo pagina de postgres 
		$text = 'GEOMETRYCOLLECTION(POINT(2 3),LINESTRING(2 3,3 4))';
		$geom = WKTGEO::fromText($text);
		
		$col = $geom->getCollection();
		$p1 = $col[1];
		
		$puntos = $p1->getPoints();
		
		$pto0 = $puntos[0];
		$pto1 = $puntos[1];
		
		$this->assert($pto0->getX() == 2 && $pto0->getY() == 3 && 
						$pto1->getX() == 3 && $pto1->getY() == 4 , 'Test de GeometryCollection');
	}
	
	public function testMultiPolygonToText() {
		//ejemplo pagina de postgres
		$text = 'MULTIPOLYGON(((0 0,4 0,4 4,0 4,0 0),(1 1,2 1,2 2,1 2,1 1)),((-1 -1,-1 -2,-2 -2,-2 -1,-1 -1)))';
		$geom = WKTGEO::fromText($text);
		$text2 = WKTGEO::toText($geom);
		$this->assert($text == strtoupper($text2), "testMultiPolygonToText");
	}	
}