<?php

class KMLGEO {
	
	public static function fromKML($placemark) {
		$geometry = null;
		$uiProperty = null;
		$placemarkElement = new SimpleXMLElement($placemark);
		$id = strval($placemarkElement["ID"]); // Ver de usar atributo ID para guardar el ID
		
		foreach ($placemarkElement->children() as $nodeName => $node) {
			if ($nodeName == 'Point') {
				$geometry = self::pointFromKML($node);
			} else if ($nodeName == 'Polygon') {
				$geometry = self::polygonFromKML($node);
			} else if ($nodeName == 'LineString') {
				$geometry = self::lineStringFromKML($node);
			} else if ($nodeName == 'Style') {
				$uiProperty = self::styleFromKML($placemarkElement->Style); // Estilo
			} else {
				throw new Exception("Unnsuported Tag in KML " . $nodeName);
			}
		}
		
		$geometry->setUIProperty($uiProperty);
		$geometry->setId($id);
		return $geometry;
	}
	
	private static function pointFromKML($point) {
		$points = self::coordinatesFromKML($point->coordinates);
		return $points[0];
	}
	
	private static function lineStringFromKML($lineString) {
		$points = self::coordinatesFromKML($lineString->coordinates);
		return new LineString($points);
	}
	
	private static function coordinatesFromKML($coordinates) {
		$points = array();
		$coords = explode(' ', $coordinates);
		foreach ($coords as $point) {
			$pointCoord = explode(',', $point);
			$points[] = new Point($pointCoord[0], $pointCoord[1]);
		}
		return $points;
	}
	
	private static function lineRingFromKML($lineRing) {
		$points = self::coordinatesFromKML($lineRing->coordinates);
		return new LineRing($points);
	}
	
	private static function polygonFromKML($polygon) {
		$exteriorBoundary = self::lineRingFromKML($polygon->outerBoundaryIs->LineRing);
		$interiorBoundary = array();
		foreach ($polygon->innerBoundaryIs->LineRing as $line) {
			$interiorBoundary[] = self::lineRingFromKML($line);			
		}
		return new Polygon($exteriorBoundary, $interiorBoundary);
	}
	
	private static function styleFromKML($style) {
		$styles = array();
		foreach ($style->children() as $nodeName => $node) {
			if ($nodeName == 'IconStyle') {
				$styles[] = new Icon(0, 0, strval($node->href), 0, 0);
			} else if ($nodeName == 'PolyStyle') {
				$styles[] = new Background(0, 0, Color::fromColorName(strval($node->color)));
			} else if ($nodeName == 'LineStyle') {
				$styles[] = new Border(0, 0, Color::fromColorName(strval($node->color)), strval($node->width));
			} else {
				throw new Exception("Unnsuported Tag in KML " . $nodeName);
			}
		}
		return $styles[0]; // Solo retorno el primero
	}
	
	public static function toKML(Geometry $geom) {
		throw new Exception("Not implemented yet");
	} 
	
}

?>