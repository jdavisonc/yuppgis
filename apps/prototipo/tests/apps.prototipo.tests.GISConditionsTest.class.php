<?php

YuppLoader::load('yuppgis.core.testing', 'YuppGISTestCase');
YuppLoader::load('prototipo.model', 'Paciente');

/**
 * Test para probar condiciones geograficas
 * @author harley
 */
class GISConditionsTest extends YuppGISTestCase {
	
	function testFindBy() {
		$and = Condition::_AND()
			->add(GISCondition::ISCONTAINED(
				YuppGISConventions::tableName(Paciente::getClassName()), 
				'ubicacion', new Point(10, 10)))
			->add(Condition::EQ(YuppGISConventions::tableName(Paciente::getClassName()), 'nombre', 'Roberto'));
		
		$pacientes = Paciente::findBy($and, new ArrayObject());
		
		$this->assert($pacientes !== null, 'Test de filtrado de pacientes');
	}
	
	function testFindByEquals() {
		$and = Condition::_AND()
			->add(GISCondition::EQGEO(
				YuppGISConventions::tableName(Paciente::getClassName()), 
				'ubicacion', new Point(10, 10)))
			->add(Condition::EQ(YuppGISConventions::tableName(Paciente::getClassName()), 'nombre', 'Roberto'));
		
		$pacientes = Paciente::findBy($and, new ArrayObject());
		
		$this->assert($pacientes !== null, 'Test de filtrado de pacientes por igualdad de figuras');
	}
	
	function testFindByIntersection() {
		$and = Condition::_AND()
			->add(GISCondition::INTERSECTS(
				YuppGISConventions::tableName(Paciente::getClassName()), 
				'ubicacion', new Point(10, 10)))
			->add(Condition::EQ(YuppGISConventions::tableName(Paciente::getClassName()), 'nombre', 'Roberto'));
		
		$pacientes = Paciente::findBy($and, new ArrayObject());
		
		$this->assert($pacientes !== null, 'Test de filtrado de pacientes por interseccion');
	}
	
	function testFindByDWithin() {
		$and = Condition::_AND()
			->add(GISCondition::DWITHIN(
				YuppGISConventions::tableName(Paciente::getClassName()), 
				'ubicacion', new Point(10, 10), 0))
			->add(Condition::EQ(YuppGISConventions::tableName(Paciente::getClassName()), 'nombre', 'Roberto'));
		
		$pacientes = Paciente::findBy($and, new ArrayObject());
		
		$this->assert($pacientes !== null, 'Test de filtrado de pacientes de aquellos que esten a una distancia 0 (DWITHIN)');
	}
	
}