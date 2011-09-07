<?php

class Border extends UIProperty {
	
	protected $color;
	protected $width;
	
	public function __construct($alpha = 0, $zIndex = 0, $color = Color::BLACK, $width = 0) {
		$this->color = $color;
		$this->width = $width;
		parent::__construct($alpha, $zIndex);
	}
	
	public function getColor() {
		return $this->color;
	}
	
	public function setColor($color) {
		$this->color = $color;
	}
	
	public function getWidth() {
		return $this->width;
	}
	
	public function setWidth($width) {
		$this->width = $width;
	}
	
}

?>