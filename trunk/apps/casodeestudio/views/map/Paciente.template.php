
 <?php  
 
	$element = Paciente::get($elementId); 
	$q = new GISQuery();
	$q->addProjection('p', 'nombre');
	$q->addProjection('p', 'apellido');
	$q->addProjection('p', 'ubicacion', 'u');
	$q->setCondition(
		GISCondition::EQGEO('p', 'ubicacion', $element->getUbicacion())
		);
	$q->addFrom(Paciente::getClassName(), 'p');
	
	$pm = PersistentManagerFactory::getManager();
	$result = $pm->findByQuery($q); 
?>
 
 Integrantes: 
 
  <?php 
    echo "<br>";
  	foreach ($result as $p) {
  		echo $p['nombre'] . " - "	. $p['apellido'] . "<br>";
  	}
  	 ?> 