<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');

class PremiumHomeController extends GISController {

	public function indexAction()
	{
		return $this->redirect(array ("action" => "map"));
	}

	public function mapAction(){
		return;
	}
	
}

?>