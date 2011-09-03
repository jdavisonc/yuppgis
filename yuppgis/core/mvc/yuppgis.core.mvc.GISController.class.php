<?php
YuppLoader::load('yuppgis.core.persistent', 'GISPersistentObject');
YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('yuppgis.core.basic', 'Geometry');


YuppLoader::load('yuppgis.core.gis', 'KMLUtilities');
YuppLoader::load('core.persistent.serialize', 'JSONPO');

class GISController extends YuppController {


	public function mapLayerAction(){

		$layerId = $this->params['layerId'];
		$layer = DataLayer::get($layerId);

		return $this->renderString( KMLUtilities::LayerToKml($layer));
	}

	public function getLayersAction(){
		
		$map = Map::get($this->params['mapId']);
		$layers =$map->getLayers();

		header('Content-type: application/json');
		$json = '[';
		$count = sizeof($layers);
		for ($i = 0; $i < $count-1; $i++) {
			$json .= JSONPO::toJSON($layers[$i]).',' ;
		}
		if ($count > 0){
			$json .= JSONPO::toJSON($layers[$count-1]) ;
		}
		 
		$json .= ']';

		return  $this->renderString($json);
	}
	
	public function detailsAction()
	{
		$layerId = $this->params['layerId'];
		$className = $this->params['className'];
		$layer  = DataLayer::get($layerId);		
		$elementId = $this->params['elementId'];
		
		$template = ''.$className.'.'.$layer->getName();
		
		return $this->renderTemplate($template, array('layer'=> $layer, 'elementId' => $elementId));
	}

}

?>