<?php

// Se importa datatypes geograficos
YuppLoader :: load('yuppgis.core.db', 'GISDatatypes');

// Se importa modelo de datos geograficos
YuppLoader :: load('yuppgis.core.basic', 'Geometry');
YuppLoader :: load('yuppgis.core.basic', 'Point');
YuppLoader :: load('yuppgis.core.basic', 'Curve');
YuppLoader :: load('yuppgis.core.basic', 'LineString');
YuppLoader :: load('yuppgis.core.basic', 'Line');

// Se importan dependencias de persistencia
YuppLoader :: load('yuppgis.core.config', 'YuppGISConventions');

class GISPersistentObject extends PersistentObject {
	
	public function __construct($args = array (), $isSimpleInstance = false) {
		
		$this->setWithTable('gis_persistent_object');
		
		$this->addAttribute('app', Datatypes::TEXT);
		
		$ctx = YuppContext::getInstance();
		$appName = null;
		if ($ctx->isAnotherApp()) {
			$appName = $ctx->getRealApp();
		} else {
			$appName = $ctx->getApp();
		}
		$args['app'] = $appName;
		
		parent :: __construct($args, $isSimpleInstance);
	}
	
	public function addAttribute($name, $type) {
		
		switch($type) {
		    case GISDatatypes::POINT:
		        parent::addHasOne($name, Point::getClassName());
		    	break;
		    case GISDatatypes::LINESTRING:
		    	parent::addHasOne($name, LineString::getClassName());
		    	break;
		    case GISDatatypes::LINE:
		    case GISDatatypes::LINERING:
		    
		    case GISDatatypes::POLYGON:
		    	break;
		    default;
		        parent::addAttribute($name, $type);
		    	break;
		}
	}

	public function aGetObject( $attr, $id ) {
		if (is_subclass_of($this->hasOne[$attr], Geometry :: getClassName())) {
			return PersistentManagerFactory::getManager()->get_gis_object( $this->getWithTable() , $attr, $this->hasOne[$attr], $id );
		} else {
			return parent::aGetObject($attr, $id);
		}
	}
	
	/**
	 * Retorna nombre de clase.
	 * Solo soportado por PHP > 5.3
	 * @return nombre de la clase
	 */
	public static function getClassName() {
        return get_called_class();
    }
    
}

?>