<?php

YuppLoader::load('yuppgis.core.gis', 'KMLUtilities');

class RestWSGISDAL implements GISWSDAL {
	
	private $url = null;
	
	function __construct( $appName) {
		$url = YuppGISConfig::getGISPropertyValue($appName, YuppGISConfig::PROP_BASIC_URL);
		if ($url == null || $url == '') {
			throw new Exception('URL para YuppGIS Basico no especificada');
		}
	}
	
	public function get( $ownerTableName, $attr, $persistentClass, $id ) {
		
		$request = new  HTTPRequest();
		
		$param = $this->url;
		$param .= '&OP=GET';
		$param .= '&OWNER='.$ownerTableName; //TODO_GIS: en realidad nombre de la clase
		$param .= '&ATTRIBUTE='.$attr;
		$param .= '&CLASS='.$persistentClass; //TODO_GIS: apartir de los anteriores podemos generar el get
		$param .= '&ID='.$id;
		
		$response = $request->HttpRequestGet($param);
		header('Content-Type: text/xml');
		$layer = KMLUtilities::KmlToLayer($response->getBody());
	}
	
	public function save($ownerTableName, $attrNameAssoc, $kml) {
		$request = new  HTTPRequest();
		
		$param = $this->url;
		$param .= '&OP=SAVE';
		$param .= '&OWNER='.$ownerTableName; //TODO_GIS: en realidad nombre de la clase
		$param .= '&ATTRIBUTE='.$attrNameAssoc;
		$param .= '&KML='.$kml;
		
		$response = $request->HttpRequestGet($param);
		header('Content-Type: text/xml');
		if ($response->getStatus() == '200') {
			return $response->getBody(); // Trae en el body el ID del elemento
		}
	}
	
	public function delete($ownerTableName, $attrNameAssoc, $id, $logical) {
		$request = new  HTTPRequest();
		
		$param = $this->url;
		$param .= '&OP=DELETE';
		$param .= '&OWNER='.$ownerTableName; //TODO_GIS: en realidad nombre de la clase
		$param .= '&ATTRIBUTE='.$attrNameAssoc;
		$param .= '&ID='.$id;
		$param .= '&LOGICAL='.$logical;
		
		$response = $request->HttpRequestGet($param);
		header('Content-Type: text/xml');
		return  $response->getStatus() == '200';
	}
	
}

?>