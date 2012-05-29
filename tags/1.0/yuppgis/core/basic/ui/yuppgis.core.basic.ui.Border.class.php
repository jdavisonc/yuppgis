<?php

/**
 * Clase que representa un {@link UIProperty} borde. 
 * 
 * @package yuppgis.core.basic.ui
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class Border extends UIProperty {
	
	protected $color;
	protected $width;
	
	/**
	 * Constructor.
	 * 
	 * @param int $alpha alpha a asignar al fondo.
	 * @param int $zIndex indice Z.
	 * @param Color $color color a asignar, por defecto negro.
	 * @param int $width ancho del borde.
	 */
	public function __construct($alpha = 0, $zIndex = 0, $color = Color::BLACK, $width = 0) {
		$this->color = $color;
		$this->width = $width;
		parent::__construct($alpha, $zIndex);
	}
	
	/**
	 * Retorna el color asignado.
	 */
	public function getColor() {
		return $this->color;
	}
	
	/**
	 * Asigna un color.
	 * @param Color $color color a asignar.
	 */
	public function setColor($color) {
		$this->color = $color;
	}
	
	/**
	 * Retorna el ancho asignado.
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * Asigna un ancho al borde.
	 * @param int $width ancho a asignar.
	 */
	public function setWidth($width) {
		$this->width = $width;
	}
	
}

?>