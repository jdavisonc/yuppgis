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
		$kmlurl = MapParams::getValueOrDefault($params, MapParams::KML_URL);
		
		$clickhandler = MapParams::getValueOrDefault($params, MapParams::CLICK_HANDLER);
		$doubleclickhandler  = MapParams::getValueOrDefault($params, MapParams::DOUBLECLICK_HANDLER);
		

		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/OpenLayers"));
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/common"));

		$html =	'
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script> 
		<script src="'.$olurl.'" type="text/javascript"></script>			
		<script type="text/javascript">
		
		 OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {                
                defaultHandlerOptions: {
                    "single": true,
                    "double": true,
                    "pixelTolerance": 0,
                    "stopSingle": true,
                    "stopDouble": true
                },

                initialize: function(options) {
                    this.handlerOptions = OpenLayers.Util.extend(
                        {}, this.defaultHandlerOptions
                    );
                    OpenLayers.Control.prototype.initialize.apply(
                        this, arguments
                    ); 
                    this.handler = new OpenLayers.Handler.Click(
                        this, {
                            "click": '.$clickhandler.',
                            "doubleclick": '.$doubleclickhandler.'
                        }, this.handlerOptions
                    );
                }, 

               

        });
        	
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

						var click = new OpenLayers.Control.Click();
                		map_'.$id.'.addControl(click);
                		click.activate();
						
                		                
						var report = function(e) {
			                OpenLayers.Console.log(e.type, e.feature.id);
			            };
										
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
				             
				    	    selectcontrol_'.$id.' = new OpenLayers.Control.SelectFeature(kml, {				                
				    	    	onSelect: onFeatureSelect_'.$id.', 
				                onUnselect: onFeatureUnselect_'.$id.' 
							});
							
							var highlightCtrl = new OpenLayers.Control.SelectFeature(kml, {
				                hover: true,
				                highlightOnly: true,
				                renderIntent: "temporary",
				                eventListeners: {
				                    beforefeaturehighlighted: report,
				                    featurehighlighted: report,
				                    featureunhighlighted: report
				                }
				            });
				
				            var selectCtrl = new OpenLayers.Control.SelectFeature(kml, {
				            	clickout: true
							});
				
				            map_'.$id.'.addControl(highlightCtrl);
				            map_'.$id.'.addControl(selectCtrl);
				
				            highlightCtrl.activate();
				            selectCtrl.activate();
				  
				            map_'.$id.'.addControl(selectcontrol_'.$id.');
				            selectcontrol_'.$id.'.activate();

				            /*map_'.$id.'.addControl(new OpenLayers.Control.EditingToolbar(kml));*/
						});  
									
			            
	    	           	map_'.$id.'.setCenter(new OpenLayers.LonLat(-56.181944, -34.883611), 15);
		                 
					}
				});
			
			
			});
			
		function onPopupClose_'.$id.'(evt) {
            selectcontrol_'.$id.'.unselect(selectedFeature_'.$id.');
        }
        
        function onFeatureSelect_'.$id.'(feature) {
        	
        	 $.ajax({
			      url:"/yuppgis/prototipo/home/details",
			      data: {
			      	layerId: feature.attributes.layerId,
			      	className:feature.attributes.className,
			      	elementId:feature.attributes.elementId
			      },			      			      			      
			      success: function(data){
				    var html = data;
				    if (data == \'\'){
				    	html = feature.attributes.description;
				    };
		            selectedFeature_'.$id.' = feature;
		            popup_'.$id.' = new OpenLayers.Popup.FramedCloud("chicken", 
		                                     feature.geometry.getBounds().getCenterLonLat(),
		                                     new OpenLayers.Size(100,100),
		                                     html,
		                                     null, true, onPopupClose_'.$id.');
		            feature.popup = popup_'.$id.';
		            map_'.$id.'.addPopup(popup_'.$id.');
		          }
			});
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