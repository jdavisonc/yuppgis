<?php

YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('yuppgis.core.basic', 'Tag');
YuppLoader::load('prototipo.model', 'Paciente');
YuppLoader::load('prototipo.model', 'Medico');

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

$pacientes = Paciente::listAll($params);
foreach ($pacientes as $paciente){
	$paciente->delete();
}



/**/


$map = new Map('MapaPrototipo');


$layer1 = new DataLayer('Sanos','','/yuppgis/yuppgis/js/gis/img/marker-green.png',true);
$layer2 = new DataLayer('Enfermos','','/yuppgis/yuppgis/js/gis/img/marker-blue.png',true);
$layer3 = new DataLayer('Viejos','','/yuppgis/yuppgis/js/gis/img/marker-gold.png',true);


$p1 = new Paciente();
$p1->setNombre('Juan');
$p1->setUbicacion(new Point(-56.181948, -34.884621));
$p1->setLinea(new LineString(array ( new Point(-56.181948, -34.884621), new Point(-56.17438, -34.88619))));


$p2 = new Paciente();
$p2->setNombre('Jose');
$p2->setUbicacion(new Point( -56.181448, -34.883641));
$p2->setLinea(new LineString(array ( new Point(-56.181948, -34.884621), new Point(-56.17438, -34.88619))));

$p3 = new Paciente();
$p3->setNombre('Maria');
$p3->setUbicacion(new Point(-56.181764, -34.884255));
$p3->setLinea(new LineString(array ( new Point(-56.181948, -34.884621), new Point(-56.17438, -34.88619))));

$layer1->addElement($p1);
$layer1->addElement($p2);
$layer1->addElement($p3);

$t1 = new Tag();
$t1->setName('Tag 1');
$t1->setColor('Red');
$layer1->addTag($t1);

$layer1->save();


$p1 = new Paciente();
$p1->setNombre('Roberto');
$p1->setUbicacion(new Point(-56.181948, -34.883821));
$p1->setLinea(new LineString(array ( new Point(-56.181948, -34.883821), new Point(-56.17438, -34.88619))));

$p2 = new Paciente();
$p2->setNombre('Emilia');
$p2->setUbicacion(new Point( 585055, 6242602));
//$p2->setLinea(new LineString(array ( new Point(585055, 6242602), new Point(-56.17438, -34.88619))));

$p3 = new Paciente();
$p3->setNombre('Eliana');
$p3->setUbicacion(new Point(-56.181264, -34.883341));
$p3->setLinea(new LineString(array ( new Point(-56.181264, -34.883341), new Point(-56.17438, -34.88619))));

$layer2->addElement($p1);
$layer2->addElement($p2);
$layer2->addElement($p3);

$t2 = new Tag();
$t2->setName('Tag 2');
$t2->setColor('Green');
$layer2->addTag($t2);

$t4 = new Tag();
$t4->setName('Tag 3');
$t4->setColor('Orange');
$layer2->addTag($t4);


$layer2->save();

$p1 = new Paciente();
$p1->setNombre('Martin');
$p1->setUbicacion(new Point(-56.181548, -34.882521));
$p1->setLinea(new LineString(array ( new Point(-56.181548, -34.882521), new Point(-56.17438, -34.88619))));

$p2 = new Paciente();
$p2->setNombre('Jorge');
$p2->setUbicacion(new Point( -56.181354, -34.882631));
$lineString = new LineString(array ( new Point(-56.17438, -34.88549), new Point(-56.18159, -34.88566)));
$p2->setLinea($lineString);

/*Seteo Border a LineString */
$borde = new Border();
$borde->setColor(Color::WHITE);
$borde->setWidth(4);
$lineString->setUIProperty($borde);
$p2->save();

$p3 = new Paciente();
$p3->setNombre('German');
$p3->setUbicacion(new Point(-56.181164, -34.882741));
$p3->setLinea(new LineString(array ( new Point(-56.181164, -34.882741), new Point(-56.17438, -34.88619))));

$layer3->addElement($p1);
$layer3->addElement($p2);
$layer3->addElement($p3);

$t3 = new Tag();
$t3->setName('Tag 4');
$t3->setColor('Yellow');
$layer3->addTag($t3);

$layer2->addTag($t3);
$layer2->save();

$layer3->save();

$medico = new Medico();
$medico->setNombre('Medico Test');

$puntos = array ( new Point(-56.17438, -34.88619), new Point(-56.181548, -34.882521), new Point(-56.181948, -34.880621), new Point(-56.181948, -34.883821), new Point(-56.17438, -34.88619));
$line = new LineString($puntos);
$polygon = new Polygon($line);

/*Seteo Background al MultiPolygon */
$relleno = new Background();
$relleno->setColor(Color::RED);
$multiPolygon = new MultiPolygon(array ($polygon));
$multiPolygon->setUIProperty($relleno);

$medico->setZonas($multiPolygon);
$medico->save();

$layer1->addElement($medico);
$layer1->save();

$map->addLayer($layer1);
$map->addLayer($layer2);
$map->addLayer($layer3);

$map->save();
?>