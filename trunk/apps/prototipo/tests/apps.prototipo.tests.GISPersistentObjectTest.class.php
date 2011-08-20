<?php

YuppLoader::load('core.testing', 'TestCase');
YuppLoader::load('prototipo.model', 'Paciente');

YuppLoader::load('yuppgis.core.persistent.serialize', 'TextGEO');

/**
 *
 * Test para probar configuracion de objetos geograficos con modelo de datos
 * @author harley
 */
class GISPersistentObjectTest extends TestCase {

	public function run() {
		$this->testPoint();
		$this->testGetPoint();
		$this->testTextParserPoint();
		$this->testListAllPoint();
		$this->testSavePoint();
	}

	/**
	 * Prueba para probar configuracion de Punto
	 */
	public function testPoint() {
		$paciente = new Paciente();
		$paciente->setUbicacion(new Point(array('x' => 23, 'y' => 32)));

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
	
	public function testListAllPoint() {
		$pacientes = Paciente::listAll( new ArrayObject(array()) );
		$point = $pacientes[0]->getUbicacion();
		
		$this->assert($pacientes != null, 'Test list all', $pacientes);
	}
	
	public function testTextParserPoint() {
		$text = 'POINT(23 32)';
		$point = TextGEO::fromText(Point::getClassName(), $text);
		
		$this->assert($point == array('x' => 23, 'y' => 32), 'Test parseo punto');
	}
	
	function testSavePoint() {
		$paciente = new Paciente();
		$paciente->setNombre('Ernestino');
		$paciente->setUbicacion(new Point(array('x' => 23, 'y' => 32)));
		$paciente->save();
		
	}

}

?>