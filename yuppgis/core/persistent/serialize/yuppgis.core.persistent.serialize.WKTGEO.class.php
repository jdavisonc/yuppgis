<?php

/**
 * 
 * Clase que brinda funciones para la serializacion de Geometrias en WKT
 * 
 * @package yuppgis.core.persistent.serialize
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class WKTGEO {
	
	private static $regex  = array (
		'class' => '/^(\w+)\((.*)\)/',
		'collection' => '/\([^\)]*\)/',
		'multiPolygones' => '/(\({2}.+?\){2})/',
		'geometryCollection' => '/(\w*\(.*?\))/',
		'geometryCollectionEmpty' => '/GEOMETRYCOLLECTION EMPTY/'
		
	);
	
	/**
	 * Deserealiza un WKT generando un objeto {@link Geometry}
	 * 
	 * @param string $text texto en formato WKT
	 */
	public static function fromText( $text ) {
		
		preg_match( self::$regex['geometryCollectionEmpty'], $text, $geometryEmpty );
		
		if (count($geometryEmpty) === 1) {
			return new GeometryCollection();
		}
		
		preg_match( self::$regex['class'], $text, $matches );
		$class = $matches[1];
		return self::fromTextByClass($class, $matches[2]);
			
	}
	
	private static function fromTextByClass( $class, $param) {
		
		switch ( strtoupper($class) ) {
				case strtoupper(Point::getClassName()):
					$coord = explode(' ', $param);
					
					$res = new Point($coord[0], $coord[1]);
					$res->setClass($class);
					return $res;
				
				case strtoupper(LineString::getClassName()):
	
					$line = self::createLine(explode(',',  $param));
					return $line;
					
				case strtoupper(Polygon::getClassName()):
	
					return self::createPolygones($param);
					
				case strtoupper(MultiPoint::getClassName()):
	
					$points = self::createPoints(explode(',',  $param));
					return new MultiPoint($points);
					
				case strtoupper(MultiLineString::getClassName()):
					preg_match_all( self::$regex['collection'], $param, $linesMatch, PREG_SET_ORDER );
					
					$lines = array();
					for ($i = 0; $i < count($linesMatch); $i++) {
						$lines[] = self::createLine( explode(',',  substr($linesMatch[$i][0], 1, -1)) );
					}
					return new MultiLineString($lines);
					
				case strtoupper(MultiPolygon::getClassName()):
					preg_match_all( self::$regex['multiPolygones'], $param, $polygonesMatch, PREG_SET_ORDER );
					
					$polygones = array();
					for ($i = 0; $i < count($polygonesMatch); $i++) {
						$polygones[] = self::createPolygones(substr($polygonesMatch[$i][0], 1, -1));
					}
					return new MultiPolygon($polygones);
					
				case strtoupper(GeometryCollection::getClassName()):
					preg_match_all( self::$regex['geometryCollection'], $param, $geometryesMatch, PREG_SET_ORDER );
					
					$geometryes = array();
					for ($i = 0; $i < count($geometryesMatch); $i++) {
						$geometryes[] = self::fromText($geometryesMatch[$i][0]);
					}

					return new GeometryCollection($geometryes);
					
				default:
					throw new Exception("Not implemented yet");
			}
		
		return $res;
	}
	
	private static function createPolygones($lines) {
		preg_match_all( self::$regex['collection'], $lines, $linesMatch, PREG_SET_ORDER );
		
		//se hace parse del borde exterior
		$exteriorBoundary = self::createLine( explode(',',  substr($linesMatch[0][0], 1, -1)) );
		
		//se hace parse de los posibles bordes interiores 
		$interiorsBoundary = array();
		for ($i = 1; $i < count($linesMatch); $i++) {
			$interiorsBoundary[] = 	self::createLine( explode(',',  substr($linesMatch[$i][0], 1, -1)) );
		}
		
		return new Polygon($exteriorBoundary, $interiorsBoundary);
	}
	
	private static function createPoints(array $points) {
		
		$res = array();
		for ($i = 0; $i < count($points); $i++) {
			$coord = explode(' ', $points[$i]);
			if ($coord[0] == '') {
				array_shift($coord);
			}
			$res[] =  new Point( $coord[0], $coord[1] );
		}
		return $res;
	}
	
	private static function createLine(array $points) {
		$newPoints = self::createPoints($points);		
		if (self::isLine( $newPoints )) {
			$line = new Line($newPoints);
			$line->setClass(Line::getClassName());
		} elseif (self::isLineRing( $newPoints )) {
			$line = new LineRing($newPoints);
			$line->setClass(LineRing::getClassName());
		} else {
			$line = new LineString($newPoints);
			$line->setClass(LineString::getClassName());
		}
		
		return $line;
	}
	
	private static function isLine( $points ) {
		return count($points) == 2;
	}
	
	private static function isLineRing( $points ) {
		$count = count($points);
		if ($count - count(array_unique($points, SORT_REGULAR) === 1)) {
			return ($points[0] == $points[$count - 1]);
		}
		return false;
	}
	
	
	private static function coordsToText(Point $point) {
		return  $point->getX() . ' ' . $point->getY();
	}
	
	private static function pointsToText($points) {
		$res = '';

		foreach ($points as $punto) {
			$res = $res . $punto->getX() . ' ' . $punto->getY() . ',';
		}
		return substr($res, 0, -1);
	}
	
	private static function polygonToTxt($polygon) {
		$res = '';
		
		$res .= '(' . self::pointsToText($polygon->getExteriorBoundary()->getPoints()) . '),';
		
		foreach ($polygon->getInteriorsBoundary() as $line) {
			$res .= '(' . self::pointsToText($line->getPoints()) . '),';
		}
		
		return substr($res, 0, -1);
	}
	
	/**
	 * Serealiza un objeto {@link Geometry} en formato WKT.
	 * 
	 * @param Geometry $geo objeto a serializar
	 */
	public static function toText( Geometry $geo ) {
		$class = get_class( $geo );
		switch ( $class ) {
			case Point::getClassName():
				$res = Point::getClassName() . '(' . self::coordsToText($geo) . ')';
				return $res;

			case LineString::getClassName():
			case Line::getClassName():
			case LineRing::getClassName():
				$res = LineString::getClassName() . '(' . self::pointsToText($geo->getPoints()) . ')';
				return $res;
				
			case Polygon::getClassName():
			case Surface::getClassName():
				$res = Polygon::getClassName() . '(' . self::polygonToTxt($geo) . ')';
				return $res;
				
			case MultiSurface::getClassName():
			case MultiPolygon::getClassName():		
				$res = MultiPolygon::getClassName() . '(';
				foreach ($geo->getCollection() as $polygon) {
					$res .= 	'(' . self::polygonToTxt($polygon) . '),';
				}
				
				$res = substr($res, 0, -1);
				$res .= ')';
				
				return $res;
				
			//TODO_GIS	
			default:
				throw new Exception("Not implemented yet");
			break;
		}
		
	}
	
}

?>