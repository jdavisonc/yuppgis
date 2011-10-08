<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');
YuppLoader::load('prototipo.model', 'Paciente');
YuppLoader::load('prototipo.model', 'Street');
YuppLoader::load('yuppgis.core.basic.ui', 'UIProperty');
YuppLoader::load('yuppgis.core.basic.ui', 'Icon');

/**
 * Test para probar configuracion de objetos geograficos con modelo de datos
 * @author harley
 */
class GISPersistentObjectTest extends YuppGISTestCase {

	private $paciente = null;
	private $street = null;

	/**
	 * Prueba para probar configuracion de Punto
	 */
	public function testAssignPointToPatient() {
		$paciente = new Paciente();
		$paciente->setUbicacion(new Point(23, 32));

		$point = $paciente->getUbicacion();

		$this->assert($point->getX() == 23, 'Test punto X:'.$point->getX());
		$this->assert($point->getY() == 32, 'Test punto Y:'.$point->getY());
	}
	
	function testSavePatientWithLocation() {
		$point = new Point(23, 32);
		$point->setUIProperty(new Icon(0,0,'opa',10,10));

		$paciente = new Paciente();
		$paciente->setNombre('Ernestino');
		$paciente->setUbicacion($point);
		$paciente->save();
		
		$this->assert($paciente != null && $paciente->getUbicacion()->getId() != null, 
			'Test de persistencia de Paciente (id = '.$paciente->getId().') con Punto (id = '.$point->getId().
			', X = '.$point->getX().', Y = '.$point->getY().') ');
		
		$this->paciente = $paciente;
	}
	
	

	function testUpdatePatientWithLocation() {
		$point = $this->paciente->getUbicacion();
		$point->setX(456);
		$this->paciente->save();
		
		$this->assert($this->paciente != null && $point->getId() != null, 
			'Test de actualizacion de Paciente (id = '.$this->paciente->getId().') con Punto (id = '.$point->getId().
			', X = '.$point->getX().', Y = '.$point->getY().') ');
	}
	
	function testRemovePatient() {
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
	
	function testGetPacienteFromDBWithId() {
		$p = Paciente::get(1);
		$ubi = $p->getUbicacion();
		$this->assert($ubi !== null, 'Se obtuvo correctamente el paciente con ID=1');
	}
	
	function testSaveStreet() {
		$s = new Street();
		$s->setName('Calle Para probar');
		
		$puntos = array ( new Point(23, 32), new Point(32, 82));
		$s->setData(new LineString($puntos));
		
		$s -> save();
		$this->street = $s;
		$this->assert($s->getId() !== null, 'Se guardo la Calle con una linea ');
		
	}
	
	function testGetStreet() {
		$id = $this->street->getId();
		$p = Street::get($id);
		$line = $p->getData();
		$this->assert($line !== null, 'Se obtuvo correctamente la calle con ID = '. $id);

	}
	
	function testRemoveStreet() {
		$id = $this->street->getId();
		$this->street->delete();
		
		//TODO
		$this->assert(0 == 0, 'Test de borrado de Calle');
	}

}

?>