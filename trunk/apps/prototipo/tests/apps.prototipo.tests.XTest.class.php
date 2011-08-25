<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');

/**
 * Test de ejemplo
 * @author harley
 *
 */
class XTest extends YuppGISTestCase {
	
	public function testX() {
		
		$this->assert(true, 'anda');
		
	}
	
	public function testY() {
		
		$this->assertXMLEquals('<test><hola id="1"/></test>', '<test><hola /></test>', 'test XML');
		
	}
	
}

?>