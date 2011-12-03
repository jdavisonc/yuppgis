<?php

YuppLoader :: load('yuppgis.core.config', 'YuppGISConfig');

/**
 * Clase que define los parametros que se le puede pasar al constructor del helper de Mapas
 * 
 * @package yuppgis.core.gis
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class MapParams{
	
	/**
	 * Parametro que represeenta el id del mapa que se va a mostrar
	 */
	const ID = "Id";
	
	/**
	 * Url para obtener mapa de google
	 */
	const GOOGLE_MAPS_URL = "GoogleMapsUrl";
	
	/**
	 * Parametro que permite habilitar el parametro 'sphericalMarcator' sobre google maps 
	 */
	const SPHERICAL_MERCATOR = 'SphericalMercator';
	
	/**
	 * Ancho del mapa
	 */
	const WIDTH = "Width";
	
	/**
	 * Alto del mapa
	 */
	const HEIGHT = "height";
	
	/**
	 * Parametro para definir las propiedades del borde del mapa
	 */
	const BORDER = "border";
	
	/**
	 * Parametro para asignar el manejador del evento click sobre el mapa
	 */
	const CLICK_HANDLERS = "clickhandlers";
	
	/**
	 * Parametro para asignar el manejador del evento seleccionar sobre el mapa
	 */
	const SELECT_HANDLERS = "selecthandlers";
	
	/**
	 * Parametro para asignar el tipo de mapa (Google map o MapServer)
	 */
	const TYPE = "maptype";
	
	/**
	 * Parametro para conserver los parametros que se pesaron a la vista que muestra el mapa
	 */
	const STATE = "state";
	
	/**
	 * Parametro para asignar el SRID que se usa para el mapa
	 */
	const SRID = "srid";
	
	/**
	 * Parametro para asignar en que punto se encuentra el centro del mapa
	 */
	const CENTER = "center";
	
	/**
	 * Parametro para asignar el zoom al mapa
	 */
	const ZOOM = "zoom";
	
	/**
	 * Retorna el valor del parametro
	 * 
	 * @param array $array array con los parametros pasados a un mapa
	 * @param strin $key nombre del parametro del cual se quiere obtener el valor
	 */
	public static function getValueOrDefault($array, $key)
	{
		if($array != null && array_key_exists ( $key , $array ))			
			return $array[$key];
		else{
			$appName = YuppContext::getInstance()->getApp();
			$gmaps_key = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_GOOGLE_MAPS_KEY);
			$srid = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_SRID);
			
			switch ($key){
				case MapParams::ID:
					return -1;
				case MapParams::GOOGLE_MAPS_URL:
					return "http://maps.google.com/maps?file=api&v=2&key=". $gmaps_key;
				case MapParams::SPHERICAL_MERCATOR:
					return true;
				case MapParams::WIDTH:
					return "500px";
				case MapParams::HEIGHT:
					return "250px";
				case MapParams::BORDER:
					return "1px solid black";			  
				case MapParams::CLICK_HANDLERS:
					return array();
				case MapParams::SELECT_HANDLERS:
					return array();
				case MapParams::TYPE:
					return "google";
				case MapParams::STATE:
					return "";
				case MapParams::SRID:
					return $srid;
				case MapParams::CENTER:
					return array("-6251096.6093197", "-4149355.4159976");
				case MapParams::ZOOM:
					return 3;
			}
			
		}			
	}	
}
?>