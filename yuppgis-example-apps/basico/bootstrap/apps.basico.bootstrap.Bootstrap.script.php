<?php

YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('yuppgis.core.basic', 'Tag');
YuppLoader::load('basico.model', 'HHospital');

/*Borro toda la data preexistente*/

$params = new ArrayObject() ;

$maps = Map::listAll($params);
foreach ($maps as $map){
	//$layers = DataLayer::listAll($params);
	$layers =$map->getLayers();
	foreach ($layers as $layer){
		$map->removeLayer($layer);
		$map->save();
	}
	$map->delete();
}

$layers = DataLayer::listAll($params);
foreach ($layers as $layer){
	$elements = $layer->getElements();
	foreach ($elements as $element){
		$layer->removeElement($element);
	}
	$tags = $layer->getTags();
	foreach ($tags as $tag){
		$layer->removeTag($tag);
	}
	$layer->save();
	$layer->delete();
}

$tags = Tag::listAll($params);
foreach ($tags as $tag){
	$tag->delete();
}

$hospitales = HHospital::listAll($params);
foreach ($hospitales as $hospital){
	$hospital->delete();
}

$map = new Map(array('name' => 'MapaHospitales'));


$layer1 = new DataLayer();
$layer1->setName('Hospitales');
$layer1->setClassType('HHospital');

$h1 = new HHospital();
$h1->setNombre('HHospital Maciel');
$p = new Point(10, 10);
$p->setId(1); // Primer resultado
$h1->setUbicacion($p);
$h1->save();

$layer1->addElement($h1);
$layer1->save();

$map->addLayer($layer1);
$map->save();

?>