<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('basico.model', 'Hospital');

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