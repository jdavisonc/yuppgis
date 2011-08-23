<?php

class YuppGISConfig {
	
	public static $PROP_SRID = 'srid';
	
	private static $CONFIG_FILENAME = 'yuppgis_config.php';
	private static $APP_DIR = './apps/';
	private static $CONFIG_DIR = '/config/'; 

	private $app_gis_properties = array( );
	
	//TODO_GIS: Ver de inicializar con key constantes (xa que sean mas facilemnte accesibles desde afuera) y valores por defecto
	//private $default_values = array( self::$PROP_SRID => "the first element" );
	
	private static $instance = null;
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new YuppGISConfig();
		}
		return self::$instance;
	}
	
	public static function generatePath( $appName ) {
		return self::$APP_DIR . $appName . self::$CONFIG_DIR . self::$CONFIG_DIR; 
	}
	
	public function getAppGISProperty( $propertyName, $appName = null) {
		if ($appName !== null) {
			if (!array_key_exists($appName, $this->app_gis_properties)) {
				
				$appConfigFile = self::generatePath($appName);
				if (file_exists($appConfigFile)) {
					
					//include_once($appConfigFile);
					
					// TODO_GIS: Inicializar properties
					
				}
			}
			return $this->app_gis_properties[$appName][$propertyName];
		}
		return $this->default_values[$propertyName];
	}
	
}

?>