<?php

class TextGEO {
	
	private static $regex  = array (
		'point' => '/^(\w+)\((.*)\)/'
	);
	
	public static function fromText( $class, $text ) {
		switch ($class) {
			case Point::getClassName():
				preg_match( self::$regex['point'], $text, $matches );
				$coord = explode(' ', $matches[2]);
				return array( 'x' => $coord[0], 'y' => $coord[1] );
			
			default:
				throw new Exception("Not implemented yet");
		}
		
	}
	
	// TODO_GIS: Hacer
	public static function toText( Geometry $geo ) {
		$class = get_class( $geo );
		switch ( $class ) {
			case Point::getClassName():
				$res = Point::getClassName() . '(' . $geo->getX() . ' ' . $geo->getY() . ')';
				return $res;
			break;
			
			default:
				throw new Exception("Not implemented yet");;
			break;
		}
		
	}
	
}