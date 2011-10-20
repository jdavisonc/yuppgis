<?php

YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('yuppgis.core.basic', 'Tag');
YuppLoader::load('prototipo2DB.model', 'Persona');

/*Borro toda la data preexistente*/

$params = new ArrayObject() ;



$personas = Persona::listAll($params);
foreach ($personas as $persona){
	$persona->delete();
}


/**/
$p = new Persona();
$p->setNombre("Persona 1");
$p->setPosicion(new Point(25,33));
$p->save();

?>