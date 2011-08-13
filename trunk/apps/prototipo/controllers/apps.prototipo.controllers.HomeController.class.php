<?php

class HomeController extends YuppController {

	public function indexAction()
	{
      return $this->redirect(array ("action" => "map"));
	}
	
	public function mapAction(){
		return;
	}
}

?>