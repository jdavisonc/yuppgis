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


// 

$p5 = new Paciente();

$p5->setNombre("Claudio");
$p5->setApellido("Espinosa");
$p5->setSexo("M");
//$p4->setFechaNacimiento();
$p5->setTelefono("2 555 555");
$p5->setEmail("cEspinosa@mail.com");

$p5->setCi("1.333.444-5");

$p5->setDireccion("");
$p5->setBarrio("Buceo");
$p5->setCiudad("Montevideo");
$p5->setDepartamento("Montevideo");
$p5->setUbicacion(new Point(-6249386.3316121,-4148619.7088005));

$p6 = new Paciente();

$p6->setNombre("Ana Laura");
$p6->setApellido("Gonzales");
$p6->setSexo("F");
//$p4->setFechaNacimiento();
$p6->setTelefono("2 666 666");
$p6->setEmail("alGonzales@mail.com");

$p6->setCi("1.444.555-5");

$p6->setDireccion("");
$p6->setBarrio("Buceo");
$p6->setCiudad("Montevideo");
$p6->setDepartamento("Montevideo");
$p6->setUbicacion(new Point(-6250829.0805207,-4148610.154172));


$p7 = new Paciente();

$p7->setNombre("Mercedes");
$p7->setApellido("Viera");
$p7->setSexo("F");
//$p4->setFechaNacimiento();
$p7->setTelefono("2 777 7777");
$p7->setEmail("mVieras@mail.com");

$p7->setCi("1.555.666-7");

$p7->setDireccion("");
$p7->setBarrio("Goes");
$p7->setCiudad("Montevideo");
$p7->setDepartamento("Montevideo");
$p7->setUbicacion(new Point(-6254096.7626795,-4147807.566175));


$p8 = new Paciente();

$p8->setNombre("Martin");
$p8->setApellido("Mendez");
$p8->setSexo("M");
//$p4->setFechaNacimiento();
$p8->setTelefono("2 888 888");
$p8->setEmail("mMendez@mail.com");

$p8->setCi("1.666.777-8");

$p8->setDireccion("");
$p8->setBarrio("Reducto");
$p8->setCiudad("Montevideo");
$p8->setDepartamento("Montevideo");
$p8->setUbicacion(new Point(-6255281.5366177,-4147520.927319));


$p9 = new Paciente();

$p9->setNombre("Jorge");
$p9->setApellido("Hernandez");
$p9->setSexo("M");
//$p4->setFechaNacimiento();
$p9->setTelefono("2 999 999");
$p9->setEmail("jHernandez@mail.com");

$p9->setCi("1.777.888-9");

$p9->setDireccion("");
$p9->setBarrio("Barrio Sur");
$p9->setCiudad("Montevideo");
$p9->setDepartamento("Montevideo");
$p9->setUbicacion(new Point(-6254779.9186197,-4151763.1823882));


$p10 = new Paciente();

$p10->setNombre("Emilia");
$p10->setApellido("Damian");
$p10->setSexo("F");
//$p4->setFechaNacimiento();
$p10->setTelefono("3 000 0000");
$p10->setEmail("eDamian@mail.com");

$p10->setCi("1.888.999-9");

$p10->setDireccion("");
$p10->setBarrio("Barrio Sur");
$p10->setCiudad("Montevideo");
$p10->setDepartamento("Montevideo");
$p10->setUbicacion(new Point(-6255544.2889021,-4151710.6319314));


$p11 = new Paciente();

$p11->setNombre("Ruben");
$p11->setApellido("Medina");
$p11->setSexo("M");
//$p4->setFechaNacimiento();
$p11->setTelefono("3 000 111");
$p11->setEmail("rMedina@mail.com");

$p11->setCi("2.000.111-1");

$p11->setDireccion("");
$p11->setBarrio("Ciudad Vieja");
$p11->setCiudad("Montevideo");
$p11->setDepartamento("Montevideo");
$p11->setUbicacion(new Point(-6256858.0503259,-4151705.8546171));

$p12 = new Paciente();

