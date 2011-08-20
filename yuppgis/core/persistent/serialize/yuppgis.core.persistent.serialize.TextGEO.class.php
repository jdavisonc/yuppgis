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
	public static function toText(Geometry $geo) {
		throw new Exception("Not implemented yet");
	}
	
}