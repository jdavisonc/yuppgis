<?php

YuppLoader::load('core.testing', 'TestCase');
YuppLoader::load('prototipo.model', 'Paciente');

/**
 *
 * Test para probar configuracion de objetos geograficos con modelo de datos
 * @author harley
 */
class GISPersistentObjectTest extends TestCase {

	public function run() {
		//$this->testPoint();
		$this->testGetPoint();
	}

	/**
	 * Prueba para probar configuracion de Punto
	 */
	public function testPoint() {
		$paciente = new Paciente();
		$paciente->setUbicacion(new Point(23, 32));

		$point = $paciente->getUbicacion();

		$this->assert($point->getX() == 23, 'Test punto X:'.$point->getX());
		$this->assert($point->getY() == 32, 'Test punto Y:'.$point->getY());
	}

	public function testGetPoint() {
		$paciente = Paciente::get(1);

		$point = $paciente->getUbicacion();

		$this->assert($point->getX() == 23, 'Test punto X:'.$point->getX());
		$this->assert($point->getY() == 32, 'Test punto Y:'.$point->getY());
	}

}

?>