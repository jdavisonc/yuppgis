<?php

YuppLoader::load('yuppgis.core.basic.ui', 'UIProperty');
YuppLoader::load('yuppgis.core.persistent.serialize', 'KMLGEO');

class KMLUtilities{

	public static function LayerToKml(DataLayer $layer) {
		$kml = new SimpleXMLElement('<kml/>');
		$kml->addAttribute('xmlns', 'http://earth.google.com/kml/2.0');
		$doc = $kml->addChild('Document');
		$doc->addChild('name', 'YuppGIS KML'); //TODO_GIS
		$doc->addChild('open', 1);
		$doc->addChild('description', 'Description Here!'); //TODO_GIS
		$folder = $doc->addChild('Folder');
		$folder->addAttribute('ID', $layer->getId());
		$folder->addChild('name', $layer->getName());
		$folder->addChild('visibility', 0);
		$folder->addChild('description', 'Description Here!'); //TODO_GIS
		
		foreach ($layer->getElements() as $element){
			KMLUtilities::ElementToKml($element, $layer, $folder);
		}
		return $kml->asXML();
	}

	private static function ElementToKml($element, $layer, SimpleXMLElement &$folder){
		if ($element->hasAttribute('ubicacion') && $element->getUbicacion() != null){
			KMLGEO::toKML($element->getUbicacion(), $layer, $folder);
		}

		if ($element->hasAttribute('linea') && $element->getLinea() != null){
			KMLGEO::toKML($element->getLinea(), $layer, $folder);
		}

		if ($element->hasAttribute('zonas') && $element->getZonas() != null){
			KMLGEO::toKML($element->getZonas(), $layer, $folder);
		}
	}

}

?>