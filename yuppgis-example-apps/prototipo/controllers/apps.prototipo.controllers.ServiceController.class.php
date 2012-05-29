<?php

class ServiceController extends YuppController {
	
	public function saveElementAction(){
		$layerId = 1;
		return $this->renderString($layerId);
	}
	
	public function deleteElementAction(){
		$layerId = 1;
		return $this->renderString($layerId);
	}
	
	public function getElementAction(){
		$layerId = 1;
		return $this->renderString(KMLUtilities::GeometryToKML(1, new Point(10, 10)));
	}
	
}

?>