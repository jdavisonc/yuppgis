<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');
YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('yuppgis.core.basic', 'Tag');

class MapTest extends YuppGISTestCase {
	
	public function testInitialize() {
		$map = new Map(array('name' => 'Prueba'));
		$map->setLayers(array());		
		$count = count($map->getLayers());		
		$this->assert($count == 0, 'Test inicializar mapa:'.$count);		
	}
	
	public function testAddElements() {
		$map = new Map(array('name' => 'Prueba'));
		
		$layer1 = new DataLayer();
		$layer2 = new DataLayer();
		$layer3 = new DataLayer();
				
		$map->addLayer($layer1);
		$map->addLayer($layer2);
		$map->addLayer($layer3);
		
		$count = count($map->getLayers());
		$this->assert($count == 3, 'Test agregar layer a mapa:'.$count);		
		
	}
	
	public function testRemoveElements() {		
		$map = new Map(array('name' => 'Prueba'));
		
		$layer1 = new DataLayer();
		$layer2 = new DataLayer();
		$layer3 = new DataLayer();
				
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