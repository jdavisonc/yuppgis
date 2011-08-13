<?php

YuppLoader::load('core.testing', 'TestCase');


class GISHelpersTest extends TestCase {

	public function run()
	{
		$this->test1();
	}
	
	public function test1()
	{
		$actions = GISHelpers::AvailableActions("apps.prototipo.model.tests.TestModel");
		
		$this->assert(array_count_values($actions) == 2, 'Test acciones disponibles');
		
		
	}
}

?>