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
	public static function FiltersMenu($class, $mapid, $handler){
		$html = '<select id="select_'.$class.'_'.$mapid.'">';
		$html .= '<option value="nothing"></option>';

		foreach (self::AvailableFilters($class) as $option){
			$html .= '<option value="'.$option.'">'.str_ireplace('Filter', '', $option).'</option>';
		}

		$html .= '</select>';
		$html .= '<input type="text" id="tbFiltersMenu_'.$class.'_'.$mapid.'" />';
		
		$methodName = 'filter_'.$class.'_'.$mapid;
		$html .= '<a href="#" id="btnFiltersMenu_'.$class.'_'.$mapid.'" onclick="javascript:return '.$methodName.'()">Filtrar</a>';

		$script = '<script>
						function '.$methodName.'(){
							var selectedOption = $("#select_'.$class.'_'.$mapid.'").val();
							var text = $("#tbFiltersMenu_'.$class.'_'.$mapid.'").val();
							
							 $.ajax({
							      url: "/yuppgis/prototipo/Home/Filter",
							      data: {
							        filterName: selectedOption,
							        className: "'.$class.'",
							        mapId: '.$mapid.',
							      	param: text
							      },			      			      			      
							      success: function(data){
							      	'.$handler.'(data);
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

		$id = MapParams::getValueOrDefault($params, MapParams::ID);
		$olurl = MapParams::getValueOrDefault($params, MapParams::OpenLayerJS_URL);
		$width = MapParams::getValueOrDefault($params, MapParams::WIDTH);
		$height = MapParams::getValueOrDefault($params, MapParams::HEIGHT);
		$border = MapParams::getValueOrDefault($params, MapParams::BORDER);	
		
		$clickhandlers = MapParams::getValueOrDefault($params, MapParams::CLICK_HANDLERS);
		$selecthandlers = MapParams::getValueOrDefault($params, MapParams::SELECT_HANDLERS);

		LayoutManager::getInstance()->addJSLibReference( array("name" => "jquery/jquery-1.6.1.min"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/OpenLayers"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/common"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/jquery.yuppgis.map"));

		$html =	'	
		 
		<script src="'.$olurl.'" type="text/javascript"></script>			
		<script type="text/javascript">
		
		</script>
	
		<style type="text/css">
			#map_'.$id.' {
				width: '.$width.';
				height: '.$height.';
				border: '.$border.';
				}
		</style>
		
		<div id="map_'.$id.'"></div>
		
		
		<script type="text/javascript">
		
			$("#map_'.$id.'").YuppGISMap({id: '.$id.'})
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
			$html .= '<li style="list-style-type: none">'.DisplayHelper::check($checkboxId, true, array('id'=> $checkboxId, 'onclick' => GISHelpers::MapLayerHandler($id, $layerId, $checkboxId))).'<label for="'.$checkboxId.'">'.$layer->getName().'</label></li>';
		}
		
		return $html.'</ul>';
	}
	
	private static function MapLayerHandler($mapId, $layerId, $checkboxId){
		
		$html = 'javascript:map_'.$mapId.'.getLayersByName('.$layerId.')[0].setVisibility($(\'#'.$checkboxId.'\').is(\':checked\'))';
		
		return $html;
	}
	
	public static function Log($mapId){
		$html = '<div class="logarea" style="width:550px!important;height:220px; overflow:scroll!important;" id="log_'.$mapId.'"></div>';
		
		return $html;
	}

}


?>