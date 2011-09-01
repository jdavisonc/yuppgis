<?php

YuppLoader::load('yuppgis.core.utils', 'ReflectionUtils');

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
			function setHTML(response) {
				document.getElementById("nodeList").innerHTML = response.responseText;
			}
			
			function init(){
				
				 $.ajax({
			      url:"/yuppgis/prototipo/Home/getLayersAction" ,			      
			      success: function(data){
				
			
			
 				var google = new OpenLayers.Layer.Google( "Google", { type: G_HYBRID_MAP } );
		
				var options = {
					minResolution: "auto",
					minExtent: new OpenLayers.Bounds(-1, -1, 1, 1),
					maxResolution: "auto",
					maxExtent: new OpenLayers.Bounds(-180, -90, 180, 90),
				};
				var map = new OpenLayers.Map("'.$id.'", options );

 			 	map.addLayer(google);
				map.zoomToMaxExtent();				
				
								
                var wms = new OpenLayers.Layer.WMS( "OpenLayers WMS","http://labs.metacarta.com/wms/vmap0?", {layers: "basic"});

			    $.each(data, function(i, item){
                	var layerurl =  "/yuppgis/prototipo/Home/mapLayer?layerId=" + item.id;
		 			var kml = new OpenLayers.Layer.Vector("KML", {
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
		             map.addLayers([wms, kml]);
	                
                });
                
              
                
               	map.setCenter(new OpenLayers.LonLat(-56.181944, -34.883611), 15);
                 
				}
			   });
			}

		</script>
	
		<style type="text/css">
			#'.$id.' {
				width: '.$width.';
				height: '.$height.';
				border: '.$border.';
				}
		</style>
		
		<div id="'.$id.'"></div>
		<div id="nodeList"></div>
	
	
		<body onload="init()">        	
        
    	</body> ';

		return  $html;

	}
}


?>