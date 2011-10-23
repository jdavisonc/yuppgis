<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');


class DataLayerTest extends YuppGISTestCase {
	
	public function testAddElements() {
		$layer = new DataLayer();
		$layer->setName('Prueba');
		$layer->setClassType('Paciente');
		
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
	
	public function testRemoveElements() {		
		$layer = new DataLayer();
		$layer->setName('Prueba');
		$layer->setClassType('Paciente');
		
		$p1 = new Paciente();
		$p1->setNombre('chocolate');
		$p1->setUbicacion(new Point(10, 10));
						
		$p2 = new Paciente();		
		$p2->setNombre('crema');
		$p2->setUbicacion(new Point( 10, 10));	
				
		$p3 = new Paciente();
		$p3->setNombre('limon');
		$p3->setUbicacion(new Point(10, 10));				
		
		$layer->addElement($p1);
		$layer->addElement($p2);
		$layer->addElement($p3);
		
		$layer->save();
				
		$layer->removeElement($p2);
				
		$count = count($layer->getElements());		
		$this->assert($count == 2, 'Test quitar elemento de capa:'.$count);
		
		// Borrando
		$layer->removeElement($p1);
		$layer->removeElement($p3);
		
		$p1->delete();
		$p2->delete();
		$p3->delete();
		
		$layer->delete();
	}

	public  function testAddTag(){
		$layer = new DataLayer();
		$layer->setName('AddTag');
		$layer->setClassType('Paciente');
		
		$firstTag = new Tag();
		$firstTag->setName('First Tag');
		$firstTag->setColor('Red');
		$layer->addTag($firstTag);
		
		$secTag = new Tag();
		$secTag->setName('Second Tag');
		$secTag->setColor('Green');
		$layer->addTag($secTag);
		
		$layer->save();
		
		$count = count($layer->getTags());		
		$this->assert($count == 2, 'Test agregar tag a capa:'.$count);
		
		// Borrando
		$layer->removeTag($firstTag);
		$layer->removeTag($secTag);
		
		$firstTag->delete();
		$secTag->delete();
		
		$layer->delete();
	}
	
	public function testRemoveTag(){
		$layer = new DataLayer();
		$layer->setName('RemoveTag');
		$layer->setClassType('Paciente');
		
		$firstTag = new Tag();
		$firstTag->setName('First Tag');
		$firstTag->setColor('Red');
		$layer->addTag($firstTag);
		
		$secTag = new Tag();
		$secTag->setName('Second Tag');
		$secTag->setColor('Green');
		$layer->addTag($secTag);
		
		$thirdTag = new Tag();
		$thirdTag->setName('Third Tag');
		$thirdTag->setColor('Yellow');
		$layer->addTag($thirdTag);
		
		$layer->save();
				
		$layer->removeTag($secTag);
		
		$count = count($layer->getTags());		
		$this->assert($count == 2, 'Test quitar tag a capa:'.$count);
		
		// Borrando
		$layer->removeTag($firstTag);
		$layer->removeTag($thirdTag);
		
		$firstTag->delete();
		$secTag->delete();
		$thirdTag->delete();
		
		$layer->delete();
	}
}

?>