<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('prototipo.model', 'Paciente');

class HomeController extends GISController {

	public function indexAction()
	{
		return $this->redirect(array ("action" => "map"));
	}

	public function mapAction(){
		return;
	}

	

}

?>