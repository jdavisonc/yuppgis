<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('casodeestudio.model', 'Paciente');

class PacienteController extends GISController {

	public function addAction() {
		if (isset($this->params['nombre'])) {
			$p = new Paciente();
			$p->setNombre($this->params['nombre']);
			$p->setApellido($this->params['apellido']);
			$p->setSexo($this->params['sexo']);
			if ($this->params['fechaNacimiento']) {
				$p->setFechaNacimiento(null);
			}
			if ($this->params['fechaFallecimiento']) {
				$p->setFechaFallecimiento(null);
			}
			$p->setTelefono($this->params['telefono']);
			$p->setEmail($this->params['email']);
			$p->setCi($this->params['ci']);
			
			$p->setDireccion($this->params['direccion']);
			$p->setBarrio($this->params['barrio']);
			$p->setCiudad($this->params['ciudad']);
			$p->setDepartamento($this->params['departamento']);
			// TODO_GIS: Make Magic
			$p->setUbicacion(new Point(-56.149921, -34.899518));
			
			if ($p->save()) {
				$this->params['inserted'] = $p->getId();
			} else {
				$this->params['error'] = $p;
			}
		}
		return ;
	}

}

?>