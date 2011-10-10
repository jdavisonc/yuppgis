<?php

YuppLoader::load('yuppgis.core.persistent', 'GISPersistentObject');
YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('yuppgis.core.basic', 'Geometry');
YuppLoader::load('core.http', 'HTTPRequest');

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

		if (file_exists('apps/'.$this->appName.'/views/'.$this->controllerName.'/'.$template.'.template.php')){
			return $this->renderTemplate($template, array('layer'=> $layer, 'elementId' => $elementId));
		}else{
			return $this->renderString('');
		}
	}

	public function filterAction(){
		$mapId = $this->params['mapId'];
		$className = $this->params['className'];
		$filterName = $this->params['filterName'];
		$text = $this->params['param'];
		$methodName = $filterName;


		$result = call_user_func($className.'::'.$methodName,  $text);

		$json = '[';
		$count = sizeof($result);
		for ($i = 0; $i < $count-1; $i++) {
			$json .= JSONPO::toJSON($result[$i]).',' ;
		}
		if ($count > 0){
			$json .= JSONPO::toJSON($result[$count-1]) ;
		}
			
		$json .= ']';
		header('Content-type: application/json');

		return $this->renderString($json);
	}


	public function mapServerAction(){
			
		$hasPermission = true;
		if ($hasPermission){
			$url = 'http://localhost/cgi-bin/mapserv?';
			$url .=
		  'MAP='.$this->params['MAP'].
		  '&LAYERS='. $this->params['LAYERS'].
		  '&FORMAT='. $this->params['FORMAT'].
		  '&SERVICE='. $this->params['SERVICE'].
		  '&VERSION='. $this->params['VERSION'].
		  '&REQUEST='. $this->params['REQUEST'].
		  '&STYLES='. $this->params['STYLES'].
		  '&SRS='. $this->params['SRS'].
		  '&BBOX='. $this->params['BBOX'].
		  '&WIDTH='. $this->params['WIDTH'].
		  '&HEIGHT='. $this->params['HEIGHT'];

			$request = new  HTTPRequest();

			$response = $request->HttpRequestGet($url);
			header('Content-Type: image/png');

			return $this->renderString($data = $response->getBody());
		}else{
			$im = imagecreatetruecolor(120, 20);
			$text_color = imagecolorallocate($im, 233, 14, 91);
			imagestring($im, 1, 5, 5, 'Sin permiso...', $text_color);
				
			header('Content-Type: image/png');
				
			imagepng($im);
			imagedestroy($im);
				
			return $this->renderString('');
		}
	}

}

?>