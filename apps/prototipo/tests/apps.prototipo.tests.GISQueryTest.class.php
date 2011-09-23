<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');

class GISQueryTest extends YuppGISTestCase {
	
	function testGISQueryWithDistance() {
		$q = new GISQuery();
		$q->addFunction(GISFunction::DISTANCE('p', 'ubicacion', 't', 'ubicacion'))
		  ->addFrom(YuppGISConventions::tableName(Paciente::getClassName()), 'p')
		  ->addFrom(YuppGISConventions::tableName(Paciente::getClassName()), 't');
		  
		$pm = PersistentManagerFactory::getManager();
		// TODO_GIS
		//$result = $pm->findByQuery($q);
		
		//$this->assert($result !== null, 'Test de filtrado de pacientes');
	}
	
	function testGISQuery() {
		$q = new GISQuery();
		$q->addFunction(GISFunction::DISTANCE_TO('p', 'ubicacion', new Point(10, 10)))
		  ->addFrom(YuppGISConventions::tableName(Paciente::getClassName()), 'p')
		  ->addFrom(YuppGISConventions::tableName(Paciente::getClassName()), 't');
		  
		$pm = PersistentManagerFactory::getManager();
		// TODO_GIS
		//$result = $pm->findByQuery($q);
		
		//$this->assert($result !== null, 'Test de filtrado de pacientes');
	}
	
}

?>