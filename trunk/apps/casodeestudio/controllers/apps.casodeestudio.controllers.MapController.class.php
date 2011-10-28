<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('casodeestudio.model', 'Paciente');

class MapController extends GISController {

	public function indexAction()
	{
		return $this->redirect(array ("action" => "map"));
	}

	public function mapAction(){
		return;
	}
	

}

?>