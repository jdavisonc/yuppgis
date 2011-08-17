<?php

YuppLoader::load('core.testing', 'TestCase');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');

class DataLayerTest extends TestCase {

	public function run()
	{
		$this->testInitialize();
		$this->testAddElements();
		$this->testRemoveElements();
	}
	
	public function testInitialize()
	{
		$id = uniqid();$name = 'Prueba';
		$array = new DataLayer($id, $name,'nombre');		
		$count = count($array->getElements());		
		$this->assert($count == 0, 'Test tamanio capa:'.$count);		
		
	}
	
	public function testAddElements()
	{
		$id = uniqid();$name = 'Prueba';
		$array = new DataLayer($id, $name,'nombre');
		$p1 = new Paciente();
		$p1->setProperties(array('nombre'=> 'chocolate' ));
				
		$p2 = new Paciente();
		$p2->setProperties(array('nombre'=> 'fresa' ));
		$array->addElement($p1);
		$array->addElement($p2);
		
		$count = count($array->getElements());		
		$this->assert($count == 2, 'Test tamanio capa:'.$count);		
		
	}
	
	public function testRemoveElements()
	{
		$id = uniqid();$name = 'Prueba';
		$array = new DataLayer($id, $name,'nombre');
		$p1 = new Paciente();
		$p1->setProperties(array('nombre'=> 'chocolate' ));
		$p2 = new Paciente();
		$p2->setProperties(array('nombre'=> 'crema' ));
		$p3 = new Paciente();
		$p3->setProperties(array('nombre'=> 'limon' ));
				
		
		$array->addElement($p1);
		$array->addElement($p2);
		$array->addElement($p3);
		
		$array->removeElement($p2->aGet('nombre'));
		
		$count = count($array->getElements());		
		$this->assert($count == 2, 'Test tamanio capa:'.$count);		
		
		
	}
}

?>