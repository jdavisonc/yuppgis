<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('casodeestudio.model', 'Paciente');

class MapController extends GISController {

	public function indexAction()
	{
		return $this->redirect(array ("action" => "map"));
	}

	public function mapAction(){
		return;
	}
	
	public function mapLayerAction(){
		$layerId = $this->params['layerId'];
		$layer = DataLayer::get($layerId);
		
		$layerName = $layer->getName(); // Nombre de la enfermedad
		$enfermedad = Enfermedad::fromName($layerName);
		$methodName = 'get'. ucfirst(Enfermedad::fromName($layerName));
		
		// Ya se sabe que van a ser del tipo 'Paciente' y se va a mostrar ubicacion.
		foreach ($layer->getElements() as $paciente) {
			$this->setIconoEstado($enfermedad, $paciente->$methodName(), $paciente->getUbicacion());
		}

		return $this->renderString( KMLUtilities::layerToKml($layer));
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
	
	public function detailsAction() {
		$element = Paciente::get($this->params['elementId']); 
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
		$this->params['attrs'] = $pm->findByQuery($q); // Se envian datos adicionales para desplegar el template
		
		return parent::detailsAction();
	}

}

?>