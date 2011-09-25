<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');

class GISQueryTest extends YuppGISTestCase {
	
	function testGISQueryWithDistance() {
		$q = new GISQuery();
		$q->addProjection('p', 'ubicacion', 'ubicacion_de_p');
		$q->setCondition(Condition::_AND()
			->add(Condition::EQ('p', 'nombre', 'pepito'))
			->add(GISCondition::EQGEOA('p', 'ubicacion', 't', 'ubicacion'))
			);
		$q->addFrom(Paciente::getClassName(), 'p');
		$q->addFrom(Paciente::getClassName(), 't');
		  
		$pm = PersistentManagerFactory::getManager();
		$result = $pm->findByQuery($q);
		
		$this->assert($result !== null, 'Test de filtrado de pacientes');
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