<?php

YuppLoader::load('yuppgis.core.utils', 'ReflectionUtils');
YuppLoader::load('core.mvc', 'DisplayHelper');

class GISHelpers {

	/**
	 * Obtiene las acciones declaradas en la clase
	 * @param nombre de la clase
	 * @return array con nombre de las acciones
	 */
	public static function AvailableActions($class){

		return ReflectionUtils::ReflectMethods($class, 'Action', true);
	}


	/**
	 * Obtiene los filtros declarados en la clase
	 * @param nombre de la clase
	 * @return array con nombre de los filtros
	 */
	public static function AvailableFilters($class){

		//return ReflectionUtils::ReflectMethods($class, 'Filter', true);

		if (!(is_subclass_of($class, 'PersistentObject' ))){
			throw new Exception('La clase debe ser subclase de PersistentObject', 0);
		}

		$ins = new $class(array(), true);
		$simpleAttr = $ins->getSimpleAssocAttrNames();
		$manyAttr = $ins->getManyAssocAttrNames();

		$attrs = $ins->getAttributeTypes();

		$fields = array();
		foreach (array_keys($attrs) as $attr){
			if (!(in_array($attr, YuppGISConventions::getReservedWords())) &&
			!(in_array($attr, $simpleAttr)) &&
			!(in_array($attr, $manyAttr))
			){
				$fields[] = $attr;
			}
		}

		return $fields;

	}


	/**
	 * Genera el html para un combo de selección
	 * @param nombre de la clase
	 * @param id del elemento
	 * @return html generado para el menú
	 */
	public static function ActionsMenu($class, $id){
		$html = '<select  id="'.$id.'">';
		$html .= '<option value="nothing"></option>';

		foreach (self::AvailableActions($class) as $option){
			$html .= '<option value="'.$option.'">'.$option.'</option>';
		}

		$html .= '</select>';

		return $html;
	}

	/**
	 * Genera el html para un combo de selección
	 * @param nombre de la clase
	 * @param id del elemento
	 * @return html generado para el menú
	 */
	public static function FiltersMenu($class, $mapid, $handler = null, $layerId = null, $multiple = false){
		$ctx = YuppContext::getInstance();
		$appName = $ctx->getApp();
		$controllerName = strtolower($ctx->getController());
		
		$groupId = $class.'_'.$mapid.'_'.uniqid();
		$selectId = 'select_'.$groupId;
		$tbId = 'tbFiltersMenu_'.$groupId;
		$methodName = 'filter_'.$groupId;
		$btnId = 'btnFiltersMenu_'.$groupId;

		$selectHtml = '<select class="conditionselect" data-attr-mapid="'.$mapid.'" id="'.$selectId.'">';

		foreach (self::AvailableFilters($class) as $option){
			$selectHtml .= '<option value="'.$option.'">'.$option.'</option>';
		}

		$handlerCall = '';
		if ($handler != null){
			$handlerCall = $handler.'(data);';
		}else{
			$handlerCall = '$("#map_'.$mapid.'").YuppGISMap().showFeatures(extractIds(data), true);';
		}

		$selectHtml .= '</select><br>';

		$inputHtml = '<input class="conditiontext" data-attr-mapid="'.$mapid.'" type="text" id="'.$tbId.'" />';

		$submitHtml = '<br /><button class="btn" id="'.$btnId.'" onclick="javascript:return '.$methodName.'()">Filtrar</button>';

		$script = '<script>
						function '.$methodName.'(){
							var selectedOption = $("#'.$selectId.'").val();
							var text = $("#'.$tbId.'").val();
							
							 $.ajax({
							      url: "/yuppgis/'.$appName.'/'.$controllerName.'/Filter",
							      data: {';

		$script .= '
									query: JSON.stringify(getMultipleConditionJson($("#'.$btnId.'"))),				
									className: "'.$class.'",
							        mapId: '.$mapid;

		if($layerId != null){
			$script .= ' 					      	,
							      	layerId: '.$layerId.'';
		}

		$script .= '
							      },			      			      			      
							      success: function(data){							      	
							      	'.$handlerCall.'
							      }
							  })
							  
							  return false;
						}
				</script>		
		';

		$addConditionHtml = '';
		if ($multiple){
			$addConditionHtml = '<br><button class="btn addcondition" onclick="javascript:return addNewCondition(this);">+</button>';
		}

		return  '<span class="conditionfilter"><span class="newcondition">'.$selectHtml.$inputHtml.$addConditionHtml.'</span><br />'.$submitHtml.$script.'</span>';

	}

	/*Mapa*/

