<?php

YuppLoader::load('yuppgis.core.utils', 'ReflectionUtils');
YuppLoader::load('core.mvc', 'DisplayHelper');
YuppLoader::load('core.mvc', 'DisplayHelper');

class GISHelpers{

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

		return ReflectionUtils::ReflectMethods($class, 'Filter', true);
	}


	/**
	 * Genera el html para un combo de selección
	 * @param nombre de la clase
	 * @param id del elemento
	 * @return html generado para el menú
	 */
	public static function ActionsMenu($class, $id){
		$html = '<select id="'.$id.'">';
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
	public static function FiltersMenu($class, $mapid, $handler = null){
		$appName = YuppContext::getInstance()->getApp();
		$random = uniqid();
		
		$html = '<select id="select_'.$class.'_'.$mapid.'_'.$random.'">';
		$html .= '<option value="nothing"></option>';

		foreach (self::AvailableFilters($class) as $option){
			$html .= '<option value="'.$option.'">'.str_ireplace('Filter', '', $option).'</option>';
		}
		
		$handlerCall = '';
		if ($handler != null){
			$handlerCall = $handler.'(data);';
		}else{
		 	$handlerCall = '$("#map_'.$mapid.'").YuppGISMap().showFeatures(extractIds(data), true);';			
		}

		$html .= '</select>';
		$html .= '<input type="text" id="tbFiltersMenu_'.$class.'_'.$mapid.'_'.$random.'" />';		
		
		$methodName = 'filter_'.$class.'_'.$mapid.'_'.$random;
		
		$html .= '<a href="#" id="btnFiltersMenu_'.$class.'_'.$mapid.'_'.$random.'" onclick="javascript:return '.$methodName.'()">Filtrar</a>';

		$script = '<script>
						function '.$methodName.'(){
							var selectedOption = $("#select_'.$class.'_'.$mapid.'_'.$random.'").val();
							var text = $("#tbFiltersMenu_'.$class.'_'.$mapid.'_'.$random.'").val();
							
							 $.ajax({
							      url: "/yuppgis/'.$appName.'/Home/Filter",
							      data: {
							        filterName: selectedOption,
							        className: "'.$class.'",
							        mapId: '.$mapid.',
							      	param: text
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

	/*Mapa*/

	/**
	 * Genera el html para desplegar un mapa en pantalla
	 * @param lista de parámetros de tipo enumerado MapParams
	 * @return html generado para el mapa
	 */
	public static function Map($params=null){

		$appName = YuppContext::getInstance()->getApp();
		$id = MapParams::getValueOrDefault($params, MapParams::ID);
		$olurl = MapParams::getValueOrDefault($params, MapParams::OpenLayerJS_URL);
		$width = MapParams::getValueOrDefault($params, MapParams::WIDTH);
		$height = MapParams::getValueOrDefault($params, MapParams::HEIGHT);
		$border = MapParams::getValueOrDefault($params, MapParams::BORDER);	
		$type = MapParams::getValueOrDefault($params, MapParams::TYPE);
		$clickhandlers = MapParams::getValueOrDefault($params, MapParams::CLICK_HANDLERS);
		$selecthandlers = MapParams::getValueOrDefault($params, MapParams::SELECT_HANDLERS);

		LayoutManager::getInstance()->addJSLibReference( array("name" => "jquery/jquery-1.6.1.min"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/OpenLayers"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/common"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/jquery.yuppgis.map"));

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
		
			$("#map_'.$id.'").YuppGISMap({id: '.$id.', type: "'.$type.'", appName: "'.$appName.'"})
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
			$image = '<img src="'.$layer->getIconurl().'" >';
			$html .= '<li style="list-style-type: none">'.$image.DisplayHelper::check($checkboxId, true, array('id'=> $checkboxId, 'onclick' => GISHelpers::MapLayerHandler($id, $layerId, $checkboxId))).'<label for="'.$checkboxId.'">'.$layer->getName().'</label></li>';
		}
		
		return $html.'</ul>';
	}
	
	private static function MapLayerHandler($mapId, $layerId, $checkboxId){
		
		$html = 'javascript:$(\'#map_'.$mapId.'\').YuppGISMap().map.getLayersByName('.$layerId.')[0].setVisibility($(\'#'.$checkboxId.'\').is(\':checked\'))';
		
		return $html;
	}
	
	public static function Log($mapId){
		$html = '<div class="logarea" style="width:550px!important;height:220px; overflow:scroll!important;" id="log_'.$mapId.'"></div>';
		
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
					$html .= '<li style="list-style-type: none">'.DisplayHelper::check($checkboxId, true, array('id'=> $checkboxId, 'onclick' => GISHelpers::TagLayerHandler($id, $tagId, $checkboxId))).'<label for="'.$checkboxId.'">'.$tag->getName().'</label></li>';	
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
		
		$saveMethod = '
		<script type="text/javascript">
		function saveVisualizationState_'.$mapId.'(){
						
			 $.ajax({
			      url: "/yuppgis/prototipo/Home/saveVisualization",
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
			"app" => "prototipo", 
			"controller" =>"Home", 
			"action"=>"loadVisualization",		 	
			"mapId" => $mapId,			 
			"body" => "Restaurar",
			"before" => "log('beforeSend')",
			"after" => "log('success')"
			
			);
		
		$html .= Helpers::ajax_link($load_params);
		
		return $html;
	}
	
}


?>