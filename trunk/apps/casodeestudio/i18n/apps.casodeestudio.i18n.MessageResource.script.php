<?php

YuppLoader::load('core.support', 'I18nMessage');

$m = I18nMessage::getInstance(); 

//definidios por el programador
$m->a("label.filtroGrado.todos","es", "Todos");
$m->a("label.filtroGrado.advertencia","es", "Advertencia");
$m->a("label.filtroGrado.no_controlado","es", "No Controlado");
$m->a("label.filtroGrado.controlado","es", "Controlado");


//Son usados por los Helpers, ver YuppGISConventions

// Nombre de los attr de la clase paciente
$m->a("casodeestudio.Paciente.nombre","es", "Nombre");
$m->a("casodeestudio.Paciente.apellido","es", "Apellido");
$m->a("casodeestudio.Paciente.sexo","es", "Sexo");
$m->a("casodeestudio.Paciente.fechaNacimiento","es", "Fech. Nacimiento");
$m->a("casodeestudio.Paciente.fechaFallecimiento","es", "Fech. Fallecimiento");
$m->a("casodeestudio.Paciente.telefono","es", "Telefono");
$m->a("casodeestudio.Paciente.email","es", "Email");
$m->a("casodeestudio.Paciente.ci","es", "C.I.");
$m->a("casodeestudio.Paciente.direccion","es", "Direccion");
$m->a("casodeestudio.Paciente.barrio","es", "Barrio");
$m->a("casodeestudio.Paciente.ciudad","es", "Ciudad");
$m->a("casodeestudio.Paciente.departamento","es", "Departamento");
$m->a("casodeestudio.Paciente.asma","es", "Asma");
$m->a("casodeestudio.Paciente.diabetes","es", "Diabetes");
$m->a("casodeestudio.Paciente.hipertencion","es", "Hipertension");
$m->a("casodeestudio.Paciente.insuficiencia_renal","es", "Insuficiencia Renal");
$m->a("casodeestudio.Paciente.obesidad","es", "Obesidad");

// Label AND y OR para todos las views 
$m->a("casodeestudio.filterAttr.AND","es", "Y ademas");
$m->a("casodeestudio.filterAttr.OR","es", "O puede cumplir");

?>