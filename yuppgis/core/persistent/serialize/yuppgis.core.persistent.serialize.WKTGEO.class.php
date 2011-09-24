<?php

class WKTGEO {
	
	private static $regex  = array (
		'class' => '/^(\w+)\((.*)\)/',
		'lineString' => '/(.*)(,.*)+?/',
	);
	
	public static function fromText( $text ) {
		
		preg_match( self::$regex['class'], $text, $matches );
		$class = $matches[1];
		$res = array('class' => $class);
		
		switch ( strtoupper($class) ) {
			case strtoupper(Point::getClassName()):
				$coord = explode(' ', $matches[2]);
				$res['x'] = $coord[0];
				$res['y'] = $coord[1];
				break;
				
			case strtoupper(LineString::getClassName()):
				preg_match( self::$regex['lineString'], $matches[2], $pointsMatch );
				$points = array();
				
				for ($i = 1; $i < count($pointsMatch); $i++) {
					$coord = explode(' ', str_replace(', ', '', $pointsMatch[$i]));
					$points[$i - 1] =  array( 'x' => $coord[0], 'y' => $coord[1] );
				}
				
				$res['points'] = $points;
				
				if (self::isLine( $points )) {
					$res['class'] = Line::getClassName();
				} elseif (self::isLineRing( $points )) {
					$res['class'] = LineRing::getClassName();
				}
				break;
				
			default:
				throw new Exception("Not implemented yet");
		}
		
		return $res;
	}
	
	private static function isLine( $points ) {
		return count($points) == 2;
	}
	
	private static function isLineRing( $points ) {
		$count = count($points);
		if ($count - count(array_unique($points, SORT_REGULAR) === 1)) {
			return ($points[0] === $points[$count - 1]);
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