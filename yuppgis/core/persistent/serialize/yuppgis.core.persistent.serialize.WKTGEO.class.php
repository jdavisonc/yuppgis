<?php

class WKTGEO {
	
	private static $regex  = array (
		'class' => '/^(\w+)\((.*)\)/',
		'lineString' => '/([^,]*)(,[^,]*)*/',
		'polygon' => '/\([^\)]*\)/'
		
	);
	
	public static function fromText( $text ) {
		
		preg_match( self::$regex['class'], $text, $matches );
		$class = $matches[1];
		
		switch ( strtoupper($class) ) {
			case strtoupper(Point::getClassName()):
				$coord = explode(' ', $matches[2]);
				
				$res = new Point($coord[0], $coord[1]);
				$res->setClass($class);
				return $res;
			
			case strtoupper(LineString::getClassName()):
				//preg_match( self::$regex['lineString'], $matches[2], $pointsMatch );
				
				
				$line = self::createLine(explode(',',  $matches[2]));
				return $line;
				
			case strtoupper(Polygon::getClassName()):
				//POLYGON((0 0 0,4 0 0,4 4 0,0 4 0,0 0 0),(1 1 0,2 1 0,2 2 0,1 2 0,1 1 0))
				preg_match( self::$regex['polygon'], $matches[2], $linesMatch);
				
				//se hace parse del borde exterior
				//preg_match( self::$regex['lineString'], substr($linesMatch[1], 1, -1), $pointsMatch );
				$exteriorBoundary = self::createLine( explode(',',  substr($linesMatch[0], 1, -1)) );
				
				//se hace parse de los posibles bordes interiores 
				$interiorsBoundary = array();
				for ($i = 1; $i < count($linesMatch); $i++) {
					//preg_match( self::$regex['lineString'], substr($linesMatch[$i], 1, -1), $pointsMatch );
					$interiorsBoundary[] = 	self::createLine( explode(',',  substr($linesMatch[i], 1, -1)) );
				}
				
				return new Polygon($exteriorBoundary, $interiorsBoundary);
				
			default:
				throw new Exception("Not implemented yet");
		}
		
		return $res;
	}
	
	private static function createLine(array $points) {
		$res = array();
		for ($i = 0; $i < count($points); $i++) {
			$coord = explode(' ', $points[$i]);
			if ($coord[0] == '') {
				array_shift($coord);
			}
			$res[] =  new Point( $coord[0], $coord[1] );
		}
		
		if (self::isLine( $res )) {
			$line = new Line($res);
			$line->setClass(Line::getClassName());
		} elseif (self::isLineRing( $res )) {
			$line = new LineRing($res);
			$line->setClass(LineRing::getClassName());
		} else {
			$line = new LineString($res);
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
	
	// TODO_GIS: Hacer
	public static function toText( Geometry $geo ) {
		$class = get_class( $geo );
		switch ( $class ) {
			case Point::getClassName():
				$res = Point::getClassName() . '(' . $geo->getX() . ' ' . $geo->getY() . ')';
				return $res;

			case LineString::getClassName():
			case Line::getClassName():
			case LineRing::getClassName():
				$res = LineString::getClassName() . '(';
				$first = true;
				foreach ($geo->getPoints() as $punto) {
					if ($first) {
						$first = false;
					} else {
						$res = $res . ', ';
					}
					$res = $res . $punto->getX() . ' ' . $punto->getY();
				}
				$res = $res . ')';
				return $res;
				
			default:
				throw new Exception("Not implemented yet");;
			break;
		}
		
	}
	
}