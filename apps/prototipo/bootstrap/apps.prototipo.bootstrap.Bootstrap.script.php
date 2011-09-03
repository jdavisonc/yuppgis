<?php

YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('prototipo.model', 'Paciente');

$map = new Map('MapaPrototipo');


$layer1 = new DataLayer('Sanos','','/yuppgis/yuppgis/js/gis/img/marker-green.png',true);
$layer2 = new DataLayer('Enfermos','','/yuppgis/yuppgis/js/gis/img/marker-blue.png',true);
$layer3 = new DataLayer('Viejos','','/yuppgis/yuppgis/js/gis/img/marker-gold.png',true);


$p1 = new Paciente();
$p1->setNombre('Juan');
$p1->setUbicacion(new Point(-56.181948, -34.884611));

$p2 = new Paciente();
$p2->setNombre('Jose');
$p2->setUbicacion(new Point( -56.181954, -34.884611));

$p3 = new Paciente();
$p3->setNombre('Maria');
$p3->setUbicacion(new Point(-56.181964, -34.884611));

$layer1->addElement($p1);
$layer1->addElement($p2);
$layer1->addElement($p3);

$layer1->save();


$p1 = new Paciente();
$p1->setNombre('Roberto');
$p1->setUbicacion(new Point(-56.181948, -34.883821));

$p2 = new Paciente();
$p2->setNombre('Emilia');
$p2->setUbicacion(new Point( -56.181954, -34.883831));

$p3 = new Paciente();
$p3->setNombre('Eliana');
$p3->setUbicacion(new Point(-56.181964, -34.883841));

$layer2->addElement($p1);
$layer2->addElement($p2);
$layer2->addElement($p3);

$layer2->save();

$p1 = new Paciente();
$p1->setNombre('Martin');
$p1->setUbicacion(new Point(-56.181548, -34.882621));

$p2 = new Paciente();
$p2->setNombre('Jorge');
$p2->setUbicacion(new Point( -56.181554, -34.882631));

$p3 = new Paciente();
$p3->setNombre('German');
$p3->setUbicacion(new Point(-56.181564, -34.882641));

$layer3->addElement($p1);
$layer3->addElement($p2);
$layer3->addElement($p3);

$layer3->save();


$map->addLayer($layer1);
$map->addLayer($layer2);
$map->addLayer($layer3);
		
$map->save();
?>