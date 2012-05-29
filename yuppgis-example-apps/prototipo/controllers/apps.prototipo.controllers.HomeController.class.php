<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('prototipo.model', 'PPaciente');
YuppLoader::load('prototipo.model', 'MMedico');

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