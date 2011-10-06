<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');

class GISQueryTest extends YuppGISTestCase {
	
	function testSimpleGISQueryWithoutCondition() {
		$q = new GISQuery();
		$q->addProjection('p', 'ubicacion', 'ubicacion_de_p');
		$q->addFrom(Paciente::getClassName(), 'p');
		
		$pm = PersistentManagerFactory::getManager();
		$result = $pm->findByQuery($q);
		
		$this->assert(count($result) == 9, 'Existen mas pacientes que 9 y hay '. count($result));
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
		
		$this->assert($result[0]['ubicacion'] !== null, 'Fallo al deserializar un punto en GISQuery');
	}
	
	function testGISQuery() {
		$q = new GISQuery();
		
		$q->addFunction(GISFunction::DISTANCE_TO('p', 'ubicacion', new Point(10, 10)));
		$q->addFrom(Paciente::getClassName(), 'p');
		$q->addFrom(Paciente::getClassName(), 't');
		  
		$pm = PersistentManagerFactory::getManager();
		// TODO_GIS
		//$result = $pm->findByQuery($q);
		
		//$this->assert($result !== null, 'Test de filtrado de pacientes');
	}
	
}

?>