	/**
	 * Genera el html para desplegar un mapa en pantalla
	 * @param lista de parámetros de tipo enumerado MapParams
	 * @return html generado para el mapa
	 */
	public static function Map($params=null){

		$ctx = YuppContext::getInstance();
		$appName = $ctx->getApp();
		$controllerName = strtolower($ctx->getController());
		
		$id = MapParams::getValueOrDefault($params, MapParams::ID);
		$olurl = MapParams::getValueOrDefault($params, MapParams::OpenLayerJS_URL);
		$width = MapParams::getValueOrDefault($params, MapParams::WIDTH);
		$height = MapParams::getValueOrDefault($params, MapParams::HEIGHT);
		$border = MapParams::getValueOrDefault($params, MapParams::BORDER);
		$type = MapParams::getValueOrDefault($params, MapParams::TYPE);
		$clickhandlers = MapParams::getValueOrDefault($params, MapParams::CLICK_HANDLERS);
		$selecthandlers = MapParams::getValueOrDefault($params, MapParams::SELECT_HANDLERS);
		$state = MapParams::getValueOrDefault($params, MapParams::STATE);
		$srid = MapParams::getValueOrDefault($params, MapParams::SRID);
		$center = MapParams::getValueOrDefault($params, MapParams::CENTER);
		$zoom = MapParams::getValueOrDefault($params, MapParams::ZOOM); 

		LayoutManager::getInstance()->addJSLibReference( array("name" => "jquery/jquery-1.6.1.min"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/OpenLayers"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/common"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/jquery.yuppgis.map"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/multiplecondition"));

		$html =	'
		 
		<script src="'.$olurl.'" type="text/javascript"></script>	
		
		<link type="text/css" rel="stylesheet" href="/yuppgis/yuppgis/js/gis/OpenLayers.css" />
		
	
		<style type="text/css">
			#map_'.$id.' {
				width: '.$width.';
				height: '.$height.';
				border: '.$border.';
				}
		</style>
		
		<div id="map_'.$id.'"></div>
		
		
		<script type="text/javascript">
		
			$("#map_'.$id.'").YuppGISMap({id: '.$id.', type: "'.$type.'", appName: "'.$appName.'", '.
					'controllerName: "'.$controllerName.'" , state: "'.$state.'", srid: "'.$srid.'", center: ["'.$center[0].'", "'.$center[1].'"], zoom: "'.$zoom.'"})
		';			

		foreach ($clickhandlers as $clickhandler){
			$html .= '.addClickHandler("'.$clickhandler.'")';
		}

		foreach ($selecthandlers as $selecthandler){
			$html .= '.addSelectHandler("'.$selecthandler.'")';
		}

		$html .= ';</script>';

		return  $html;

	}

	public static function MapLayers($params=null){
		$id = MapParams::getValueOrDefault($params, MapParams::ID);

		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/OpenLayers"));

		$map = Map::get($id);
		$layers = $map->getLayers();
		$html =  '<ul>';
		foreach ($layers as $layer){
			$layerId = $layer->getId();
			$checkboxId = 'chb_'.$id.'_'.$layerId;
			if ($layer->getDefaultUIProperty() instanceof Icon) {
				$image = '<img src="'.$layer->getDefaultUIProperty()->getUrl().'" >';
			}
			$html .= '<li style="list-style-type: none">'.$image.DisplayHelper::check($checkboxId, true,
			array(
					'id'=> $checkboxId, 
					'onclick' => GISHelpers::MapLayerHandler($id, $layerId, $checkboxId),
				 	'data-attr-layerid' => $layerId,
					'data-attr-mapid' => $id )
			).'<label for="'.$checkboxId.'">'.$layer->getName().'</label></li>';
		}

		return $html.'</ul>';
	}

	private static function MapLayerHandler($mapId, $layerId, $checkboxId){

		$html = 'javascript:$(\'#map_'.$mapId.'\').YuppGISMap().map.getLayersByName('.$layerId.')[0].setVisibility($(\'#'.$checkboxId.'\').is(\':checked\'))';

		return $html;
	}

	public static function Log($mapId){
		$html = '<div data-attr-mapid="'.$mapId.'" class="logarea" style="width:550px!important;height:220px; overflow:scroll!important;" id="log_'.$mapId.'"></div>';

		return $html;
	}

	public static function TagLayers($params=null){
		$myTags = array ();
		$id = MapParams::getValueOrDefault($params, MapParams::ID);
		$map = Map::get($id);
		$layers = $map->getLayers();
		$html =  '<ul>';
		foreach ($layers as $layer){
			$tags = $layer->getTags();
			$layerId = $layer->getId();
			foreach ($tags as $tag){
				if(!in_array($tag, $myTags)){
					$myTags[] = $tag;
					$tagId = $tag->getId();
					$checkboxId = 'chb_'.$id.'_'.$layerId.'_'.$tagId;
					$html .= '<li style="list-style-type: none">'.DisplayHelper::check($checkboxId, true,
					array(
						'id'=> $checkboxId, 
						'onclick' => GISHelpers::TagLayerHandler($id, $tagId, $checkboxId),						

						'data-attr-mapid' => $id

					)).'<label for="'.$checkboxId.'">'.$tag->getName().'</label></li>';
				}
			}
		}
		return $html.'</ul>';
	}

	private static function TagLayerHandler($mapId, $tagId, $checkboxId){
		$map = Map::get($mapId);
		$layers = $map->getLayers();
		$html = "";
		foreach ($layers as $layer){
			$tags = $layer->getTags();
			foreach ($tags as $tag){
				if($tag->getId() == $tagId){
					$layerId = $layer->getId();
					$html .= '$(\'#map_'.$mapId.'\').YuppGISMap().map.getLayersByName('.$layerId.')[0].setVisibility($(\'#'.$checkboxId.'\').is(\':checked\'));';
				}
			}
		}
		return $html;
	}

	public static function VisualizationState($mapId){
		$ctx = YuppContext::getInstance();
		$appName = $ctx->getApp();
		$controllerName = strtolower($ctx->getController());
		
		$saveMethod = '
		<script type="text/javascript">
		function saveVisualizationState_'.$mapId.'(){
						
			 $.ajax({
			      url: "/yuppgis/'.$appName.'/'.$controllerName.'/saveVisualization",
			      type: "POST",
			      data: {			        
			        mapId: '.$mapId.',
			      	json: JSON.stringify($("#'.$mapId.'").YuppGISMap().getVisualizationState())
			      }
			  })
			  
			  return false;
		}
		</script><br/>
		';

		$html = $saveMethod;
		$html .= '<a href="#" id="btnSaveVisualizationState_'.$mapId.'" onclick="javascript:return saveVisualizationState_'.$mapId.'()">Persistir</a>';
		$html .= '<br />';

		$load_params = array(
			"app" => $appName, 
			"controller" => $controllerName, 
			"action"=>"loadVisualization",		 	
			"mapId" => $mapId,			 
			"body" => "Restaurar",
			"before" => "log('beforeSend')",
			"after" => "function(state){ 
							$('#map_".$mapId."').YuppGISMap().loadVisualizationState(state);
						}"
			
						);

						$html .= Helpers::ajax_link($load_params);

						return $html;
	}

