<?php

YuppLoader::load('core.testing', 'TestCase');
YuppLoader::load('prototipo.model', 'Paciente');

class GISHelpersTest extends TestCase {

	public function run()
	{
		$this->testGetActions();
		$this->testGetFilters();
	}
	
	public function testGetActions()
	{
		$result = GISHelpers::AvailableActions("Paciente");
		$count = count($result);
		
		$this->assert($count == 2, 'Test acciones disponibles:'.$count);		
		
	}
	
public function testGetFilters()
	{
		$result = GISHelpers::AvailableFilters("Paciente");
		$count = count($result);
		
		$this->assert($count == 2, 'Test filtros disponibles:'.$count);		
		
	}
}

?>