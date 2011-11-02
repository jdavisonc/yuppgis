<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('casodeestudio.model', 'Medico');

class MedicoController extends GISController {

	
	public function addAction() {
	}
	
	public function listAction() {
		$this->params['list']  = Medico::listAll($this->params);
		return ;
	}
	
	public function infoAction() {
	}
	
}

?>