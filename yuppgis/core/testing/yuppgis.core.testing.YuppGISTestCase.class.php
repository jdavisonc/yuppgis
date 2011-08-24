<?php

YuppLoader::load('core.testing', 'TestCase');

YuppLoader::load('yuppgis.core.utils', 'ReflectionUtils');

/**
 * Clase para facilitar los test unitarios, por defecto ejecuta todos los metodos que 
 * cuyo nombre comience con la palabra 'test'.
 *  
 * @author harley
 */
abstract class YuppGISTestCase extends TestCase {
	
	public function run() {
		
		// Obtengo metodos de clase
		$tests = ReflectionUtils::ReflectMethods(get_called_class(), 'test', false);
		
		// Ejecuto metodos obtenidos por reflection
		foreach ( $tests as $test ) {
			$this->$test();
		}
		
	}
	
}

?>