<?php

YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('casodeestudio.model', 'Paciente');
YuppLoader::load('casodeestudio.model', 'Medico');
YuppLoader::load('casodeestudio.model', 'Enfermedad');
YuppLoader::load('casodeestudio.model', 'Estado');

$map = new Map(array('name' => 'MapaDeEnfermedades'));

$diabetes = new DataLayer();
$diabetes->setName(Enfermedad::getName(Enfermedad::DIABETES));
$diabetes->setClassType('Paciente');
$diabetes->setAttributes(array('ubicacion'));
$diabetes->setDefaultUIProperty(new Icon(0, 0, '/images/aed-2.png'));

$hipertencion = new DataLayer();
$hipertencion->setName(Enfermedad::getName(Enfermedad::HIPERTENCION));
$hipertencion->setClassType('Paciente');
$hipertencion->setAttributes(array('ubicacion'));
$hipertencion->setDefaultUIProperty(new Icon(0, 0, '/images/aed-2.png'));

$obesidad = new DataLayer();
$obesidad->setName(Enfermedad::getName(Enfermedad::OBESIDAD));
$obesidad->setClassType('Paciente');
$obesidad->setAttributes(array('ubicacion'));
$obesidad->setDefaultUIProperty(new Icon(0, 0, '/images/aed-2.png'));

$asma = new DataLayer();
$asma->setName(Enfermedad::getName(Enfermedad::ASMA));
$asma->setClassType('Paciente');
$asma->setAttributes(array('ubicacion'));
$asma->setDefaultUIProperty(new Icon(0, 0, '/images/aed-2.png'));

$insuficiencia = new DataLayer();
$insuficiencia->setName(Enfermedad::getName(Enfermedad::INSUFICIENCIA_RENAL));
$insuficiencia->setClassType('Paciente');
$insuficiencia->setAttributes(array('ubicacion'));
$insuficiencia->setDefaultUIProperty(new Icon(0, 0, '/images/aed-2.png'));

$map->addLayer($diabetes);
$map->addLayer($hipertencion);
$map->addLayer($obesidad);
$map->addLayer($asma);
$map->addLayer($insuficiencia);

$map->save();


// Pacientes



?>