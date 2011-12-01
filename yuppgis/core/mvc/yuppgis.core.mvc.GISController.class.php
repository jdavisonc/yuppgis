<?php

YuppLoader::load('yuppgis.core.persistent', 'GISPersistentObject');
YuppLoader::load('yuppgis.core.basic', 'Map');
YuppLoader::load('yuppgis.core.basic', 'DataLayer');
YuppLoader::load('yuppgis.core.basic', 'Geometry');

YuppLoader::load('core.http', 'HTTPRequest');

YuppLoader::load('yuppgis.core.gis', 'KMLUtilities');
YuppLoader::load('core.persistent.serialize', 'JSONPO');

/**
 * Controlador base que se debe extender para usar los helpers para la parte gis (@link GISHelpers)
 * 
 * @package yuppgis.core.mvc
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class GISController extends YuppController {


	/**
	 * Accion que retorna el KML que representa a una capa. Ver biblioteca jquery.yuppgis.map.js,
	 */
	public function mapLayerAction(){

		$layerId = $this->params['layerId'];
		$layer = DataLayer::get($layerId);

		return $this->renderString( KMLUtilities::layerToKml($layer));
	}

	/**
	 * Accion que persiste el estado de visualizacion de los elementos de un mapa, pasados por parametros ($json)
	 */
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

	/**
	 * Accion que carga el estado de visualizacion de los elementos de un mapa
	 */
	public function loadVisualizationAction(){
		$map = Map::get($this->params['mapId']);

		header('Content-type: application/json');

		return $this->renderString($map->getVisualization_json());
	}

	/**
	 * Accion que retorna las capas de un mapa, representado en JSON. Ver biblioteca jquery.yuppgis.map.js,
	 */
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

	/**
	 * Accion que retorna el template correspondiente para mostrar el detalle de un elemento en el mapa. Ver biblioteca jquery.yuppgis.map.js.
	 */
	public function detailsAction()
	{
		$layerId = $this->params['layerId'];
		$className = $this->params['className'];
		$layer  = DataLayer::get($layerId);
		$elementId = $this->params['elementId'];
		if (isset($this->params['attrs'])) {
			$attrs = $this->params['attrs'];
		} else {
			$attrs = '';
		}

		$template = ''.$className.'.'.$layer->getName();

		if (file_exists('apps/'.$this->appName.'/views/'. $this->controllerName . '/'.$template.'.template.php')) {
			
			return $this->renderTemplate($template, array('layer'=> $layer, 'elementId' => $elementId, 'attrs' => $attrs));
		} elseif (file_exists('apps/'.$this->appName.'/views/'. $this->controllerName . '/'.$className.'.template.php')) {
			
			// template por clase, menos especifico que por layer
			return $this->renderTemplate($className, array('layer'=> $layer, 'elementId' => $elementId, 'attrs' => $attrs));
		} else {
			return $this->renderString('');
		}
	}

	/**
	 * Accion que retorna resultado para el filtro generado por {@link GISHelper}
	 */
	public function filterAction(){
		$mapId = $this->params['mapId'];
		$className = $this->params['className'];

		$layerId = null;
		if(array_key_exists('layerId', $this->params)) {
			$layerId = $this->params['layerId'];
		}

		$query = $this->params['query'];

		$result = self::filter($className, $query, $layerId);

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

	private static function filter($class, $query, $layerId = null){
		$result = array ();
			
		$model = json_decode($query);

		$baseCondition = $model->conditions[0];
		$finalCondition = self::getSingleGISCondition($class, $baseCondition);

		array_shift($model->conditions);

		if (sizeof($model->conditions)> 0){
			
			foreach ($model->conditions as $condition){
			
				$prevCond = $finalCondition;
				$finalCondition = call_user_func_array('Condition::_'.strtoupper($condition->condition), array());
				$finalCondition->add($prevCond);
				$finalCondition->add(self::getSingleGISCondition($class, $condition));
			}
		}

		$values = call_user_func_array($class.'::findBy', array($finalCondition, new ArrayObject()));

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

	private static function getSingleGISCondition($class, $condition){
		$field = $condition->field;
		$param = $condition->text;

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

		$cond = call_user_func_array('Condition'.$condMethod,
		array(YuppGISConventions::tableName(call_user_func_array($class.'::getClassName',array())), $field, $condparam));

		return $cond;
	}

	/**
	 * Accion que retorna resultados para filtro de distancia en {@link GISHelpers}
	 */
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

	/**
	 * Accion que cumple la funcion de Proxy entre servidor de mapas configurado (WMS) y OpenLayers.
	 */
	public function mapServerAction(){
			
		$hasPermission = true;
		if ($hasPermission){
			
			$appName = YuppContext::getInstance()->getApp();
			$wms_url = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_WMS_URL);
			$wms_map_file = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_WMS_MAP_FILE);
			$wms_layers = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_WMS_LAYERS);
			$wms_format = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_WMS_FORMAT);
			
			$url = $wms_url . '?' .
				  'MAP='.$wms_map_file.
				  '&LAYERS='. $wms_layers.
				  '&FORMAT='. $wms_format.
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