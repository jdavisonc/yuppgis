<?php

YuppLoader::load('core.testing', 'TestCase');

YuppLoader::load('yuppgis.core.utils', 'ReflectionUtils');
YuppLoader::load('yuppgis.core.utils', 'XMLUtils');

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
	
	public function assert($cond, $msg = 'Error') {
		$trace = debug_backtrace();
		parent::assert($cond, get_called_class() . '::' . $trace[1]['function'] . '() - ' . $msg);
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
	
	/**
	 * Compara dos XML segun contenido y estructura
	 * @param $xmlExpected
	 * @param $xmlActual
	 * @param $msg
	 */
	public function assertXMLEquals($xmlExpected, $xmlActual, $msg) {
		
		$expected = simplexml_load_string($xmlExpected);
		$actual = simplexml_load_string($xmlActual);
		
		$result = XMLUtils::xml_is_equal($expected, $actual);
		if ($result === true) {
			$this->assert(true, $msg);
		} else {
			$this->assert(false, $msg ." - ". "XML documents are different: $result");
		}
	}
	
}

?>