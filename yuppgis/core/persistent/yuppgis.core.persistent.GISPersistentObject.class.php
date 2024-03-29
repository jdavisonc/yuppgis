<?php

// Se importa datatypes geograficos
YuppLoader :: load('yuppgis.core.db', 'GISDatatypes');

// Se importa modelo de datos geograficos
YuppLoader :: load('yuppgis.core.basic', 'Geometry');
YuppLoader :: load('yuppgis.core.basic', 'Point');
YuppLoader :: load('yuppgis.core.basic', 'Curve');
YuppLoader :: load('yuppgis.core.basic', 'LineString');
YuppLoader :: load('yuppgis.core.basic', 'Line');
YuppLoader :: load('yuppgis.core.basic', 'LineRing');
YuppLoader :: load('yuppgis.core.basic', 'Surface');
YuppLoader :: load('yuppgis.core.basic', 'Polygon');
YuppLoader :: load('yuppgis.core.basic', 'GeometryCollection');
YuppLoader :: load('yuppgis.core.basic', 'MultiSurface');
YuppLoader :: load('yuppgis.core.basic', 'MultiPoint');
YuppLoader :: load('yuppgis.core.basic', 'MultiCurve');
YuppLoader :: load('yuppgis.core.basic', 'MultiLineString');
YuppLoader :: load('yuppgis.core.basic', 'MultiPolygon');
YuppLoader::load('yuppgis.core.basic', 'Observable');

// Se importan dependencias de persistencia
YuppLoader :: load('yuppgis.core.config', 'YuppGISConventions');

/**
 * Clase que tiene la logica para modelar objetos con objetos geograficos 
 * 
 * @package yuppgis.core.persistent
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class GISPersistentObject extends Observable {

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

	/**
	 * Funcion usada para agregar un atributo a un objeto del modelo
	 * @see PersistentObject::addAttribute()
	 */
	public function addAttribute($name, $type) {

		switch($type) {
			case GISDatatypes::POINT:
				parent::addHasOne($name, Point::getClassName());
				break;
			case GISDatatypes::CURVE:
				parent::addHasOne($name, MultiPoint::getClassName());
				break;
			case GISDatatypes::LINESTRING:
				parent::addHasOne($name, LineString::getClassName());
				break;
			case GISDatatypes::LINE:
				parent::addHasOne($name, Line::getClassName());
				break;
			case GISDatatypes::LINERING:
				parent::addHasOne($name, LineRing::getClassName());
				break;
			case GISDatatypes::SURFACE:
				parent::addHasOne($name, Surface::getClassName());
				break;
			case GISDatatypes::POLYGON:
				parent::addHasOne($name, Polygon::getClassName());
				break;
			case GISDatatypes::MULTIPOINT:
				parent::addHasOne($name, MultiPoint::getClassName());
				break;
			case GISDatatypes::MULTILINESTRING:
				parent::addHasOne($name, MultiLineString::getClassName());
				break;
			case GISDatatypes::MULTICURVE:
				parent::addHasOne($name, MultiCurve::getClassName());
				break;
			case GISDatatypes::MULTISURFACE:
				parent::addHasOne($name, MultiSurface::getClassName());
				break;
			case GISDatatypes::MULTIPOLYGON:
				parent::addHasOne($name, MultiPolygon::getClassName());
				break;
			case GISDatatypes::GEOMETRYCOLLECTION:
				parent::addHasOne($name, MultiPoint::getClassName());
				break;
			default;
			parent::addAttribute($name, $type);
			break;
		}
	}

	/**
	 * Funcion que retorna los atributos que son una geometria (@link Geometry) de un objeto
	 */
	public function hasGeometryAttributes()
	{
		$res = array();
		foreach ($this->hasOne as $attrname => $hmclazz) {
			if ($hmclazz == Geometry::getClassName() || is_subclass_of($hmclazz, Geometry::getClassName())) {
				$res[] = $attrname;
			}
		}
		return $res;
	}

	/**
	 * Retorna un objeto asociado
	 * @see PersistentObject::aGetObject()
	 */
	public function aGetObject( $attr, $id ) {
		if (is_subclass_of($this->hasOne[$attr], Geometry :: getClassName())) {
			return PersistentManagerFactory::getManager()->get_gis_object( get_class($this) , $attr, $this->hasOne[$attr], $id );
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


	/**
	 * @see Observable::notifyObservers()
	 */
	public function notifyObservers($params){
		$string = $this->getObservers();
		$observers = explode(";", $string);
		array_shift($observers);

		foreach($observers as $observer) {
			$arr = explode("_", $observer);
			$classname = $arr[0];
			$id = $arr[1];

				
			$obj = call_user_func_array($classname.'::get',array($id));
			$obj->notify($this, $params);
				
		}

	}

}

?>