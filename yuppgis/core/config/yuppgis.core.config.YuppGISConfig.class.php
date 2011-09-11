<?php

class YuppGISConfig {
	
	// Propiedades a almacenar
	const PROP_SRID = 'srid';
	const PROP_GISDB = 'gisdb';
	const PROP_GOOGLE_MAPS_KEY = 'google_maps_key';
	
	// Valores por defecto
	const DEFAULT_SRID = 32721;
	const DEFAULT_GISDB = null;
	const DEFAULT_GOOGLE_MAPS_KEY = 'ABQIAAAA9a4X6TFheB81m4gfqmoVHRT2yXp_ZAY8_ufC3CFXhHIE1NvwkxTQ99cJQVFQa1QeiRqp7S_AHD65MQ'; // localhost 
	
	// Propiedades de configuracion
	const CONFIG_FILENAME = 'yuppgis_config.php';
	const APP_DIR = './apps/';
	const CONFIG_DIR = '/config/'; 

	private $app_gis_properties = array( );
	private $default_values = array( );
	private $currentMode = null;
	
	private static $instance = null;
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new YuppGISConfig();
		}
		return self::$instance;
	}
	
	/**
	 * Se iniciliazan valores por defecto
	 */
	public function __construct() {
		$this->currentMode = YuppConfig::getInstance()->getCurrentMode();
		
		$this->default_values[self::PROP_SRID] = self::DEFAULT_SRID;
		$this->default_values[self::PROP_GISDB] = self::DEFAULT_GISDB;
		$this->default_values[self::PROP_GOOGLE_MAPS_KEY] = self::DEFAULT_GOOGLE_MAPS_KEY;
	}
	
	public static function generatePath( $appName ) {
		return self::APP_DIR . $appName . self::CONFIG_DIR . self::CONFIG_FILENAME; 
	}
	
	/**
	 * Retorna la propiedad pedida, en caso de no existir para la aplicacion, retorna su valor por defecto
	 * @param $propertyName
	 * @param $appName
	 */
	public function getGISPropertyValue( $appName = null, $propertyName) {
		if ($appName !== null) {
			if (!array_key_exists($appName, $this->app_gis_properties)) {
				
				$appConfigFile = self::generatePath($appName);
				if (file_exists($appConfigFile)) {
					
					// Obtengo variables del archivo de configuracion
					include_once($appConfigFile);
					
					// Inicializo arrays
					$this->app_gis_properties[$appName][self::PROP_SRID] = ${self::PROP_SRID};
					$this->app_gis_properties[$appName][self::PROP_GISDB] = ${self::PROP_GISDB}[$this->currentMode];
					$this->app_gis_properties[$appName][self::PROP_GOOGLE_MAPS_KEY] = ${self::PROP_GOOGLE_MAPS_KEY};
					
					return $this->app_gis_properties[$appName][$propertyName];		
				}
			} else {
				return $this->app_gis_properties[$appName][$propertyName];			
			}
		}
		return $this->default_values[$propertyName];
	}
	
}

?>