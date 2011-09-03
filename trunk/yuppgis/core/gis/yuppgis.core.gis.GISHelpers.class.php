<?php

YuppLoader::load('yuppgis.core.utils', 'ReflectionUtils');
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
		$kmlurl = MapParams::getValueOrDefault($params, MapParams::KML_URL);


		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/OpenLayers"));

		$html =	'
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script> 
		<script src="'.$olurl.'" type="text/javascript"></script>			
		<script type="text/javascript">
			
		var selectcontrol_'.$id.', selectedFeature_'.$id.', map_'.$id.'; 
		$(document).ready(function(){
			
				
				 $.ajax({
			      url:"/yuppgis/prototipo/Home/getLayersAction?mapId='.$id.'",			      			      			      
			      success: function(data){
				
			
					
		 				var google = new OpenLayers.Layer.Google( "Google", { type: G_HYBRID_MAP } );
				
						var options = {
							minResolution: "auto",
							minExtent: new OpenLayers.Bounds(-1, -1, 1, 1),
							maxResolution: "auto",
							maxExtent: new OpenLayers.Bounds(-180, -90, 180, 90),
						};
						map_'.$id.' = new OpenLayers.Map("map_'.$id.'", options );
		
		 			 	map_'.$id.'.addLayer(google);
						map_'.$id.'.zoomToMaxExtent();				
						
										
		                var wms = new OpenLayers.Layer.WMS( "OpenLayers WMS","http://labs.metacarta.com/wms/vmap0?", {layers: "basic"});
						map_'.$id.'.addLayer(wms);
					    $.each(data, function(i, item){
		                	var layerurl =  "/yuppgis/prototipo/Home/mapLayer?layerId=" + item.id;
				 			var kml = new OpenLayers.Layer.Vector(item.id, {
									            strategies: [new OpenLayers.Strategy.Fixed()],
									            protocol: new OpenLayers.Protocol.HTTP({
									                url: layerurl,					                
									                format: new OpenLayers.Format.KML({
									                    extractStyles: true, 
									                    extractAttributes: true,
									                    maxDepth: 2
									                })
									            })
									        });					        
				             map_'.$id.'.addLayer(kml);
				             console.log(\'Capa \' + i);
				    	    selectcontrol_'.$id.' = new OpenLayers.Control.SelectFeature(map_'.$id.'.layers[i + 2], {				                
				    	    	onSelect: onFeatureSelect_'.$id.', 
				                onUnselect: onFeatureUnselect_'.$id.' 
							});
				  
				            map_'.$id.'.addControl(selectcontrol_'.$id.');
				            selectcontrol_'.$id.'.activate();  
						});  
						                
		               	map_'.$id.'.setCenter(new OpenLayers.LonLat(-56.181944, -34.883611), 15);
		                 
					}
				});
			
			
			});
			
		function onPopupClose_'.$id.'(evt) {
            selectControl_'.$id.'.unselect(selectedFeature_'.$id.');
        }
        
        function onFeatureSelect_'.$id.'(feature) {
            selectedFeature_'.$id.' = feature;
            popup_'.$id.' = new OpenLayers.Popup.FramedCloud("chicken", 
                                     feature.geometry.getBounds().getCenterLonLat(),
                                     new OpenLayers.Size(100,100),
                                     "<div style=\'font-size:.8em\'>"+feature.attributes.name+"</div>",
                                     null, true, onPopupClose_'.$id.');
            feature.popup = popup_'.$id.';
            map_'.$id.'.addPopup(popup_'.$id.');
        }
        
        function onFeatureUnselect_'.$id.'(feature) {
            map_'.$id.'.removePopup(feature.popup);
            feature.popup.destroy();
            feature.popup = null;
        }
			

		</script>
	
		<style type="text/css">
			#map_'.$id.' {
				width: '.$width.';
				height: '.$height.';
				border: '.$border.';
				}
		</style>
		
		<div id="map_'.$id.'"></div>';
			


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

}


?>