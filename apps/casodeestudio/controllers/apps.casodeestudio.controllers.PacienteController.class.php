<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('casodeestudio.model', 'Paciente');

class PacienteController extends GISController {
	
	const URL_WS_CALLES = 'http://localhost/yuppgis/geolocalizacion/geo/calles?calle=';
	const URL_WS_GEOLOCALIZACION = 'http://localhost/yuppgis/geolocalizacion/geo/geolocalizar?';
	
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
			
			
			$calle = $this->params['calle'];
			$numero = $this->params['numero_puerta'];
			$p->setDireccion($calle . ' ' . $numero);
			$p->setBarrio($this->params['barrio']);
			$p->setCiudad($this->params['ciudad']);
			$p->setDepartamento($this->params['departamento']);
			
			$ubicacion = $this->getUbicacionPaciente($calle, $numero);
			$p->setUbicacion($ubicacion);
			
			if ($p->save()) {
				$this->params['inserted'] = $p->getId();
			} else {
				$this->params['error'] = $p;
			}
		}
		$this->params['url_ws_calles'] = self::URL_WS_CALLES;
		return ;
	}
	
	
	public function getUbicacionPaciente($calle, $numero) {
		$url = self::URL_WS_GEOLOCALIZACION . 'calle=' . $calle . '&numero=' . $numero;
		
		$request = new  HTTPRequest();
		$response = $request->HttpRequestGet($url);
		if ($response->getStatus() == 200) {
			$element = $response->getBody();
			if ($element != 'Resultado no encontrado') {
				$elements = KMLUtilities::KMLToGeometry($element); 
				return $elements[0];
			} else {
				//error
			}
		} else {
			//error
		}
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