$p12->setNombre("Americo");
$p12->setApellido("Rochon");
$p12->setSexo("M");
//$p4->setFechaNacimiento();
$p12->setTelefono("3 111 222");
$p12->setEmail("aRochon@mail.com");

$p12->setCi("2.111.222-1");

$p12->setDireccion("");
$p12->setBarrio("Ciudad Vieja");
$p12->setCiudad("Montevideo");
$p12->setDepartamento("Montevideo");
$p12->setUbicacion(new Point(-6257259.3447243,-4151705.8546171));


$p13 = new Paciente();

$p13->setNombre("Romina");
$p13->setApellido("Viudez");
$p13->setSexo("F");
//$p4->setFechaNacimiento();
$p13->setTelefono("3 222 333");
$p13->setEmail("rViudez@mail.com");

$p13->setCi("2.222.333-1");

$p13->setDireccion("");
$p13->setBarrio("Ciudad Vieja");
$p13->setCiudad("Montevideo");
$p13->setDepartamento("Montevideo");
$p13->setUbicacion(new Point(-6257125.5799248,-4151705.8546171));


$p14 = new Paciente();

$p14->setNombre("Paula");
$p14->setApellido("Blanco");
$p14->setSexo("F");
//$p4->setFechaNacimiento();
$p14->setTelefono("3 333 444");
$p14->setEmail("pBlanco@mail.com");

$p14->setCi("2.333.444-1");

$p14->setDireccion("");
$p14->setBarrio("Centro");
$p14->setCiudad("Montevideo");
$p14->setDepartamento("Montevideo");
$p14->setUbicacion(new Point(-6255778.3773015,-4150855.4926776));


$p15 = new Paciente();

$p15->setNombre("Diego");
$p15->setApellido("Kesman");
$p15->setSexo("M");
//$p4->setFechaNacimiento();
$p15->setTelefono("3 444 555");
$p15->setEmail("dKesman@mail.com");

$p15->setCi("2.444.555-1");

$p15->setDireccion("");
$p15->setBarrio("Centro");
$p15->setCiudad("Montevideo");
$p15->setDepartamento("Montevideo");
$p15->setUbicacion(new Point(-6255549.0662167,-4151056.1398768));


$p16 = new Paciente();

$p16->setNombre("Claudia");
$p16->setApellido("Alonso");
$p16->setSexo("F");
//$p4->setFechaNacimiento();
$p16->setTelefono("3 555 666");
$p16->setEmail("cAlonso@mail.com");

$p16->setCi("2.555.666-1");

$p16->setDireccion("");
$p16->setBarrio("Centro");
$p16->setCiudad("Montevideo");
$p16->setDepartamento("Montevideo");
$p16->setUbicacion(new Point(-6257068.2521536,-4150912.8204487));



$p16 = new Paciente();

$p16->setNombre("Marcelo");
$p16->setApellido("Perez");
$p16->setSexo("M");
//$p4->setFechaNacimiento();
$p16->setTelefono("3 666 777");
$p16->setEmail("mPerez@mail.com");

$p16->setCi("2.666.777-1");

$p16->setDireccion("");
$p16->setBarrio("Punta Carretas");
$p16->setCiudad("Montevideo");
$p16->setDepartamento("Montevideo");
$p16->setUbicacion(new Point(-6251507.4583464,-4153855.6460373));

$p17 = new Paciente();

$p17->setNombre("Javier");
$p17->setApellido("Rios");
$p17->setSexo("M");
//$p4->setFechaNacimiento();
$p17->setTelefono("3 777 888");
$p17->setEmail("jRios@mail.com");

$p17->setCi("2.777.888-1");

$p17->setDireccion("");
$p17->setBarrio("Pocitos");
$p17->setCiudad("Montevideo");
$p17->setDepartamento("Montevideo");
$p17->setUbicacion(new Point(-6250829.0797205,-4153301.4775823));


$p18 = new Paciente();

$p18->setNombre("Marcela");
$p18->setApellido("Muñoz");
$p18->setSexo("F");
//$p4->setFechaNacimiento();
$p18->setTelefono("3 888 999");
$p18->setEmail("mMuñoz@mail.com");

