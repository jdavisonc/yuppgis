<?php

class SelectValue extends SelectItem {
	
	private $value;
	
	public function __construct($value = null) {
		$this->value = $value;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public static function VALUE($value) {
		return new SelectValue($value);
	}
	
}

?>