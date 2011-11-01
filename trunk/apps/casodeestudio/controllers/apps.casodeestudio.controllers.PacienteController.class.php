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
	
	public function listAction() {
		$this->params['list']  = Paciente::listAll($this->params);
		return ;
	}
	
	public function infoAction() {
		$id = $this->params['id'];
		$this->params['paciente'] = Paciente::get($id);
		return ;
	}
	
	public function editEnfAction() {
		$id = $this->params['id'];
		$paciente = Paciente::get($id);
		
		if (isset($this->params['edited'])) {
			$paciente->aSet(Enfermedad::ASMA, $this->params[Enfermedad::ASMA]);
			$paciente->aSet(Enfermedad::DIABETES, $this->params[Enfermedad::DIABETES]);
			$paciente->aSet(Enfermedad::HIPERTENCION, $this->params[Enfermedad::HIPERTENCION]);
			$paciente->aSet(Enfermedad::INSUFICIENCIA_RENAL, $this->params[Enfermedad::INSUFICIENCIA_RENAL]);
			$paciente->aSet(Enfermedad::OBESIDAD, $this->params[Enfermedad::OBESIDAD]);
			
			// se editan las enfermedades como capas
			$this->editEnfermedades($paciente);
			
			if ($paciente->save()) {
				return $this->redirect(array ("action" => "info", "params" => array("id" => $id)));
			} else {
				$this->params['error'] = $p;
			}
		} else {
			$this->params['paciente'] = $paciente;
		}
		return ;
	}
	
	/**
	 * Se sabe el ID por el orden en que inserto el bootstrap las enfermedades
	 * @param unknown_type $paciente
	 */
	private function editEnfermedades($paciente) {
		if ($paciente->aGet(Enfermedad::ASMA)) {
			$this->addToEnfermedad(1, $paciente);
		} else {
			$this->deleteOfEnfermedad(1, $paciente);
		}
		if ($paciente->aGet(Enfermedad::DIABETES)) {
			$this->addToEnfermedad(2, $paciente);
		} else {
			$this->deleteOfEnfermedad(2, $paciente);
		}
		if ($paciente->aGet(Enfermedad::HIPERTENCION)) {
			$this->addToEnfermedad(3, $paciente);
		} else {
			$this->deleteOfEnfermedad(3, $paciente);
		}
		if ($paciente->aGet(Enfermedad::HIPERTENCION)) {
			$this->addToEnfermedad(4, $paciente);
		} else {
			$this->deleteOfEnfermedad(4, $paciente);
		}
		if ($paciente->aGet(Enfermedad::OBESIDAD)) {
			$this->addToEnfermedad(5, $paciente);
		} else {
			$this->deleteOfEnfermedad(5, $paciente);
		}
	}
	
	private function addToEnfermedad($id, $paciente) {
		$dl = DataLayer::get($id);
		$dl->addElement($paciente);
		$dl->save();
	}
	
	private function deleteOfEnfermedad($id, $paciente) {
		$dl = DataLayer::get($id);
		$dl->removeElement($paciente);
		$dl->save();
	}

}

?>