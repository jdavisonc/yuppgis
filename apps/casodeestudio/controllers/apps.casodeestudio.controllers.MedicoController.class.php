<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('casodeestudio.model', 'Medico');
YuppLoader::load('casodeestudio.model', 'Paciente');

class MedicoController extends GISController {

	public function listAction() {
		$this->params['list']  = Medico::listAll($this->params);
		return ;
	}

	public function mapAction(){
		return;
	}
	
	public function mapLayerAction(){

		$layerId = $this->params['layerId'];
		if ($layerId == "aux_medico") {
			
			$medico = Medico::get($this->params['medicoId']);
			$layer = new DataLayer();
			$layer->setName('Medico');
			$layer->setClassType('Medico');
			$layer->setAttributes(array('zona'));
			
			$layer->addElement($medico);
			$layer->setId('aux_medico');
			
			return $this->renderString( KMLUtilities::layerToKml($layer));
			
		} else  {
			
			$medico = Medico::get($this->params['medicoId']);
			$layerOriginal = DataLayer::get($layerId);
			
			$pacientes = $this->getPacientesEnZona($medico, $layerOriginal);
			
			$layer = new DataLayer();
			$layer->setName($layerOriginal->getName());
			$layer->setClassType($layerOriginal->getClassType());
			$layer->setAttributes($layerOriginal->getAttributes());
			$layer->setDefaultUIProperty(new Icon(0, 0, '/images/firstaid.png'));
			$layer->setElements(array());
			
			$layerName = $layer->getName(); // Nombre de la enfermedad
			$enfermedad = Enfermedad::fromName($layerName);
			$methodName = 'get'. ucfirst(Enfermedad::fromName($layerName));
			
			foreach ($pacientes as $paciente) {
				$this->setIconoEstado($enfermedad, $paciente->$methodName(), $paciente->getUbicacion());
				$layer->addElement($paciente);
			}
			$layer->setId($layerOriginal->getName());
			
			return $this->renderString( KMLUtilities::layerToKml($layer));
		}
	}
	
	public function setIconoEstado($enfermedad, $estado, $point) {
		$icon = $point->getUIProperty();
		$url = '/images/'.$enfermedad.'-'.$estado.'.png';
		if ($icon) {
			$icon->setUrl($url);
		} else {
			$point->setUIProperty(new Icon(0,0,$url));
		}
	}
	
	public function getLayersAction(){

		$map = Map::get($this->params['mapId']);
		$layers = $map->getLayers();

		header('Content-type: application/json');
		$json = '[';
		$count = sizeof($layers);
		
		// capa para el medico
		$layer = new DataLayer();
		$layer->setName('Medico');
		$layer->setClassType('Medico');
		$layer->setAttributes(array('zona'));
	
		$layer->setId('aux_medico');
		$json .= JSONPO::toJSON($layer) . ',';
		
		
		for ($i = 0; $i < $count - 1; $i++) {
			$json .= JSONPO::toJSON($layers[$i]).',' ;
		}
		
		if ($count > 0){
			$json .= JSONPO::toJSON($layers[$count-1]);
		}
		
		$json .= ']';
		
	
		return  $this->renderString($json);
	}
	
	
	private function getPacientesEnZona(Medico $m, $layer) {
		$q = new GISQuery();
		
		$pol = $m->getZona();
		$line = $pol->getExteriorBoundary();
		$points = $line->getPoints();
		
		$idElements = '';
		foreach ($layer->getElements() as $element) {
			$idElements .= $element->getId() . ',';
		}
		
		if ($idElements == '') {
			//la capa no tiene elementos
			return array();
		}
		
		$q->addProjection('p', 'id');
		$q->addProjection('p', 'ubicacion', 'u');
		$q->setCondition(Condition::_AND()
			->add(GISCondition::ISCONTAINED('p', 'ubicacion', $m->getZona()))
			->add(Condition::IN('p', 'id', substr($idElements, 0, -1)))
			);
		$q->addFrom(Paciente::getClassName(), 'p');
		
		
		$pm = PersistentManagerFactory::getManager();
		$idS = $pm->findByQuery($q);
		
		$res = array();
		foreach ($idS as $datosPaciente) {
			$res[] = Paciente::get($datosPaciente['id']);
		}
		return $res;
	}
	
	private function getAttrsPaciente($idPaciente ) {
		$element = Paciente::get($idPaciente); 
		$q = new GISQuery();
		$q->addProjection('p', 'id');
		$q->addProjection('p', 'nombre');
		$q->addProjection('p', 'apellido');
		$q->addProjection('p', 'ubicacion', 'u');
		$q->setCondition(
			GISCondition::EQGEO('p', 'ubicacion', $element->getUbicacion())
			);
		$q->addFrom(Paciente::getClassName(), 'p');
		
		$pm = PersistentManagerFactory::getManager();
		return $pm->findByQuery($q);
	}
	
	public function detailsAction() {
		$layerId = $this->params['layerId'];

		$className = $this->params['className'];
		$elementId = $this->params['elementId'];
		
		if ($layerId == 'aux_medico') {
			return $this->renderString('');
		} else  {
			// para el caso particular se tiene template por clase paciente
			$attrs = $this->getAttrsPaciente($elementId);
			return $this->renderTemplate($className, array('layer'=> null, 'elementId' => $elementId, 'attrs' => $attrs));
		} 
	}
	
	public function mostrarPorEstado() {
		
		$estado = $this->params['estado'];
		$medicoId = $this->params['medico'];
		
		$m = Medico::get($medicoId);
		
		$condition = '';
			
		if ($estado != 'todos') {
			$condition = Condition::_AND()
							->add(GISCondition::ISCONTAINED('p', 'ubicacion', $m->getZona()))
							->add(Condition::_OR()
									->add(Condition::EQ('p', Enfermedad::ASMA, $estado))
									->add(Condition::EQ('p', Enfermedad::DIABETES, $estado))
									->add(Condition::EQ('p', Enfermedad::HIPERTENSION, $estado))
									->add(Condition::EQ('p', Enfermedad::INSUFICIENCIA_RENAL, $estado))
									->add(Condition::EQ('p', Enfermedad::OBESIDAD, $estado)));
		} else {
			$condition = GISCondition::ISCONTAINED('p', 'ubicacion', $m->getZona());
		}
		
		$q = new GISQuery();
		$q->addProjection('p', 'id');
		$q->addProjection('p', 'ubicacion');
		$q->setCondition($condition);
		$q->addFrom(Paciente::getClassName(), 'p');
		$pm = PersistentManagerFactory::getManager();
		$result = $pm->findByQuery($q);
		
		
		$json = '[';
		$count = sizeof($result);
		for ($i = 0; $i < $count-1; $i++) {
			$json .= $result[$i]['id'].',' ;
		}
		if ($count > 0){
			$json .= $result[$count-1]['id'] ;
		}
			
		$json .= ']';
		header('Content-type: application/json');

		return $this->renderString($json);
		
	}
	
}

?>