<?php

class KMLGEO {
	
	public function fromKML($kml) {
		$kmlElement = new SimpleXMLElement($kml);

		foreach ($kmlElement->xpath('//Placemark') as $placemark) {
			$arr = $node->attributes();
			$id = $arr['ID']; // Ver de usar atributo ID para guardar el ID
			$style = $placemak->Style; // Estilo
			if ($placemark->Point != null) {
				
			} else if ($placemark->MultiPoint != null) {
				
			}
			
		}
	}
	
}

?>