	/**
	 * Genera el html para un combo de selección
	 * @param nombre de la clase
	 * @param id del elemento
	 * @return html generado para el menú
	 */
	public static function DistanceFilterMenu(
	$classfrom, $mapid, $handler = null, $fieldName='nombre', $positionfrom='ubicacion',
	$classto, $positionto='zonas'){

		$ctx = YuppContext::getInstance();
		$appName = $ctx->getApp();
		$controllerName = strtolower($ctx->getController());
		
		$groupId = $classfrom.'_'.$mapid.'_'.uniqid();

		$selectId = 'select_'.$groupId;
		$html = '<select data-attr-mapid="'.$mapid.'" id="'.$selectId.'">';
		$params = new ArrayObject() ;
		$method = 'get'.ucfirst($fieldName);

		foreach (call_user_func($classfrom.'::listAll',$params) as $option){

			$html .= '<option value="'.$option->getId().'">'.$option->$method().'</option>';
		}

		$handlerCall = '';
		if ($handler != null){
			$handlerCall = $handler.'(data);';
		}else{
			$handlerCall = '$("#map_'.$mapid.'").YuppGISMap().showFeatures(extractIds(data), true);';
		}

		$tbId = 'tbFiltersMenu_'.$groupId;
		$html .= '</select>';
		$html .= '<input onkeypress="return onlyNumbers(event)" data-attr-mapid="'.$mapid.'" type="text" id="'.$tbId.'" />';

		$methodName = 'filter_'.$groupId;

		$btnId = 'btnFiltersMenu_'.$groupId;
		$html .= '<a href="#" id="'.$btnId.'" onclick="javascript:return '.$methodName.'()">Filtrar</a>';

		$script = '<script>
						function '.$methodName.'(){
							var selectedOption = $("#'.$selectId.'").val();
							var text = $("#'.$tbId.'").val();
							
							 $.ajax({
							      url: "/yuppgis/'.$appName.'/'.$controllerName.'/FilterDistance",
							      data: {
							        filterValue: selectedOption,
							        classFrom: "'.$classfrom.'",
							        mapId: '.$mapid.',
							      	param: text,
							      	positionFrom: "'.$positionfrom.'",
							      	classTo: "'.$classto.'",
							      	positionTo: "'.$positionto.'"
							      },			      			      			      
							      success: function(data){
							      	'.$handlerCall.'
							      }
							  })
							  
							  return false;
						}
				</script>		
		';

		$html .= $script;

		return $html;
	}

}


?>