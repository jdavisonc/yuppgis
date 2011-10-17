<?php

YuppLoader :: load('core.persistent', 'PersistentManager');
YuppLoader :: load('yuppgis.core.persistent', 'GISPersistentManager');
YuppLoader :: load('yuppgis.core.persistent', 'GISPMPremium');
YuppLoader :: load('yuppgis.core.persistent', 'GISPMBasic');

class PersistentManagerFactory {
	
	private static $manager = null;
	
	public static function getManager($load_estragegy = NULL) {
		if (!self::$manager){
			$appName = YuppContext::getInstance()->getApp();
			self::$manager = self::getManagerInstanceByAppName($load_estragegy, $appName);
		}
		return self::$manager;
   }
   
   /**
    * 
    * Retorna una nueva instancia del Manager dependiendo de la aplicacion 
    * @param $load_estragegy
    * @param $appName
    */
   public static function getManagerInstanceByAppName ( $load_estragegy = NULL, $appName) {
   			
		$mode = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_YUPPGIS_MODE);
		if ($mode === YuppGISConfig::MODE_PREMIUM) {
			return new GISPMPremium( $load_estragegy, $appName );
		} else if ($mode === YuppGISConfig::MODE_BASIC) {
			return new GISPMBasic( $load_estragegy, $appName );
		} else {
			return new PersistentManager( $load_estragegy );
		}
   }
   
   
}

?>