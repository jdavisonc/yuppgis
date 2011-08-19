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
		$id = uniqid();
		$name = 'Prueba';
		$layer = new DataLayer($id, $name,'nombre');
		$layer->setElements(array());		
		$count = count($layer->getElements());		
		$this->assert($count == 0, 'Test inicializar capa:'.$count);		
		
	}
	
	public function testAddElements()
	{
		$id = uniqid();$name = 'Prueba';
		$layer = new DataLayer($id, $name,'nombre');
		$p1 = new Paciente();
		$p1->setNombre('chocolate');		
		$p2 = new Paciente();
		$p2->setNombre('crema');
		$p3 = new Paciente();
		$p3->setNombre('limon');
				
		$layer->addElement($p1);
		$layer->addElement($p2);
		$layer->addElement($p3);
		
		$count = count($layer->getElements());		
		$this->assert($count == 3, 'Test agregar elemento a capa:'.$count);		
		
	}
	
	public function testRemoveElements()
	{
		$id = 1;
		$name = 'Prueba';
		$layer = new DataLayer($id, $name,'nombre');
		
		$p1 = new Paciente();
		$p1->setNombre('chocolate');
		$p1->setId('2');	
				
		$p2 = new Paciente();		
		$p2->setNombre('crema');
		$p2->setId('3');	
				
		$p3 = new Paciente();
		$p3->setNombre('limon');
		$p3->setId('4');		
				
		$layer->addElement($p1);
		$layer->addElement($p2);
		$layer->addElement($p3);
				
		$layer->removeElement($p2->getId());
		
		$count = count($layer->getElements());		
		$this->assert($count == 2, 'Test quitar elemento de capa:'.$count);		
		
		
	}
}

?>