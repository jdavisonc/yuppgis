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
	
	public function __construct($alpha = 0, $zIndex = 0, $color = Color::BLACK) {
		$this->color = $color;
		parent::__construct($alpha, $zIndex);
	}
	
	public function getColor() {
		return $this->color;
	}
			
	public function setColor($color) {
		$this->color = $color;
	}
}

?>