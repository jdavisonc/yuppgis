<?php
class Icon extends UIProperty {	
	
	protected $url;
	protected $width;
	protected $height;
	
	public function __construct($alpha = 0, $zIndex = 0, $url = '', $width = 0, $height = 0) {
		$this->url = $url;
		$this->width = $width;
		$this->height = $height;
		parent::__construct($alpha, $zIndex);
	}
	
	public function getUrl() {
		return $this->url;
	}
	
	public function setUrl($url) {	
		global $_base_dir;	
		$this->url = $_base_dir. '/apps/'.YuppContext::getInstance()->getApp().$url;
	}
	
	public function getWidth() {
		return $this->width;
	}
	
	public function setWidth($width) {
		$this->width = $width;
	}
	
	public function getHeight() {
		return $this->height;
	}
	
	public function setHeight($height) {
		$this->height = $height;
	}
	
}

?>