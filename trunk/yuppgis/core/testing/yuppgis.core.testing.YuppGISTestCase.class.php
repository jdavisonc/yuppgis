<?php

YuppLoader::load('core.testing', 'TestCase');

YuppLoader::load('yuppgis.core.utils', 'ReflectionUtils');

/**
 * Clase que facilita los test unitarios, por defecto ejecuta todos los metodos que 
 * cuyo nombre comience con la palabra 'test'.
 * Ej: testPersistencia();
 *  
 * @author harley
 */
abstract class YuppGISTestCase extends TestCase {
	
	public function run() {
		$tests = ReflectionUtils::ReflectMethods(get_called_class(), 'test', false);
		
		// Ejecuto metodos obtenidos por reflection
		foreach ( $tests as $test ) {
			$this->$test();
		}
	}
	
	public function assertEquals($expected, $actual, $msg) {
		$this->assert($expected === $actual, $msg);
	}
	
	public function assertNotEquals($expected, $actual, $msg) {
		$this->assert($expected !== $actual, $msg);
	}
	
	public function assertNull($actual, $msg) {
		$this->assert($actual == null, $msg);
	}
	
	public function assertNotNull($actual, $msg) {
		$this->assert($actual != null, $msg);
	}
	
	public function assertXMLEquals($xmlExpected, $xmlActual, $msg) {
		$expected = new DOMDocument;
        $expected->preserveWhiteSpace = FALSE;
        $expected->loadXML($xmlExpected);

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = FALSE;
        $actual->loadXML($xmlActual);
        
        $this->assert($expected == $actual, $msg);
	}
	
}

?>