<?php

YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('yuppgis.core.basic', 'Tag');
YuppLoader::load('basico.model', 'Hospital');

$h1 = new Hospital();
$h1->setNombre('Hospital Maciel');
$p = new Point(10, 10);
$p->setId(1); // Primer resultado
$h1->setUbicacion($p);
$h1->save();

?>