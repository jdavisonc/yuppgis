<?php

class GISDatatypes {
	
	const POINT = "yuppgis_type_point";
	const LINESTRING = "yuppgis_type_linestring";
	const LINE = "yuppgis_type_line";
	const LINERING = "yuppgis_type_linering";
	const SURFACE = "yuppgis_type_surface";
	const POLYGON = "yuppgis_type_polygon";
	const CURVE = "yuppgis_type_curve";
	const MULTIPOINT = "yuppgis_type_multipoint";
	const MULTICURVE = "yuppgis_type_multicurve";
	const MULTILINESTRING = "yuppgis_type_multilinestring";
	const MULTISURFACE = "yuppgis_type_multisurface";
	const MULTIPOLYGON = "yuppgis_type_multipolygon";
	const GEOMETRYCOLLECTION = "yuppgis_type_geometrycollection";
	
	public static function getTypeOf(Geometry $geom) {
		switch (get_class($geom)) {
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