<?php

YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('casodeestudio.model', 'Paciente');
YuppLoader::load('casodeestudio.model', 'Medico');
YuppLoader::load('casodeestudio.model', 'Enfermedad');
YuppLoader::load('casodeestudio.model', 'Estado');

/**** borrado ****/
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

/****  creacion ****/

$map = new Map(array('name' => 'MapaDeEnfermedades'));
$map2 = new Map(array('name' => 'MapaDeMedicos'));
$capasEnfermedades = array();
foreach (Enfermedad::getEnfermedades() as $enfermedad) {
	$dlenf = new DataLayer();
	$dlenf->setName(Enfermedad::getName($enfermedad));
	$dlenf->setClassType('Paciente');
	$dlenf->setAttributes(array('ubicacion'));
	$dlenf->setDefaultUIProperty(new Icon(0, 0, '/images/'.$enfermedad.'.png'));
	$dlenf->save();
	$map->addLayer($dlenf);
	$map2->addLayer($dlenf);
	$capasEnfermedades[] = $dlenf;
}

/****  Pacientes ***/


$p1 = new Paciente();

$p1->setNombre("Juan");
$p1->setApellido("Perez");
$p1->setSexo("M");
//$p1->setFechaNacimiento();
$p1->setTelefono("2 203 05 78");
$p1->setEmail("jperez@gmail.com");

$p1->setCi("3.526.125-7");

$p1->setDireccion("");
$p1->setBarrio("Villa Muñoz");
$p1->setCiudad("Montevideo");
$p1->setDepartamento("Montevideo");
$p1->setUbicacion(new Point(-6253638.1405101,-4148156.3101165));

// Asociasiones
/*
$p1->addToProcedimientos();
$p1->addToMedicaciones();
$p1->addToEstudios();*/


$p2 = new Paciente();

$p2->setNombre("Jose");
$p2->setApellido("Rodriguez");
$p2->setSexo("M");
//$p2->setFechaNacimiento();
$p2->setTelefono("2 208 35 49");
$p2->setEmail("jrodriguez@gmail.com");

$p2->setCi("4.123.325-8");

$p2->setDireccion("");
$p2->setBarrio("Villa Muñoz");
$p2->setCiudad("Montevideo");
$p2->setDepartamento("Montevideo");
$p2->setUbicacion(new Point(-6251856.2022884,-4148299.6295445));

// Asociasiones
/*
$p2->addToProcedimientos();
$p2->addToMedicaciones();
$p2->addToEstudios();*/


$p3 = new Paciente();

$p3->setNombre("Maria");
$p3->setApellido("Lopez");
$p3->setSexo("F");
//$p3->setFechaNacimiento();
$p3->setTelefono("2 258 45 55");
$p3->setEmail("mlopez@gmail.com");

$p3->setCi("4.555.625-7");

$p3->setDireccion("");
$p3->setBarrio("Villa Muñoz");
$p3->setCiudad("Montevideo");
$p3->setDepartamento("Montevideo");
$p3->setUbicacion(new Point(-6252759.1146849,-4149331.5294262));

// Asociasiones
/*
$p3->addToProcedimientos();
$p3->addToMedicaciones();
$p3->addToEstudios();*/


$p4 = new Paciente();

$p4->setNombre("Pedro");
$p4->setApellido("Lopez");
$p4->setSexo("M");
//$p4->setFechaNacimiento();
$p4->setTelefono("2 258 45 55");
$p4->setEmail("plopez@gmail.com");

$p4->setCi("1.256.345-7");

$p4->setDireccion("");
$p4->setBarrio("Villa Muñoz");
$p4->setCiudad("Montevideo");
$p4->setDepartamento("Montevideo");
$p4->setUbicacion(new Point(-6252759.1146849,-4149331.5294262));
// Asociasiones
/*
$p3->addToProcedimientos();
$p3->addToMedicaciones();
$p3->addToEstudios();*/


$capasEnfermedades[0]->addElement($p1);
$capasEnfermedades[4]->addElement($p2);
$capasEnfermedades[1]->addElement($p3);
$capasEnfermedades[2]->addElement($p4);

$p1->aSet(Enfermedad::ASMA, Estado::CONTROLADO);
$p2->aSet(Enfermedad::OBESIDAD, Estado::ADVERTENCIA);
$p3->aSet(Enfermedad::DIABETES, Estado::NO_CONTROLADO);
$p4->aSet(Enfermedad::HIPERTENSION, Estado::NO_CONTROLADO);

/*** Medicos ***/






$m1 = new Medico();
$m1->setNombre('Roberto');
$m1->setApellido('Sanchez');

$puntos = array ( new Point(-6250169.8103519,-4149231.2058267), new Point(-6249825.8437246,-4150654.8454783), new Point(-6255128.6625612,-4150177.1140516), new Point(-6254001.2163941,-4148810.8021711), new Point(-6250169.8103519,-4149231.2058267));
$line = new LineString($puntos);
$polygon = new Polygon($line);

/*Seteo Background al Polygon */
$relleno = new Background();
$relleno->setColor(Color::RED);
$polygon->setUIProperty($relleno);

$m1->setZona($polygon);
$m1->save();


$m2 = new Medico();
$m2->setNombre('Mario');
$m2->setApellido('Pereira');


$puntos = array ( new Point(-6254326.0737643,-4148419.0624012), new Point(-6251230.3741192,-4148763.0290285), new Point(-6249797.179839,-4146278.8256095), new Point(-6254230.527479,-4145743.7664116), new Point(-6254326.0737643,-4148419.0624012));
$line = new LineString($puntos);
$polygon = new Polygon($line);

/*Seteo Background al Polygon */
$relleno = new Background();
$relleno->setColor(Color::BLUE);
$polygon->setUIProperty($relleno);

$m2->setZona($polygon);
$m2->save();

// Se guardan

$map->save();
$map2->save();

?>