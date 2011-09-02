<?php

class YuppGISConfig {
	
	// Propiedades a almacenar
	public static $PROP_SRID = 'srid';
	public static $PROP_GISDB = 'gisdb';
	
	// Valores por defecto
	private static $DEFAULT_SRID = 32721;
	private static $DEFAULT_GISDB = null;
	
	// Propiedades de configuracion
	private static $CONFIG_FILENAME = 'yuppgis_config.php';
	private static $APP_DIR = './apps/';
	private static $CONFIG_DIR = '/config/'; 

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
		
		$this->default_values[self::$PROP_SRID] = self::$DEFAULT_SRID;
		$this->default_values[self::$PROP_GISDB] = self::$DEFAULT_GISDB;
	}
	
	public static function generatePath( $appName ) {
		return self::$APP_DIR . $appName . self::$CONFIG_DIR . self::$CONFIG_FILENAME; 
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
					$this->app_gis_properties[$appName][self::$PROP_SRID] = $srid;
					$this->app_gis_properties[$appName][self::$PROP_GISDB] = $gisdb[$this->currentMode];
					
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