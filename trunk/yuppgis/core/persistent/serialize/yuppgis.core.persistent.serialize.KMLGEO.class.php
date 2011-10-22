<?php

class KMLGEO {
	
	/************
	 * From KML
	 ************/
	
	public static function fromKML(SimpleXMLElement $placemarkElement) {
		$geometry = null;
		$uiProperty = null;
		$id = strval($placemarkElement['ID']); // Ver de usar atributo ID para guardar el ID
		
		foreach ($placemarkElement->children() as $nodeName => $node) {
			if ($nodeName == 'Point') {
				$geometry = self::pointFromKML($node);
			} else if ($nodeName == 'Polygon') {
				$geometry = self::polygonFromKML($node);
			} else if ($nodeName == 'LineString') {
				$geometry = self::lineStringFromKML($node);
			} else if ($nodeName == 'LinearRing') {
				$geometry = self::lineRingFromKML($node);
			} else if ($nodeName == 'Style') {
				$uiProperty = self::styleFromKML($placemarkElement->Style); // Estilo
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
				$styles[] = new Icon(0, 0, strval($node->href), strval($node->$width), strval($node->$height));
			} else if ($nodeName == 'PolyStyle') {
				$styles[] = new Background(0, 0, Color::fromColorName(strval($node->color)));
			} else if ($nodeName == 'LineStyle') {
				$styles[] = new Border(0, 0, Color::fromColorName(strval($node->color)), strval($node->width));
			} else {
				throw new Exception("Tag en KML no soportada " . $nodeName);
			}
		}
		return $styles[0]; // Solo retorno el primero
	}

	
	/************
	 * To KML
	 ************/
	
	public static function toKML($id, Geometry $geom, $description, $class, $layerId, $defaultStyle, SimpleXMLElement &$parent) {
		if ($geom instanceof GeometryCollection) {
			return self::collectionToKML($id, $geom, $description, $class, $layerId, $defaultStyle, $parent);
		} else {
			return self::singleToKML($id, $geom, $description, $class, $layerId, $defaultStyle, $parent);
		}
	}
	
	private static function collectionToKML($id, Geometry $geom, $description, $class, $layerId, $defaultStyle, SimpleXMLElement &$parent) {
		$kml = '';
		foreach ($geom->getCollection() as $singleGeom) {
			$singleGeom->setUIProperty($geom->getUIProperty()); // se asigna el estilo de la coleccion
			$kml .= self::singleToKML($id, $singleGeom, $description, $class, $layerId, $defaultStyle, $parent);
		}
		return $kml;
	}
	
	private static function singleToKML($id, Geometry $geom, $description, $class, $layerId, $defaultStyle, SimpleXMLElement &$parent) {
		$gisDatatype = GISDatatypes::getTypeOf($geom);
		
		$placemark = $parent->addChild('Placemark');
		$placemark->addAttribute('ID', $id);
		$placemark->addChild('name', $id);
		$placemark->addChild('description', $description); 
		$placemark->addChild('className', $class);
		$placemark->addChild('layerId', $layerId);
		$placemark->addChild('elementId', $id);
		$placemark->addChild('gisType', $gisDatatype);
		
		switch ($gisDatatype) {
			case GISDatatypes::POINT:
				self::pointToKML($placemark, $geom);
				break;
			case GISDatatypes::LINESTRING:
				self::lineStringToKML($placemark, $geom);
				break;
			case GISDatatypes::LINE:
				self::lineStringToKML($placemark, $geom);
				break;
			case GISDatatypes::LINERING:
				self::lineRingToKML($placemark, $geom);
				break;
			case GISDatatypes::POLYGON:
				self::polygonToKML($placemark, $geom);
				break;
			default:
				throw new Exception('Geometria no soportada ' . $gisDatatype);
		}
		$uiProperty = $geom->getUIProperty();
		if ($uiProperty != null) {
			self::styleToKML($placemark, $gisDatatype, $uiProperty);
		} else if ($defaultStyle != null) {
			self::defaultStyleLayerToKML($placemark, $gisDatatype, $defaultStyle);
		}
	}		
	
	private static function pointToKML(&$placemark, $geom) {
		$point = $placemark->addChild('Point');
		self::coordinatesToKML($point, array($geom));
	}
	
	private static function coordinatesToKML(&$geomTag, array $points) {
		$coords = '';
		foreach ($points as $point) {
			$coords .= $point->getX() . ',' . $point->getY() . ',0. ';
		}
		$geomTag->addChild('coordinates', substr($coords, 0, -1));
	}
	
	private static function lineStringToKML(&$placemark, $geom) {
		$line = $placemark->addChild('LineString');
		self::coordinatesToKML($line, $geom->getPoints());
	}
	
	private static function lineRingToKML(&$placemark, $geom) {
		$line = $placemark->addChild('LinearRing');
		self::coordinatesToKML($line, $geom->getPoints());
	}
	
	private static function polygonToKML(&$placemark, $geom) {
		$polygon = $placemark->addChild('Polygon');
		$outerBoundary = $polygon->addChild('outerBoundaryIs');
		self::lineRingToKML($outerBoundary, $geom->getExteriorBoundary());
		if ($geom->getInteriorsBoundary() != null) {
			$innerBoundary = $polygon->addChild('innerBoundaryIs');
			foreach ($geom->getInteriorsBoundary() as $line) {
				self::lineRingToKML($innerBoundary, $line);
			}
		}
	}
	
	private static function styleToKML(&$placemark, $gisDatatype, $uiproperty) {
		$style = $placemark->addChild('Style');
		
		switch (get_class($uiproperty)) {
			case Icon::getClassName():
				switch ($gisDatatype){
					case GISDatatypes::POINT:
						self::iconToKML($style, $uiproperty);
					default:
						break;
				}
				break;
			case Background::getClassName():
				switch ($gisDatatype){
					case GISDatatypes::POLYGON:
						self::backgroundToKML($style, $uiproperty);
					default:
						break;
				}
				break;
			case Border::getClassName():
				switch ($gisDatatype){
					case GISDatatypes::LINE:
					case GISDatatypes::LINERING:
					case GISDatatypes::LINESTRING:
					case GISDatatypes::POLYGON:
						self::borderToKML($style, $uiproperty);
					default:
						break;
				}
				break;
			default:
				break;
		}
	}
	
	private static function defaultStyleLayerToKML(&$placemark, $gisDatatype, $defaultStyle) {
		$style = $placemark->addChild('Style');
		
		if ($defaultStyle instanceof Icon && $gisDatatype == GISDatatypes::POINT) {
			self::iconToKML($style, $defaultStyle);
		}
	}
	
	private static function iconToKML(&$style, $uiproperty) {
		$iconStyle = $style->addChild('IconStyle');
		$iconStyle->addChild('scale', 0.8);
		$icon = $iconStyle->addChild('Icon');
		$icon->addChild('href', $uiproperty->getUrl());
		$icon->addChild('width', $uiproperty->getWidth());
		$icon->addChild('height', $uiproperty->getHeight());
	}
	
	private static function borderToKML(&$style, $uiproperty) {
		$lineStyle = $style->addChild('LineStyle');
		$lineStyle->addChild('color', $uiproperty->getColor());
		$lineStyle->addChild('width', $uiproperty->getWidth());
	}
	
	private static function backgroundToKML(&$style, $uiproperty) {
		$polyStyle = $style->addChild('PolyStyle');
		$polyStyle->addChild('color', $uiproperty->getColor());
	}
}

?>