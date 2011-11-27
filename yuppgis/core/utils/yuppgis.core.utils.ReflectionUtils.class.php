<?php

/**
 * Clase que brinda utilidades de reflection
 * 
 * @package yuppgis.core.utils
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class ReflectionUtils {
	
	/**
	 * Retorna los metodos de la clase que terminen/comiencen con el token pasado
	 * @param $class
	 * @param $token
	 * @param $issuffix
	 * @throws Exception
	 */
	public static function ReflectMethods($class, $token, $issuffix=true){
		if (!class_exists($class)){
			throw new Exception("La clase $class no existe o no está cargada");
		}else{
			$methods = get_class_methods($class);
			$actions =  array();
			foreach ($methods as $method){
				if (String::endsWith($method, $token) && $issuffix){
					array_push($actions, $method);
				}else{
					if (String::startsWith($method, $token) && !$issuffix){
						array_push($actions, $method);
					}
				}
			}
			return $actions;
		}
	}
	
}

?>