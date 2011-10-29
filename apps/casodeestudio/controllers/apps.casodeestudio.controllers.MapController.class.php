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
		$methodName = 'get'. Enfermedad::fromName($layerName);
		
		// Ya se sabe que van a ser del tipo 'Paciente' y se va a mostrar ubicacion.
		foreach ($layer->getElements() as $paciente) {
			$this->setIconoEstado($methodName, $paciente->$methodName(), $paciente->getUbicacion());
		}

		return $this->renderString( KMLUtilities::layerToKml($layer));
	}
	
	public function setIconoEstado($enfermedad, $estado, $point) {
		$icon = $point->getUIProperty();
		$icon->setUrl('/images/'.$enfermedad.'-'.$estado.'.png');
	}
	

}

?>