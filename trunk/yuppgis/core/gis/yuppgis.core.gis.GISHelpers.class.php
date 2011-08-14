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

	public static function AvailableActions($class){

		return self::ReflectMethods($class, 'Action', true);
	}

	public static function AvailableFilters($class){

		return self::ReflectMethods($class, 'Filter', true);
	}



	/*Mapa*/

	public static function Map($params=null){

		$id = MapParams::getValueOrDefault($params, MapParams::ID);
		$url = MapParams::getValueOrDefault($params, MapParams::URL);
		$width = MapParams::getValueOrDefault($params, MapParams::WIDTH);
		$height = MapParams::getValueOrDefault($params, MapParams::HEIGHT);
		$border = MapParams::getValueOrDefault($params, MapParams::BORDER);

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
	
	<script>init();</script>
		';

		return  $html;

	}
}


?>