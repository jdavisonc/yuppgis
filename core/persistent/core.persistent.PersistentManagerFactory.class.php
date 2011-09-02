<?php

YuppLoader :: load('core.persistent', 'PersistentManager');
YuppLoader :: load('yuppgis.core.persistent', 'GISPersistentManager');
YuppLoader :: load('yuppgis.core.persistent', 'GISPMPremium');

class PersistentManagerFactory {
	
	private static $manager = null;
	
	public static function getManager( $load_estragegy = NULL ) {
		if (!self::$manager){
			// TODO_GIS selecciona el PM segun config
			self::$manager = new GISPMPremium( $load_estragegy );
		}
		return self::$manager;
   }
   
   
}