<?php

// Se importa datatypes geograficos
YuppLoader :: load('yuppgis.core.db', 'GISDatatypes');

// Se importa modelo de datos geograficos
YuppLoader :: load('yuppgis.core.basic', 'Geometry');
YuppLoader :: load('yuppgis.core.basic', 'Point');

// Se importan dependencias de persistencia
YuppLoader :: load('yuppgis.core.persistent', 'GISPersistentManager');
YuppLoader :: load('yuppgis.core.config', 'YuppGISConventions');

 abstract class GISPersistentObject extends PersistentObject {
	
	public function addAttribute($name, $type) {
		
		switch($type) {
		    case GISDatatypes::POINT:
		        parent::addHasOne($name, Point::getClassName());
		    	break;
		    case GISDatatypes::LINE:
		    case GISDatatypes::LINERING:
		    case GISDatatypes::LINESTRING:
		    case GISDatatypes::POLYGON:
		    	break;
		    default;
		        parent::addAttribute($name, $type);
		    	break;
		}
	}

	public function aGetObject( $attr, $id ) {
		if (is_subclass_of($this->hasOne[$attr], Geometry :: getClassName())) {
			return GISPersistentManager::getInstance()->get_gis_object( $this->getWithTable() , $attr, $this->hasOne[$attr], $id );
		} else {
			return parent::aGetObject($attr, $id);
		}
	}
	
}

?>