<?php

class SelectGIS extends SelectAttribute {
	
	public function __construct($tableAlias, $attrAlias) {
		parent::__construct($tableAlias, null, $attrAlias);
	}
	
}

?>