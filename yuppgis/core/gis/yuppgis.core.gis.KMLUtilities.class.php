<?php

YuppLoader::load('yuppgis.core.persistent.serialize', 'KMLGEO');

class KMLUtilities{

	public static function layerToKML(DataLayer $layer) {
		$kml = new SimpleXMLElement('<kml/>');
		$kml->addAttribute('xmlns', 'http://earth.google.com/kml/2.0');
		$doc = $kml->addChild('Document');
		$doc->addChild('name', 'YuppGIS KML'); //TODO_GIS
		$doc->addChild('open', 1);
		$doc->addChild('description', 'Description Here!'); //TODO_GIS
		$folder = $doc->addChild('Folder');
		$folder->addAttribute('ID', $layer->getId());
		$folder->addChild('name', $layer->getName());
		$folder->addChild('visibility', ($layer->getVisible()) ? 1 : 0);
		$folder->addChild('description', 'Description Here!'); //TODO_GIS
		
		foreach ($layer->getElements() as $element){
			KMLUtilities::elementToKML($element, $layer, $folder);
		}
		return $kml->asXML();
	}

	/**
	 * TODO_GIS: Hacer dinamico
	 * 
	 * @param unknown_type $element
	 * @param unknown_type $layer
	 * @param SimpleXMLElement $folder
	 */
	private static function elementToKML($element, DataLayer $layer, SimpleXMLElement &$folder){
		
		foreach ($layer->getAttributes() as $attribute) {
			$description = 'Capa: '.$layer->getName().', Id: '.$element->getId();
			$getAttribute = 'get' . $attribute;
			$geo = $element->$getAttribute();
			if ($geo) {
				KMLGEO::toKML($element->getId(), $geo, $description, get_class($element), 
						$layer->getId(), $layer->getDefaultUIProperty() ,$folder);
			}
		}
	}
	
	public static function GeometryToKML($id, Geometry $geom) {
		$kml = new SimpleXMLElement('<kml/>');
		$kml->addAttribute('xmlns', 'http://earth.google.com/kml/2.0');
		$doc = $kml->addChild('Document');
		$doc->addChild('name', 'YuppGIS KML'); //TODO_GIS
		$doc->addChild('open', 1);
		$doc->addChild('description', 'Description Here!'); //TODO_GIS
		
		KMLGEO::toKML($id, $geom, null, null, null, null, $doc);
				
		return $kml->asXML();
	}
	
	/**
	 * Funcion que parsea un KML y retorna una lista de geometrias.
	 * 
	 * @param string $kml
	 * @return array de geometrias
	 */
	public static function KMLToGeometry($kml) {
		$kmlElement = new SimpleXMLElement($kml);
		$doc = $kmlElement->Document;
		$elements = array();
		if ($doc->Folder != null) {
			$placemarks = $doc->Folder->Placemark;
			if ($placemarks) {
				foreach ($placemarks as $elem) {
					$elements[] = KMLGEO::fromKML($elem);
				}
			}
		} 
		if ($doc->Placemark) {
			foreach ($doc->Placemark as $elem) {
				$elements[] = KMLGEO::fromKML($elem);
			}
		}
		return $elements;
	}

}

?>