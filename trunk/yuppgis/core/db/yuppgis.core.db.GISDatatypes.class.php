<?php

/**
 * Tipos de datos geograficos definidos.
 * 
 * @package yuppgis.core.db
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class GISDatatypes {
	
	/**
	 * Tipo Punto
	 */
	const POINT = "yuppgis_type_point";
	
	/**
	 * Tipo LineString
	 */
	const LINESTRING = "yuppgis_type_linestring";
	
	/**
	 * Tipo Linea
	 */
	const LINE = "yuppgis_type_line";
	
	/**
	 * Tipo Anillo
	 */
	const LINERING = "yuppgis_type_linering";
	
	/**
	 * Tipo Superficie
	 */
	const SURFACE = "yuppgis_type_surface";
	
	/**
	 * Tipo Poligono
	 */
	const POLYGON = "yuppgis_type_polygon";
	
	/**
	 * Tipo Curva
	 */
	const CURVE = "yuppgis_type_curve";
	
	/**
	 * Tipo Multi Puntos
	 */
	const MULTIPOINT = "yuppgis_type_multipoint";
	
	/**
	 * Tipo Multi Curvas
	 */
	const MULTICURVE = "yuppgis_type_multicurve";
	
	/**
	 * Tipo Multi LineString
	 */
	const MULTILINESTRING = "yuppgis_type_multilinestring";
	
	/**
	 * Tipo Multi Superficies
	 */
	const MULTISURFACE = "yuppgis_type_multisurface";
	
	/**
	 * Tipo Multi Poligonos
	 */
	const MULTIPOLYGON = "yuppgis_type_multipolygon";
	
	/**
	 * Tipo Coleccion de Geometrias
	 */
	const GEOMETRYCOLLECTION = "yuppgis_type_geometrycollection";
	
	/**
	 * Retorna el tipo de una geometria (@link Geometry)
	 * @param Geometry $geom
	 */
	public static function getTypeOf(Geometry $geom) {
		return self::getTypeByName(get_class($geom));
	}
	
	/**
	 * Retorna el tipo de un objeto geografico a partir de la geometria (@link Geometry)
	 * 
	 * @param string $geom
	 * @throws Exception en caso de no sea un nombre valido
	 */
	public static function getTypeByName($geom) {
		switch ($geom) {
			case Point::getClassName():
				return self::POINT;
			case LineString::getClassName():
				return self::LINESTRING;
			case LineRing::getClassName():
				return self::LINERING;
			case Line::getClassName():
				return self::LINE;
			case Surface::getClassName():
				return self::SURFACE;
			case Polygon::getClassName():
				return self::POLYGON;
			case Curve::getClassName():
				return self::CURVE;
			case MultiPoint::getClassName():
				return self::MULTIPOINT;
			case MultiCurve::getClassName():
				return self::MULTICURVE;
			case MultiLineString::getClassName():
				return self::MULTILINESTRING;
			case MultiSurface::getClassName():
				return self::MULTISURFACE;
			case MultiPolygon::getClassName():
				return self::MULTIPOLYGON;
			case GeometryCollection::getClassName():
				return self::GEOMETRYCOLLECTION;
			default:
				throw new Exception('Unsupported Geometry');
		}
	}

}

?>