$p18->setCi("2.888.999-3");

$p18->setDireccion("");
$p18->setBarrio("Pocitos");
$p18->setCiudad("Montevideo");
$p18->setDepartamento("Montevideo");
$p18->setUbicacion(new Point(-6251364.1389184,-4152422.4517571));


$p19 = new Paciente();

$p19->setNombre("Carlos");
$p19->setApellido("Antunez");
$p19->setSexo("M");
//$p4->setFechaNacimiento();
$p19->setTelefono("3 888 999");
$p19->setEmail("cAntunez@mail.com");

$p19->setCi("2.888.999-3");

$p19->setDireccion("");
$p19->setBarrio("Pocitos");
$p19->setCiudad("Montevideo");
$p19->setDepartamento("Montevideo");
$p19->setUbicacion(new Point(-6251106.163948,-4152737.7544988));


$p20 = new Paciente();

$p20->setNombre("Juan Carlos");
$p20->setApellido("Valdez");
$p20->setSexo("M");
//$p4->setFechaNacimiento();
$p20->setTelefono("3 932 888");
$p20->setEmail("jcValdez@mail.com");

$p20->setCi("2.785.838-6");

$p20->setDireccion("");
$p20->setBarrio("Pocitos");
$p20->setCiudad("Montevideo");
$p20->setDepartamento("Montevideo");
$p20->setUbicacion(new Point(-6251545.6768606,-4153234.5951825));

// Asociasiones
/*
$p3->addToProcedimientos();
$p3->addToMedicaciones();
$p3->addToEstudios();*/


$capasEnfermedades[0]->addElement($p1);
$capasEnfermedades[4]->addElement($p2);
$capasEnfermedades[1]->addElement($p3);
$capasEnfermedades[2]->addElement($p4);

$capasEnfermedades[0]->addElement($p5);
$capasEnfermedades[4]->addElement($p6);
$capasEnfermedades[1]->addElement($p7);
$capasEnfermedades[2]->addElement($p8);

$capasEnfermedades[0]->addElement($p9);
$capasEnfermedades[4]->addElement($p10);
$capasEnfermedades[1]->addElement($p11);
$capasEnfermedades[2]->addElement($p12);

$capasEnfermedades[0]->addElement($p13);
$capasEnfermedades[4]->addElement($p14);
$capasEnfermedades[1]->addElement($p15);
$capasEnfermedades[2]->addElement($p16);

$capasEnfermedades[0]->addElement($p17);
$capasEnfermedades[4]->addElement($p18);
$capasEnfermedades[1]->addElement($p19);
$capasEnfermedades[2]->addElement($p20);

$p1->aSet(Enfermedad::ASMA, Estado::CONTROLADO);
$p2->aSet(Enfermedad::OBESIDAD, Estado::ADVERTENCIA);
$p3->aSet(Enfermedad::DIABETES, Estado::NO_CONTROLADO);
$p4->aSet(Enfermedad::HIPERTENSION, Estado::NO_CONTROLADO);

$p5->aSet(Enfermedad::ASMA, Estado::ADVERTENCIA);
$p6->aSet(Enfermedad::OBESIDAD, Estado::NO_CONTROLADO);
$p7->aSet(Enfermedad::DIABETES, Estado::CONTROLADO);
$p8->aSet(Enfermedad::HIPERTENSION, Estado::CONTROLADO);

$p9->aSet(Enfermedad::ASMA, Estado::NO_CONTROLADO);
$p10->aSet(Enfermedad::OBESIDAD, Estado::ADVERTENCIA);
$p11->aSet(Enfermedad::DIABETES, Estado::ADVERTENCIA);
$p12->aSet(Enfermedad::HIPERTENSION, Estado::ADVERTENCIA);

$p13->aSet(Enfermedad::ASMA, Estado::NO_CONTROLADO);
$p14->aSet(Enfermedad::OBESIDAD, Estado::CONTROLADO);
$p15->aSet(Enfermedad::DIABETES, Estado::CONTROLADO);
$p16->aSet(Enfermedad::HIPERTENSION, Estado::NO_CONTROLADO);

