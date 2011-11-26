<?php

/**
 * Clase que representa un {@link UIProperty} icono.
 * 
 * @package yuppgis.core.basic.ui
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class Icon extends UIProperty {
	
	const DEFAULT_ICON = '/yuppgis/yuppgis/js/gis/img/marker-gold.png';
	
	protected $url;
	protected $width;
	protected $height;
	
	public function __construct($alpha = 0, $zIndex = 0, $url = '', $width = 0, $height = 0) {
		if ($url) {
			$this->url = $this->getRealPath($url);
		} else {
			$this->url = self::DEFAULT_ICON;
		}
		$this->width = $width;
		$this->height = $height;
		parent::__construct($alpha, $zIndex);
	}
	
	public function getUrl() {
		return $this->url;
	}
	
	public function setUrl($url) {	
		$this->url = $this->getRealPath($url);
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
	
	private function getRealPath($url) {
		global $_base_dir;	
		return $_base_dir . '/apps/' . YuppContext::getInstance()->getApp() . $url;
	}
	
}

?>