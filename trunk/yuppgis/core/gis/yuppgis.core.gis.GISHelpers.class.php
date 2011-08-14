<?php
class GISHelpers{


	/*Menu*/
	private static function ReflectMethods($class, $token, $issuffix=true){
		if (!class_exists($class)){
			throw new Exception("La clase $class no existe o no está cargada");
		}else{
			/*Recorro la clase y busco los metodos que terminan en filter*/
			$methods = get_class_methods($class);
			$actions =  array();
			foreach ($methods as $method){
				if (String::endsWith($method, $token) && $issuffix){
					array_push($actions, $method);
				}else{
					if (String::startsWith($method, $token) && !$issuffix){
						array_push($actions, $method);
					}
				}
			}
			return $actions;
		}
	}

	
	/**
	 * Obtiene las acciones declaradas en la clase
	 * @param nombre de la clase
	 * @return array con nombre de las acciones
	 */
	public static function AvailableActions($class){

		return self::ReflectMethods($class, 'Action', true);
	}

	
	/**
	 * Obtiene los filtros declarados en la clase
	 * @param nombre de la clase
	 * @return array con nombre de los filtros
	 */
	public static function AvailableFilters($class){

		return self::ReflectMethods($class, 'Filter', true);
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
		$url = MapParams::getValueOrDefault($params, MapParams::URL);
		$width = MapParams::getValueOrDefault($params, MapParams::WIDTH);
		$height = MapParams::getValueOrDefault($params, MapParams::HEIGHT);
		$border = MapParams::getValueOrDefault($params, MapParams::BORDER);
		
		$pin_map = "/yuppgis/yuppgis/js/gis/img/marker-blue.png";
		
		GISLayoutManager::getInstance()->addGISJSLibReference( array("name" => "gis/OpenLayers"));

		$html =	'
		<script src="'.$url.'" type="text/javascript"></script>			
		<script type="text/javascript">
			function setHTML(response) {
				document.getElementById("nodeList").innerHTML = response.responseText;
			}
			
			function init(){
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
                var styleMap = new OpenLayers.StyleMap({pointRadius: 10, externalGraphic: "'.$pin_map.'"});                         
                vectorLayer = new OpenLayers.Layer.Vector("Points", {styleMap: styleMap});
 
                map.addLayers([wms, vectorLayer]);
                map.addControl(new OpenLayers.Control.LayerSwitcher());
                map.addControl(new OpenLayers.Control.MousePosition());
 
                drawPoint=new OpenLayers.Control.DrawFeature(vectorLayer,OpenLayers.Handler.Point);
				drawPoint.featureAdded = featAdded;
				map.addControl(drawPoint);
				
                map.setCenter(new OpenLayers.LonLat(0, 0), 3);
				
			}

			function featAdded() {
				var el = document.getElementById("text");
				el.value=drawPoint.handler.point.geometry.x+", "+drawPoint.handler.point.geometry.y;
            }
            function dibujar() {
            	drawPoint.activate();
            }
            function parar() {
				drawPoint.deactivate();
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
        	<input type="button" value="Dibujar" onclick="dibujar()"/> 
        	<input type="button" value="Parar" onclick="parar()"/> 
        	Último punto: <input type="text" value="no set" id="text" width="30"/> 
    	</body> ';

		return  $html;

	}
}


?>