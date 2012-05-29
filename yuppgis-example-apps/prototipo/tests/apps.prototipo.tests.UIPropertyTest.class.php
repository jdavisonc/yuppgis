<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');

/**
 * Test para probar la serializacion de JSON a UIProperty
 * @author harley
 */
class UIPropertyTest extends YuppGISTestCase {
	
	public function testSerializationOfIcon() {
		$uip = new Icon(60, 10, 'http://host/oneimage.jpg', 20, 10);
		$json = UIProperty::toJSON($uip);
		
		$icon2 = UIProperty::fromJSON( $json );
		$this->assert($icon2->getUrl() === $uip->getUrl(), 'Test json2');
	}
	
}