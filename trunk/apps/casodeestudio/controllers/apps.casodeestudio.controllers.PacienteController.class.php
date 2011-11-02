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
			
			$asma = $this->params[Enfermedad::ASMA];
			$diabetes = $this->params[Enfermedad::DIABETES];
			$hipertencion = $this->params[Enfermedad::HIPERTENCION];
			$insuficiencia_renal = $this->params[Enfermedad::INSUFICIENCIA_RENAL];
			$obesidad = $this->params[Enfermedad::OBESIDAD];
			
			$this->changeEnfermedadOnPaciente($paciente, $asma, Enfermedad::ASMA);
			$this->changeEnfermedadOnPaciente($paciente, $diabetes, Enfermedad::DIABETES);
			$this->changeEnfermedadOnPaciente($paciente, $hipertencion, Enfermedad::HIPERTENCION);
			$this->changeEnfermedadOnPaciente($paciente, $insuficiencia_renal, Enfermedad::INSUFICIENCIA_RENAL);
			$this->changeEnfermedadOnPaciente($paciente, $obesidad, Enfermedad::OBESIDAD);
			
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
	

	private function changeEnfermedadOnPaciente($paciente, $nuevoValor, $enfermedad) {
		if ($paciente->aGet($enfermedad) != $nuevoValor) {
			$paciente->aSet($enfermedad, $nuevoValor);
			if ($nuevoValor != '') { // Falta verificar que ya no este en esa capa
				$this->addToEnfermedad($enfermedad, $paciente);
			} else {
				$this->deleteOfEnfermedad($enfermedad, $paciente);
			}
		}
	}
	
	private function addToEnfermedad($enfermedad, $paciente) {
		$id = Enfermedad::getLayerIdForEnfermedad($enfermedad);
		$dl = DataLayer::get($id);
		$dl->addElement($paciente);
		$dl->save();
	}
	
	private function deleteOfEnfermedad($enfermedad, $paciente) {
		$id = Enfermedad::getLayerIdForEnfermedad($enfermedad);
		$dl = DataLayer::get($id);
		$dl->removeElement($paciente);
		$dl->save();
	}

}

?>