$p17->aSet(Enfermedad::ASMA, Estado::NO_CONTROLADO);
$p18->aSet(Enfermedad::OBESIDAD, Estado::NO_CONTROLADO);
$p19->aSet(Enfermedad::DIABETES, Estado::ADVERTENCIA);
$p20->aSet(Enfermedad::HIPERTENSION, Estado::CONTROLADO);

/*** Medicos ***/




$m1 = new Medico();
$m1->setNombre('Roberto');
$m1->setApellido('Sanchez');

//$puntos = array ( new Point(-6250169.8103519,-4149231.2058267), new Point(-6249825.8437246,-4150654.8454783), new Point(-6255128.6625612,-4150177.1140516), new Point(-6254001.2163941,-4148810.8021711), new Point(-6250169.8103519,-4149231.2058267));
$puntos = array ( new Point(-6257746.6307796,-4151209.0139333), new Point(-6255319.7551319,-4150215.3325657), new Point(-6253389.7201679,-4152164.4767867), new Point(-6257612.8659801,-4152011.6027301), new Point(-6257746.6307796,-4151209.0139333));
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


//$puntos = array ( new Point(-6254326.0737643,-4148419.0624012), new Point(-6251230.3741192,-4148763.0290285), new Point(-6249797.179839,-4146278.8256095), new Point(-6254230.527479,-4145743.7664116), new Point(-6254326.0737643,-4148419.0624012));
$puntos = array ( new Point(-6255587.2847308,-4147425.3810337), new Point(-6254154.0904507,-4146575.0190941), new Point(-6252568.022114,-4148380.8438871), new Point(-6254536.2755921,-4149116.5502842), new Point(-6255587.2847308,-4147425.3810337));
$line = new LineString($puntos);
$polygon = new Polygon($line);

/*Seteo Background al Polygon */
$relleno = new Background();
$relleno->setColor(Color::BLUE);
$polygon->setUIProperty($relleno);

$m2->setZona($polygon);
$m2->save();


$m3 = new Medico();
$m3->setNombre('Natalia');
$m3->setApellido('Gomez');

//$puntos = array ( new Point(-6254326.0737643,-4148419.0624012), new Point(-6251230.3741192,-4148763.0290285), new Point(-6249797.179839,-4146278.8256095), new Point(-6254230.527479,-4145743.7664116), new Point(-6254326.0737643,-4148419.0624012));
$puntos = array ( new Point(-6252290.9378865,-4148170.6420593), new Point(-6249453.2132118,-4147224.7338344), new Point(-6248402.204073,-4149059.222513), new Point(-6252004.2990305,-4149622.9455966), new Point(-6252290.9378865,-4148170.6420593));
$line = new LineString($puntos);
$polygon = new Polygon($line);

/*Seteo Background al Polygon */
$relleno = new Background();
$relleno->setColor(Color::RED);
$polygon->setUIProperty($relleno);

$m3->setZona($polygon);
$m3->save();


$m4 = new Medico();
$m4->setNombre('Luis');
$m4->setApellido('Martinez');

//$puntos = array ( new Point(-6254326.0737643,-4148419.0624012), new Point(-6251230.3741192,-4148763.0290285), new Point(-6249797.179839,-4146278.8256095), new Point(-6254230.527479,-4145743.7664116), new Point(-6254326.0737643,-4148419.0624012));
$puntos = array ( new Point(-6251975.6351449,-4152312.573528), new Point(-6251020.1722914,-4152193.1406723), new Point(-6250551.9954932,-4152460.6702712), new Point(-6250781.3065781,-4153420.9104389), new Point(-6251770.2106314,-4154185.2807217), new Point(-6251975.6351449,-4152312.573528));
$line = new LineString($puntos);
$polygon = new Polygon($line);

/*Seteo Background al Polygon */
$relleno = new Background();
$relleno->setColor(Color::BLUE);
$polygon->setUIProperty($relleno);

$m4->setZona($polygon);
$m4->save();



// Se guardan

$map->save();
$map2->save();

?>