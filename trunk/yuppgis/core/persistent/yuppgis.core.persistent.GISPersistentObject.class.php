<?php

// Se importa datatypes geograficos
YuppLoader :: load('yuppgis.core.db', 'GISDatatypes');

// Se importa modelo de datos geograficos
YuppLoader :: load('yuppgis.core.basic', 'Geometry');
YuppLoader :: load('yuppgis.core.basic', 'Point');

class GISPersistentObject extends PersistentObject {
	
	public function addAttribute($name, $type) {
		
		switch($type) {
		    case GISDatatypes::POINT:
		    case GISDatatypes::LINE:
		    case GISDatatypes::LINERING:
		    case GISDatatypes::LINESTRING:
		    case GISDatatypes::POLYGON:
		        parent::addHasOne($name, Point::getClassName());
		    	break;
		    default;
		        parent::addAttribute($name, $type);
		    break;
		}
	}
	
	
}

?>