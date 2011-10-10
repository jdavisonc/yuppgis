<?php

YuppLoader :: load('core.persistent', 'PersistentManager');
YuppLoader :: load('yuppgis.core.persistent', 'GISPersistentManager');
YuppLoader :: load('yuppgis.core.persistent', 'GISPMPremium');
YuppLoader :: load('yuppgis.core.persistent', 'GISPMBasic');

class PersistentManagerFactory {
	
	private static $manager = null;
	
	public static function getManager( $load_estragegy = NULL ) {
		if (!self::$manager){
			$appName = YuppContext::getInstance()->getApp();
			$mode = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_YUPPGIS_MODE);
			if ($mode === YuppGISConfig::MODE_PREMIUM) {
				self::$manager = new GISPMPremium( $load_estragegy );
			} else if ($mode === YuppGISConfig::MODE_BASIC) {
				self::$manager = new GISPMBasic( $load_estragegy );
			} else {
				self::$manager = new PersistentManager( $load_estragegy );
			}
		}
		return self::$manager;
   }
   
   
}

?>