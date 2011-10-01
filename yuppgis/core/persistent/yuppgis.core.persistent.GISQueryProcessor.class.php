<?php

class GISQueryProcessor {
	
	public function process() {
		
		//1. Armamos las dos bolsas de Selects
		self::processSelects();
		
		//2. Se ejecuta query sobre DB (no Geo)
		self::executeSimpleQuery();
		
		//3. Se arman las GISQuery con los res de 1 y 2 
		self::processGISQuerys();
		
		//4. 
		self::executeGISQuerys();
		
		//5. Se arma resultado final
		self::processResult();
		
	}
	
	/**
	 * A partir de los Selects y Froms originales genera los Selects para ir contra la base no geografica
	 * y los selects para ir contra la base geo
	 */
	private static function processSelects() {
		//TODO_GIS
	}
	
	/**
	 * Se encarga de ejececutar la query sobre la base de datos no geografica
	 */
	private static function executeSimpleQuery() {
		//TODO_GIS
	}
	
	/**
	 * Se encarga de armar las gis query
	 */
	private static function processGISQuerys() {
		//TODO_GIS
	}
	
	/**
	 * Se encarga de ejececutar las querys sobre la base de datos geografica
	 */
	private static function executeGISQuerys() {
		//TODO_GIS
	}
	
	/**
	 * Se arma el resultado
	 */
	private static function processResult() {
		//TODO_GIS
	}
	
}


?>