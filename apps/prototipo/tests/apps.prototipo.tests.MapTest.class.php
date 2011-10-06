<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');
YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('yuppgis.core.basic', 'Tag');

class MapTest extends YuppGISTestCase {
	
	public function testInitialize() {
		$name = 'Prueba';
		$map = new Map($name);
		$map->setLayers(array());		
		$count = count($map->getLayers());		
		$this->assert($count == 0, 'Test inicializar mapa:'.$count);		
	}
	
	public function testAddElements() {
		$name = 'Prueba';
		$map = new Map($name);
		
		
		$layer1 = new DataLayer('nombre1');
		$layer2 = new DataLayer('nombre2');
		$layer3 = new DataLayer('nombre3');
				
		$map->addLayer($layer1);
		$map->addLayer($layer2);
		$map->addLayer($layer3);
		
		$count = count($map->getLayers());
		$this->assert($count == 3, 'Test agregar layer a mapa:'.$count);		
		
	}
	
	public function testRemoveElements() {		
		$name = 'Prueba';
		$map = new Map($name);
		
		
		$layer1 = new DataLayer('nombre1');
		$layer2 = new DataLayer('nombre2');
		$layer3 = new DataLayer('nombre3');
				
		$map->addLayer($layer1);
		$map->addLayer($layer2);
		$map->addLayer($layer3);
		
		$map->save();
				
		$map->removeLayer($layer2);
				
		$count = count($map->getLayers());		
		$this->assert($count == 2, 'Test quitar layer de mapa:'.$count);
		
		// Borrando
		$map->removeLayer($layer1);
		$map->removeLayer($layer3);
		
		$layer1->delete();
		$layer2->delete();
		$layer3->delete();
		
		$map->delete();
	}
}

?>