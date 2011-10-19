<?php

YuppLoader::load('yuppgis.core.gis', 'KMLUtilities');

class RestWSGISDAL implements GISWSDAL {
	
	private $getUrl = null;
	private $saveUrl = null;
	private $deleteUrl = null;
	
	function __construct( $appName) {
		$url = YuppGISConfig::getGISPropertyValue($appName, YuppGISConfig::PROP_BASIC_URL);
		$getUrl = YuppGISConfig::getGISPropertyValue($appName, YuppGISConfig::PROP_BASIC_GET_URL);
		$saveUrl = YuppGISConfig::getGISPropertyValue($appName, YuppGISConfig::PROP_BASIC_SAVE_URL);
		$deleteUrl = YuppGISConfig::getGISPropertyValue($appName, YuppGISConfig::PROP_BASIC_DELETE_URL);
		
		$this->getUrl = ($getUrl) ? $getUrl : $url;
		$this->saveUrl = ($saveUrl) ? $saveUrl : $url;
		$this->deleteUrl = ($deleteUrl) ? $deleteUrl : $url;
		
		if (!$this->getUrl || !$this->saveUrl || !$this->deleteUrl) {
			throw new Exception('URLs para YuppGIS Basico no especificadas');
		}
	}
	
	public function get($ownerName, $attr, $persistentClass, $id) {
		$request = new  HTTPRequest();
		
		$vars = array ('id' => $id, 'ownerName' => $ownerName, 'attr' => $attr, 
						'class' => $persistentClass, 'op' => 'get');
		$url = $this->replaceWithVarsValue($this->url, $vars);
		
		$response = $request->HttpRequestGet($url);
		header('Content-Type: text/xml');
		$layer = KMLUtilities::KMLToLayer($response->getBody());
		if ($layer != null && !$layer->getElements()) {
			$elements = $layer->getElements(); 
			return $elements[0]; // Solo el primer resultado retorno
		}
	}
	
	public function save($ownerName, $attr, PersistentObject $obj) {
		$request = new  HTTPRequest();
		
		$vars = array ('id' => $id, 'ownerName' => $ownerName, 'attr' => $attr, 
						'op' => 'save');
		$url = $this->replaceWithVarsValue($this->url, $vars);
		
		$response = $request->HttpRequestPost($url, array('kml' => $kml));
		header('Content-Type: text/xml');
		if ($response->getStatus() == '200') {
			$id = $response->getBody(); // Trae en el body el ID del elemento
			if (is_int($id)) {
				return $id;
			} else {
				throw new Exception("Save operation failed. " . $id);
			}
		}
	}
	
	public function delete($ownerName, $attr, $id, $logical) {
		$request = new  HTTPRequest();
		
		$vars = array ('id' => $id, 'ownerName' => $ownerName, 'attr' => $attr, 
						'logical' => $logical, 'op' => 'delete');
		$url = $this->replaceWithVarsValue($this->url, $vars);
		
		$response = $request->HttpRequestGet($url);
		header('Content-Type: text/xml');
		return  $response->getStatus() == '200';
	}
	
	private function replaceWithVarsValue($url, array $vars) {
		$newUrl = $url;
		foreach ($vars as $key => $value) {
			$newUrl = str_replace('{'.$key.'}', $value, $newUrl);
		}
		return $newUrl;
	}
	
}

?>