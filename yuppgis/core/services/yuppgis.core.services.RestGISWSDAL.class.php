<?php

YuppLoader::load('yuppgis.core.gis', 'KMLUtilities');

class RestGISWSDAL implements GISWSDAL {
	
	private $getUrl = null;
	private $saveUrl = null;
	private $deleteUrl = null;
	
	function __construct( $appName) {
		$url = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_BASIC_URL);
		$getUrl = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_BASIC_GET_URL);
		$saveUrl = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_BASIC_SAVE_URL);
		$deleteUrl = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_BASIC_DELETE_URL);
		
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
		$url = $this->replaceWithVarsValue($this->getUrl, $vars);
		
		$response = $request->HttpRequestGet($url);
		$elements = KMLUtilities::KMLToGeometry($response->getBody());
		if ($elements) { 
			return $this->elementSelector($ownerName, $attr, $persistentClass, $id, $elements); // Solo el primer resultado retorno
		}
	}
	
	/**
	 * Funcion que segun los parametros de busqueda y el resultado ya deserealizado, elige el objeto a retornar.
	 * @param unknown_type $ownerName
	 * @param unknown_type $attr
	 * @param unknown_type $persistentClass
	 * @param unknown_type $id
	 * @param array $elements
	 */
	protected function elementSelector($ownerName, $attr, $persistentClass, $id, array $elements) {
		return $elements[0];
	}
	
	public function save($ownerName, $attr, PersistentObject $obj) {
		$kml = KMLUtilities::GeometryToKML(0, $obj);
		$vars = array ('ownerName' => $ownerName, 'attr' => $attr, 'op' => 'save');
		$url = $this->replaceWithVarsValue($this->saveUrl, $vars);
		
		$request = new HTTPRequest();
		$response = $request->HttpRequestPost($url, array('kml' => $kml));
		if ($response->getStatus() == '200') {
			$id = $response->getBody(); // Trae en el body el ID del elemento
			if (is_numeric($id)) {
				return $id;
			} else {
				throw new Exception("Save operation failed. " . $id);
			}
		} else {
			throw new Exception("Save operation failed. Service response was " . $response->getStatus(), $response->getStatus());
		}
	}
	
	public function delete($ownerName, $attr, $id, $logical) {
		$request = new  HTTPRequest();
		
		$vars = array ('id' => $id, 'ownerName' => $ownerName, 'attr' => $attr, 
						'logical' => $logical, 'op' => 'delete');
		$url = $this->replaceWithVarsValue($this->deleteUrl, $vars);
		
		$response = $request->HttpRequestGet($url);
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