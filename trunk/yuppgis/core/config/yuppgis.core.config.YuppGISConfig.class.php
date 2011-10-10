<?php

class YuppGISConfig {
	
	// Propiedades a almacenar
	const PROP_SRID = 'srid';
	const PROP_GISDB = 'gisdb';
	const PROP_GOOGLE_MAPS_KEY = 'google_maps_key';
	const PROP_YUPPGIS_MODE = "yuppgis_mode";
		
	// Valores por defecto
	const DEFAULT_SRID = 32721;
	const DEFAULT_GISDB = null;
	const DEFAULT_GOOGLE_MAPS_KEY = 'ABQIAAAA9a4X6TFheB81m4gfqmoVHRT2yXp_ZAY8_ufC3CFXhHIE1NvwkxTQ99cJQVFQa1QeiRqp7S_AHD65MQ'; // localhost
	
	const MODE_BASIC = 'basic';
	const MODE_PREMIUM = 'premium';   
	
	// Propiedades de configuracion
	const CONFIG_FILENAME = 'yuppgis_config.php';
	const APP_DIR = './apps/';
	const CONFIG_DIR = '/config/'; 

	private $app_gis_properties = array( );
	private $default_values = array( );
	
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
		$this->default_values[self::PROP_SRID] = self::DEFAULT_SRID;
		$this->default_values[self::PROP_GISDB] = self::DEFAULT_GISDB;
		$this->default_values[self::PROP_GOOGLE_MAPS_KEY] = self::DEFAULT_GOOGLE_MAPS_KEY;
		$this->default_values[self::PROP_YUPPGIS_MODE] = null;
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
					$this->loadPropertiesFile($appName, $appConfigFile);
					return $this->app_gis_properties[$appName][$propertyName];		
				}
			} else {
				return $this->app_gis_properties[$appName][$propertyName];			
			}
		}
		return $this->default_values[$propertyName];
	}
	
	/**
	 * Carga las propiedades segun una aplicacion y su archivo de configuracion
	 * 
	 * @param $appName
	 * @param $appConfigFile
	 */
	private function loadPropertiesFile($appName, $appConfigFile) {
		// Obtengo variables del archivo de configuracion
		include_once($appConfigFile);
		
		// Inicializo arrays
		$this->app_gis_properties[$appName][self::PROP_GISDB] = $this->getValue(${self::PROP_GISDB}, self::PROP_GISDB);
		$this->app_gis_properties[$appName][self::PROP_SRID] = $this->getValue(${self::PROP_SRID}, self::PROP_SRID);
		$this->app_gis_properties[$appName][self::PROP_GOOGLE_MAPS_KEY] = $this->getValue(${self::PROP_GOOGLE_MAPS_KEY}, self::PROP_GOOGLE_MAPS_KEY);
		$this->app_gis_properties[$appName][self::PROP_YUPPGIS_MODE] = $this->getValue(${self::PROP_YUPPGIS_MODE}, self::PROP_YUPPGIS_MODE);
	}
	
	/**
	 * Funcion que retorna una variable si esta existe, y sino retorna su valor por defecto
	 * @param $var 
	 * @param $varName
	 */
	private function getValue(&$var, $varName) {
		return (isset($var)) ? $var : $this->default_values[$varName];
	}
}

?>