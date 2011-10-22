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

		return $this->renderString( KMLUtilities::layerToKml($layer));
	}

	public function saveVisualizationAction(){

		$mapId = $this->params['mapId'];
		$json = $this->params['json'];

		header('Content-type: application/json');

		try{
			$map = Map::get($mapId);
			$map->setVisualization_json($json);
			$map->save();

			return $this->renderString('{res: true}');
		}catch (Exception $err){

			return $this->renderString('{res: false, msg: "'.$err->getMessage().'"}');
		}
	}

	public function loadVisualizationAction(){
		$map = Map::get($this->params['mapId']);

		header('Content-type: application/json');

		return $this->renderString($map->getVisualization_json());
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
		$layerId = null;
		if(array_key_exists('layerId', $this->params)) {
			$layerId = $this->params['layerId'];
		}

		$result = self::filter($className, $filterName,  $text, $layerId);

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


	private static function filter($class, $field, $param, $layerId = null){
		$result = array ();
			
		$ins = new $class(array(), true);
		$type = $ins->getType($field);



		switch ($type){
			case Datatypes::INT_NUMBER:
				$condparam = intval($param);
				$condMethod = '::EEQ';
				break;
			case Datatypes::TEXT:
				$condparam = '%'.$param.'%';
				$condMethod = '::ILIKE';
				break;
		}

		$cond = call_user_func_array('Condition'.$condMethod, array(YuppGISConventions::tableName(call_user_func_array($class.'::getClassName',array())), $field, $condparam));


		$values = call_user_func_array($class.'::findBy', array($cond, new ArrayObject()));

		if($layerId != null){
			$elements = DataLayer::get($layerId)->getElements();
			foreach ($elements as $p){
				if(in_array($p, $values)){
					$result[] = $p;
				}
			}
		}else{
			$result = $values;
		}

		return $result;
	}

	public function filterDistanceAction(){
		$mapId = $this->params['mapId'];
		$classFromName = $this->params['classFrom'];
		$filterValue = $this->params['filterValue'];
		$distance = $this->params['param'];

		$classToName = $this->params['classTo'];
		$positionfrom = $this->params['positionFrom'];
		$positionto = $this->params['positionTo'];

		$reference = call_user_func_array($classFromName .'::get', array($filterValue));



		$methodTo = 'get'.ucfirst($positionto);
		$valuesTo = $reference->$methodTo();
		$result =	array();
		if ($valuesTo != null){
			$condition = GISCondition::DWITHIN(
			YuppGISConventions::tableName($classToName),
			$positionfrom, $valuesTo, $distance);

			$result =	call_user_func_array($classToName.'::findBy', array($condition, new ArrayObject()));
		}
			
		
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