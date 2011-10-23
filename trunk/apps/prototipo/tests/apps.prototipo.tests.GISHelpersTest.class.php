<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');
YuppLoader::load('prototipo.model', 'Paciente');

class GISHelpersTest extends YuppGISTestCase {
	
	public function testGetActions() {
		$result = GISHelpers::AvailableActions("Paciente");
		$count = count($result);
		
		$this->assert($count == 2, 'Test acciones disponibles:'.$count);		
		
	}
	
	public function testGetFilters() {
		$result = GISHelpers::AvailableFilters("Paciente");
		$count = count($result);
		
		$this->assert($count == 1, 'Test filtros disponibles:'.$count);		
		
	}
}

?>