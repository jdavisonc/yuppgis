<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('casodeestudio.model', 'Medico');
YuppLoader::load('casodeestudio.model', 'Paciente');

class MedicoController extends GISController {

	
	public function addAction() {
	}
	
	public function listAction() {
		$this->params['list']  = Medico::listAll($this->params);
		return ;
	}

	public function infoAction() {
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
			$layer->setId('aux_paciente');
			
			return $this->renderString( KMLUtilities::layerToKml($layer));
		} else if ('aux_paciente') {
			$medico = Medico::get($this->params['medicoId']);
			$pacientes = $this->getPacientesEnZona($medico);
			
			$layer = new DataLayer();
			$layer->setName('Pacientes');
			$layer->setClassType('Paciente');
			$layer->setAttributes(array('ubicacion'));
			$layer->setDefaultUIProperty(new Icon(0, 0, '/images/firstaid.png'));
			
			foreach ($pacientes as $paciente) {
				$layer->addElement($paciente);
			}
			$layer->setId('aux_paciente');
			
			return $this->renderString( KMLUtilities::layerToKml($layer));
		} else {
			$layer = DataLayer::get($layerId);
			return $this->renderString( KMLUtilities::layerToKml($layer));
		}
	}
	
	
	public function getLayersAction(){

		$map = Map::get($this->params['mapId']);
		$layers =$map->getLayers();

		header('Content-type: application/json');
		$json = '[';
		$count = sizeof($layers);
		for ($i = 0; $i < $count - 1; $i++) {
			$json .= JSONPO::toJSON($layers[$i]).',' ;
		}
		
		if ($count > 0){
			$json .= JSONPO::toJSON($layers[$count-1]) . ',' ;
		}
		
		// capa para el medico
		$layer = new DataLayer();
		$layer->setName('Medico');
		$layer->setClassType('Medico');
		$layer->setAttributes(array('zona'));
	
		$layer->setId('aux_medico');
		$json .= JSONPO::toJSON($layer) . ', ';
		
		// capa para los pacientes
		$layer = new DataLayer();
		$layer->setName('Pacientes');
		$layer->setClassType('Paciente');
		$layer->setAttributes(array('ubicacion'));
		
		$layer->setId('aux_pacientes');
		$json .= JSONPO::toJSON($layer);
			
		$json .= ']';
	
		return  $this->renderString($json);
	}
	
	
	private function getPacientesEnZona(Medico $m) {
		$q = new GISQuery();
		
		$pol = $m->getZona();
		$line = $pol->getExteriorBoundary();
		$points = $line->getPoints();
		
		$q->addProjection('p', 'id');
		$q->addProjection('p', 'ubicacion', 'u');
		$q->setCondition(
			GISCondition::ISCONTAINED('p', 'ubicacion', $m->getZona())
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
		} else if ('aux_paciente') {
			// para el caso particular se tiene template por clase paciente
			$attrs = $this->getAttrsPaciente($elementId);
			return $this->renderTemplate($className, array('layer'=> null, 'elementId' => $elementId, 'attrs' => $attrs));
		} else {
			return parent::deleteAction();
		}
	}
	
}

?>