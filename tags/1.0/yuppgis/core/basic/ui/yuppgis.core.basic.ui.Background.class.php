<?php

/**
 * Clase que representa un {@link UIProperty} relleno.
 * 
 * @package yuppgis.core.basic.ui
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class Background extends UIProperty {
	
	protected $color;
	
	/**
	 * Constructor.
	 * 
	 * @param int $alpha alpha a asignar al fondo.
	 * @param int $zIndex indice Z.
	 * @param Color $color color a asignar, por defecto negro.
	 */
	public function __construct($alpha = 0, $zIndex = 0, $color = Color::BLACK) {
		$this->color = $color;
		parent::__construct($alpha, $zIndex);
	}
	
	/**
	 * Retorna el color asignado.
	 */
	public function getColor() {
		return $this->color;
	}
	
	/**
	 * Asignacion de color.
	 * @param Color $color color a asignar.
	 */
	public function setColor($color) {
		$this->color = $color;
	}
}

?>