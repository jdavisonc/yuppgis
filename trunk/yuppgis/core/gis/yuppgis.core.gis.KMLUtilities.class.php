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
	private static function elementToKML($element, $layer, SimpleXMLElement &$folder){
		if ($element->hasAttribute('ubicacion') && $element->getUbicacion() != null){
			$description = 'Capa: '.$layer->getName().', Id: '.$element->getId();
			KMLGEO::toKML($element->getId(), $element->getUbicacion(), $description, get_class($element), 
					$layer->getId(), new Icon(0,0,$layer->getIconurl()) ,$folder);
		}

		if ($element->hasAttribute('linea') && $element->getLinea() != null){
			$description = 'Capa: '.$layer->getName().', Id: '.$element->getId();
			KMLGEO::toKML($element->getId(), $element->getLinea(), $description, get_class($element), 
					$layer->getId(), new Icon(0,0,$layer->getIconurl()) ,$folder);
		}

		if ($element->hasAttribute('zonas') && $element->getZonas() != null){
			$description = 'Capa: '.$layer->getName().', Id: '.$element->getId();
			KMLGEO::toKML($element->getId(), $element->getZonas(), $description, get_class($element), 
					$layer->getId(), new Icon(0,0,$layer->getIconurl()) ,$folder);
		}
	}
	
	public static function KMLToLayer($kml) {
		$kmlElement = new SimpleXMLElement($kml);
		$doc = $kmlElement->Document;
		if ($doc->Folder != null) {
			$folder = $doc->Folder;

			$layer = new DataLayer();
			$layer->setName(strval($folder->name));
			$layer->setId(strval($folder['ID']));
			$layer->setVisible(strval($folder->visibility));

			$placemarks = $folder->Placemark;
			if ($placemarks) {
				foreach ($placemarks as $elem) {
					$layer->addElement(KMLGEO::fromKML($elem));
				}
			}
			return $layer;
		}
		return null;
	}

}

?>