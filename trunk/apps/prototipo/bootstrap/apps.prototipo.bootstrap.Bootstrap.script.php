<?php

YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('yuppgis.core.basic', 'Tag');
YuppLoader::load('prototipo.model', 'Paciente');
YuppLoader::load('prototipo.model', 'Medico');

/*Borro toda la data preexistente*/

$file = 'events.txt';		
file_put_contents($file, "");
		
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

$medicos = Medico::listAll($params);
foreach ($medicos as $medico){
	$medico->delete();
}


/**/


$map = new Map(array('name' => 'MapaPrototipo'));


$layer1 = new DataLayer();
$layer1->setName('Sanos');
$layer1->setClassType('Paciente');
$layer1->setAttributes(array('ubicacion'));
$layer1->setIconUrl('/yuppgis/yuppgis/js/gis/img/marker-green.png');

$layer2 = new DataLayer();
$layer2->setName('Enfermos');
$layer2->setClassType('Paciente');
$layer2->setAttributes(array('ubicacion','linea'));
$layer2->setIconUrl('/yuppgis/yuppgis/js/gis/img/marker-blue.png');

$layer3 = new DataLayer();
$layer3->setName('Viejos');
$layer3->setClassType('Paciente');
$layer3->setAttributes(array('ubicacion'));

$layer4 = new DataLayer();
$layer4->setName('Medicos');
$layer4->setClassType('Medico');
$layer4->setAttributes(array('zonas'));


$p1 = new Paciente();
$p1->setNombreWithNotify('Juan');
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
$borde->setColor(Color::BLUE);
$borde->setWidth(4);
$lineString->setUIProperty($borde);
$p2->save();

$p3 = new Paciente();
$p3->setNombreWithNotify('GermanS');
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

$m1 = new Medico();
$m1->setNombre('Medico 1');

$puntos = array ( new Point(-56.17438, -34.88619), new Point(-56.181548, -34.882521), new Point(-56.181948, -34.880621), new Point(-56.181948, -34.883821), new Point(-56.17438, -34.88619));
$line = new LineString($puntos);
$polygon = new Polygon($line);

/*Seteo Background al MultiPolygon */
$relleno = new Background();
$relleno->setColor(Color::RED);
$multiPolygon = new MultiPolygon(array ($polygon));
$multiPolygon->setUIProperty($relleno);

$m1->setZonas($multiPolygon);
$m1->save();

$layer4->addElement($m1);

$m2 = new Medico();
$m2->setNombre('Medico 2');
$m2->setUbicacion(new Point(-56.17438, -34.88619));
$m2->save();
$layer1->registerObserver($m2);
$p3->registerObserver($m2);
$p3->save();

$p3->setNombreWithNotify('German');
$p3->save();

$layer4->addElement($m2);
$layer4->save();






$map->addLayer($layer1);
$map->addLayer($layer2);
$map->addLayer($layer3);
$map->addLayer($layer4);

$map->save();
?>