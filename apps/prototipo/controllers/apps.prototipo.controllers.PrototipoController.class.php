<?php

class PrototipoController extends YuppController {

	public function indexAction()
	{
      return $this->renderString("Bienvenido a su nueva aplicacion!");
	}
	
	public function mapAction(){
		return;
	}
}

?>