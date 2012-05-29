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
	
	/**
	 * Icono por defecto.
	 */
	const DEFAULT_ICON = '/yuppgis/yuppgis/js/gis/img/marker-gold.png';
	
	protected $url;
	protected $width;
	protected $height;
	
	/**
	 * Constructor.
	 * 
	 * @param int $alpha alpha a asignar al fondo.
	 * @param int $zIndex indice Z.
	 * @param string $url url del icono.
	 * @param int $width ancho del icono.
	 * @param int $height alto del icono.
	 */
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
	
	/**
	 * Retorna la URL del icono.
	 */
	public function getUrl() {
		return $this->url;
	}
	
	/**
	 * Asigna una URL de icono.
	 * @param string $url url del icono.
	 */
	public function setUrl($url) {	
		$this->url = $this->getRealPath($url);
	}
	
	/**
	 * Retorna el ancho del icono.
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * Asigna un ancho al icono
	 * @param int $width ancho del icono.
	 */
	public function setWidth($width) {
		$this->width = $width;
	}
	
	/**
	 * Retorna el alto del icono.
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * Asigna un alto al icono
	 * @param int $width alto del icono.
	 */
	public function setHeight($height) {
		$this->height = $height;
	}
	
	private function getRealPath($url) {
		global $_base_dir;	
		return $_base_dir . '/apps/' . YuppContext::getInstance()->getApp() . $url;
	}
	
}

?>