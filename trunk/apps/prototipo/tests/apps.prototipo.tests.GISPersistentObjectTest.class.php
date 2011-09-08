<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');
YuppLoader::load('prototipo.model', 'Paciente');

/**
 * Test para probar configuracion de objetos geograficos con modelo de datos
 * @author harley
 */
class GISPersistentObjectTest extends YuppGISTestCase {

	private $paciente = null;

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

	/*
	public function testListAllPoint() {
		$pacientes = Paciente::listAll( new ArrayObject(array()) );
		$point = $pacientes[0]->getUbicacion();
		
		$this->assert($pacientes != null, 'Test list all', $pacientes);
	}*/
	
	public function testTextParserPoint() {
		$text = 'POINT(23 32)';
		$point = WKTGEO::fromText(Point::getClassName(), $text);
		
		$this->assert($point == array('x' => 23, 'y' => 32), 'Test parseo punto');
	}
	
	function testSavePoint() {
		$paciente = new Paciente();
		$paciente->setNombre('Ernestino');
		$point = new Point(23, 32);
		$paciente->setUbicacion($point);
		$paciente->getUbicacion()->setUIProperty(new Icon(0,0,'opa',10,10));
		$paciente->save();
		
		$this->assert($paciente != null && $paciente->getUbicacion()->getId() != null, 
			'Test de persistencia de Paciente (id = '.$paciente->getId().') con Punto (id = '.$point->getId().
			', X = '.$point->getX().', Y = '.$point->getY().') ');
		
		$this->paciente = $paciente;
	}
	
	function testJSON() {
		$this->paciente->getUbicacion()->setUIProperty(new Icon(0,0,'opa',10,10));
	}
	
	
	function testUpdatePoint() {
		$point = $this->paciente->getUbicacion();
		$point->setX(456);
		$this->paciente->save();
		
		$this->assert($this->paciente != null && $point->getId() != null, 
			'Test de actualizacion de Paciente (id = '.$this->paciente->getId().') con Punto (id = '.$point->getId().
			', X = '.$point->getX().', Y = '.$point->getY().') ');
	}
	
	function testRemovePaciente() {
		$id = $this->paciente->getId();
		$this->paciente->delete();
		
		$deleted = false;
		try {
			$paciente = Paciente::get($id);
		} catch (Exception $e) {
			$deleted = true;
		}
		$this->assert($deleted, 'Test de borrado de Paciente');
	}
	
	function testFindBy() {
		$and = Condition::_AND()
			->add(GISCondition::ISCONTAINED(
				YuppGISConventions::tableName(Paciente::getClassName()), 
				'ubicacion', new Point(10, 10)))
			->add(Condition::EQ(YuppGISConventions::tableName(Paciente::getClassName()), 'nombre', 'Roberto'));
		
		$pacientes = Paciente::findBy($and, new ArrayObject());
		
		$this->assert($pacientes !== null, 'Test de filtrado de pacientes');
		
	}

}

?>