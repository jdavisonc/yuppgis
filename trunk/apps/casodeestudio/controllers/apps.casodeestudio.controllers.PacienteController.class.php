<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('casodeestudio.model', 'Paciente');

class PacienteController extends GISController {

	public function addAction() {
		if (isset($this->params['nombre'])) {
			$nombre = $this->params['nombre'];
			$apellido = $this->params['apellido'];
			$sexo = $this->params['sexo'];
			$fnac = $this->params['fechaNacimiento'];
			$ffall = $this->params['fechaFallecimiento'];
			$telefono = $this->params['telefono'];
			$email = $this->params['email'];
		}
		return ;
	}

}

?>