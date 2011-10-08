<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');

class GISQueryTest extends YuppGISTestCase {
	
	function testSimpleGISQueryWithoutSelect() {
		$q = new GISQuery();
		$q->addFrom(Paciente::getClassName(), 'p');
		$q->setCondition(Condition::EQ('p', 'nombre', 'Juan'));
		
		$pm = PersistentManagerFactory::getManager();
		$result = $pm->findByQuery($q);
		
		$this->assert(count($result) > 0, 'Fallo Query sin select '. count($result));
	}
	
	function testSimpleGISQueryWithoutCondition() {
		$q = new GISQuery();
		$q->addProjection('p', 'ubicacion', 'ubicacion_de_p');
		$q->addFrom(Paciente::getClassName(), 'p');
		
		$pm = PersistentManagerFactory::getManager();
		$result = $pm->findByQuery($q);
		
		$this->assert(count($result) == 12, 'Existen mas pacientes que 12 y hay '. count($result));
	}
	
	function testSimpleGISQueryWithConditionOneRecord() {
		$q = new GISQuery();
		$q->addProjection('p', 'ubicacion', 'ubicacion_de_p');
		$q->setCondition(Condition::_AND()
			->add(Condition::EQ('p', 'nombre', 'Juan'))
			->add(GISCondition::EQGEO('p', 'ubicacion', new Point(-56.181948, -34.884621)))
			);
		$q->addFrom(Paciente::getClassName(), 'p');
		
		$pm = PersistentManagerFactory::getManager();
		$result = $pm->findByQuery($q);
		
		$this->assert(count($result) == 1 && $result[0]['ubicacion_de_p'] !== null, 'Deserializacion de punto en GISQuery caminando');
	}
	
	function testGISQuery() {
		$q = new GISQuery();
		$q->addFunction(GISFunction::DISTANCE_TO('p', 'ubicacion', new Point(-56.181548, -34.884121), 'distancia'));
		$q->addFrom(Paciente::getClassName(), 'p');
		  
		$pm = PersistentManagerFactory::getManager();
		$result = $pm->findByQuery($q);
		
		$this->assert(count($result) == 12 && $result[0]['distancia'] !== null, 'Distancia de de puntos caminando ' . $result[0]['distancia']);
	}
	
}

?>