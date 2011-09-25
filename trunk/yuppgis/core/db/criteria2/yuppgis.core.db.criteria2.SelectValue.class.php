<?php

class SelectValue extends SelectItem {
	
	private $value;
	
	public function __construct($value = null, $selectItemAlias = null) {
		$this->value = $value;
		parent::__construct($selectItemAlias);
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