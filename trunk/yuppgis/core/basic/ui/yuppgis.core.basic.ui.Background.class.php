<?php

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